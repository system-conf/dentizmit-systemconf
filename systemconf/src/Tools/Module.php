<?php

declare(strict_types=1);

namespace Systemconf\Tools;

use Systemconf\ModuleInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Yönetici araçları: sayfaların Elementor verisini REST üzerinden okuma.
 * Yalnızca manage_options yetkisi olan kullanıcılar erişebilir.
 */
final class Module implements ModuleInterface
{
    private const NAMESPACE = 'systemconf/v1';

    public function slug(): string
    {
        return 'tools';
    }

    public function title(): string
    {
        return 'Araçlar';
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/elementor-data/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'elementorData'],
            'permission_callback' => static fn(): bool => current_user_can('manage_options'),
            'args'                => [
                'id' => ['validate_callback' => static fn($v): bool => is_numeric($v)],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/elementor-index', [
            'methods'             => 'GET',
            'callback'            => [$this, 'elementorIndex'],
            'permission_callback' => static fn(): bool => current_user_can('manage_options'),
        ]);

        register_rest_route(self::NAMESPACE, '/elementor-replace-widget', [
            'methods'             => 'POST',
            'callback'            => [$this, 'replaceWidget'],
            'permission_callback' => static fn(): bool => current_user_can('manage_options'),
        ]);
    }

    /**
     * Bir sayfadaki tek bir bileşeni (id ile) yerinde başka bir bileşenle değiştirir.
     * Gövde: {post_id, widget_id, widget_type, settings}
     */
    public function replaceWidget(WP_REST_Request $request)
    {
        $postId = (int) $request->get_param('post_id');
        $widgetId = sanitize_text_field((string) $request->get_param('widget_id'));
        $widgetType = sanitize_key((string) $request->get_param('widget_type'));
        $settings = $request->get_param('settings');

        if ($postId <= 0 || $widgetId === '' || $widgetType === '' || !is_array($settings)) {
            return new WP_Error('systemconf_bad_request', 'post_id, widget_id, widget_type ve settings zorunlu.', ['status' => 400]);
        }

        $raw = get_post_meta($postId, '_elementor_data', true);
        $tree = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

        if (!is_array($tree)) {
            return new WP_Error('systemconf_no_data', 'Bu içerikte Elementor verisi yok.', ['status' => 404]);
        }

        $replaced = 0;
        $tree = (new WidgetReplacer())->replace($tree, $widgetId, $widgetType, $settings, $replaced);

        if ($replaced === 0) {
            return new WP_Error('systemconf_widget_not_found', 'Bileşen bulunamadı: ' . $widgetId, ['status' => 404]);
        }

        update_post_meta($postId, '_elementor_data', wp_slash(wp_json_encode($tree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        delete_post_meta($postId, '_elementor_css');

        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        return new WP_REST_Response(['post_id' => $postId, 'replaced' => $replaced]);
    }

    /** Tek bir yazının/sayfanın Elementor ağacını döndürür. */
    public function elementorData(WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $post = get_post($id);

        if ($post === null) {
            return new WP_Error('systemconf_not_found', 'Yazı bulunamadı.', ['status' => 404]);
        }

        $raw = get_post_meta($id, '_elementor_data', true);
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

        return new WP_REST_Response([
            'id'            => $id,
            'title'         => $post->post_title,
            'type'          => $post->post_type,
            'status'        => $post->post_status,
            'template_type' => get_post_meta($id, '_elementor_template_type', true),
            'page_settings' => get_post_meta($id, '_elementor_page_settings', true),
            'elements'      => $decoded,
        ]);
    }

    /** Elementor ile düzenlenmiş tüm içeriklerin kısa listesi. */
    public function elementorIndex(): WP_REST_Response
    {
        $posts = get_posts([
            'post_type'      => 'any',
            'post_status'    => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => 500,
            'meta_key'       => '_elementor_edit_mode',
            'meta_value'     => 'builder',
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ]);

        $rows = [];
        foreach ($posts as $post) {
            $rows[] = [
                'id'            => $post->ID,
                'title'         => $post->post_title,
                'type'          => $post->post_type,
                'status'        => $post->post_status,
                'template_type' => get_post_meta($post->ID, '_elementor_template_type', true),
                'link'          => get_permalink($post),
            ];
        }

        return new WP_REST_Response($rows);
    }

    public function renderSettings(): void
    {
        echo '<p>' . esc_html__('Bu modül yalnızca yönetici REST uçları sağlar; ayarı yoktur.', 'systemconf') . '</p>';
        echo '<ul><li><code>GET /wp-json/systemconf/v1/elementor-index</code></li>';
        echo '<li><code>GET /wp-json/systemconf/v1/elementor-data/{id}</code></li></ul>';
    }
}
