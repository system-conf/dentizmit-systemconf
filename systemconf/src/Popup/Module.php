<?php

declare(strict_types=1);

namespace Systemconf\Popup;

use Systemconf\ModuleInterface;

/**
 * Popup modülü: ayar kaydı, ön yüz varlıkları ve çıktı.
 * Popup Maker eklentisinin yerine geçer.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'popup';
    }

    public function title(): string
    {
        return 'Popup';
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_footer', [$this, 'renderPopup']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_popup_group',
            Config::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [Config::class, 'sanitize'],
            ]
        );
    }

    public function renderSettings(): void
    {
        (new Settings())->render();
    }

    public function enqueueAssets(): void
    {
        $config = Config::load();

        if (!$this->shouldRender($config)) {
            return;
        }

        wp_enqueue_style(
            'systemconf-popup',
            SYSTEMCONF_URL . 'assets/popup/popup.css',
            [],
            SYSTEMCONF_VERSION
        );

        wp_enqueue_script(
            'systemconf-popup',
            SYSTEMCONF_URL . 'assets/popup/popup.js',
            [],
            SYSTEMCONF_VERSION,
            true
        );

        wp_localize_script('systemconf-popup', 'systemconfPopup', [
            'trigger'      => $config['trigger'],
            'delaySeconds' => $config['delay_seconds'],
            'repeatDays'   => $config['repeat_days'],
            'storageKey'   => 'systemconfPopupClosed_' . Config::fingerprint($config),
        ]);

        wp_add_inline_style('systemconf-popup', $this->inlineStyle($config));
    }

    public function renderPopup(): void
    {
        // Bazı tema/eklenti bileşimleri wp_footer kancasını iki kez çalıştırıyor;
        // popup sayfada yalnızca bir kez basılmalı.
        static $rendered = false;

        if ($rendered) {
            return;
        }

        $config = Config::load();

        if (!$this->shouldRender($config)) {
            return;
        }

        $rendered = true;
        (new View($config))->render();
    }

    /** @param array<string, mixed> $config */
    private function shouldRender(array $config): bool
    {
        if (!$config['enabled'] || is_admin() || is_customize_preview()) {
            return false;
        }

        if ($config['content'] === '' && $config['image_url'] === '') {
            return false;
        }

        return $this->matchesTarget($config);
    }

    /** @param array<string, mixed> $config */
    private function matchesTarget(array $config): bool
    {
        if ($config['show_on'] === Config::SHOW_HOME) {
            return is_front_page();
        }

        if ($config['show_on'] === Config::SHOW_PAGES) {
            $ids = is_array($config['page_ids']) ? $config['page_ids'] : [];

            return is_singular() && in_array(get_queried_object_id(), $ids, true);
        }

        return true;
    }

    /** @param array<string, mixed> $config */
    private function inlineStyle(array $config): string
    {
        return sprintf(
            ':root{--scpu-bg:%s;--scpu-accent:%s;--scpu-width:%dpx;}',
            esc_attr((string) $config['bg_color']),
            esc_attr((string) $config['accent_color']),
            (int) $config['width_px']
        );
    }
}
