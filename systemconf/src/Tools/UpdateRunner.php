<?php

declare(strict_types=1);

namespace Systemconf\Tools;

use WP_Error;
use WP_REST_Response;

/**
 * WordPress'in kendi güncelleme mekanizmasını komutla tetikler:
 * GitHub sürümünü kontrol eder, yeni sürüm varsa standart yükseltici ile kurar.
 * Dışarıdan dosya kabul etmez; kaynak yalnızca Updater modülündeki GitHub deposudur.
 */
final class UpdateRunner
{
    public const ROUTE_NAMESPACE = 'systemconf/v1';

    public function registerRoutes(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, '/run-update', [
            'methods'             => 'POST',
            'callback'            => [$this, 'run'],
            'permission_callback' => static fn(): bool => current_user_can('update_plugins'),
        ]);
    }

    /** @return WP_REST_Response|WP_Error */
    public function run()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $basename = plugin_basename(SYSTEMCONF_FILE);

        // Önbelleği temizleyip güncelleme bilgisini tazele.
        delete_transient('systemconf_github_release');
        delete_site_transient('update_plugins');
        wp_update_plugins();

        $updates = get_site_transient('update_plugins');
        $available = is_object($updates) && isset($updates->response[$basename]) ? $updates->response[$basename] : null;

        if ($available === null) {
            return new WP_REST_Response([
                'updated'   => false,
                'installed' => SYSTEMCONF_VERSION,
                'message'   => 'Yeni sürüm yok.',
            ]);
        }

        $skin = new QuietSkin();
        $upgrader = new \Plugin_Upgrader($skin);
        $result = $upgrader->upgrade($basename, ['clear_update_cache' => true]);

        if (is_wp_error($result)) {
            return new WP_Error('systemconf_update_failed', $result->get_error_message(), ['status' => 500]);
        }

        if ($result !== true) {
            return new WP_Error('systemconf_update_failed', 'Yükseltme tamamlanamadı: ' . implode(' | ', $skin->messages()), ['status' => 500]);
        }

        if (!is_plugin_active($basename)) {
            activate_plugin($basename);
        }

        $data = get_plugin_data(SYSTEMCONF_FILE, false, false);

        return new WP_REST_Response([
            'updated'   => true,
            'previous'  => SYSTEMCONF_VERSION,
            'installed' => (string) ($data['Version'] ?? '?'),
            'target'    => (string) ($available->new_version ?? ''),
            'log'       => $skin->messages(),
        ]);
    }
}
