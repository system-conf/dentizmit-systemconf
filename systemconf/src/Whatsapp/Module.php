<?php

declare(strict_types=1);

namespace Systemconf\Whatsapp;

use Systemconf\ModuleInterface;

/**
 * WhatsApp destek düğmesi modülü: ayar kaydı, ön yüz varlıkları ve çıktı.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'whatsapp';
    }

    public function title(): string
    {
        return 'WhatsApp Düğmesi';
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_footer', [$this, 'renderWidget']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_whatsapp_group',
            Config::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [Config::class, 'sanitize'],
                'default'           => Config::defaults(),
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
            'systemconf-whatsapp',
            SYSTEMCONF_URL . 'assets/whatsapp/button.css',
            [],
            SYSTEMCONF_VERSION
        );

        wp_enqueue_script(
            'systemconf-whatsapp',
            SYSTEMCONF_URL . 'assets/whatsapp/button.js',
            [],
            SYSTEMCONF_VERSION,
            true
        );

        wp_localize_script('systemconf-whatsapp', 'systemconfWhatsapp', [
            'phone'        => $config['phone'],
            'prefill'      => $config['prefill'],
            'delaySeconds' => $config['delay_seconds'],
        ]);

        wp_add_inline_style('systemconf-whatsapp', $this->inlineStyle($config));
    }

    public function renderWidget(): void
    {
        // Bazı tema/eklenti bileşimleri wp_footer kancasını iki kez çalıştırıyor;
        // düğme sayfada yalnızca bir kez basılmalı.
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
        if (!$config['enabled'] || $config['phone'] === '') {
            return false;
        }

        if (is_admin()) {
            return false;
        }

        return true;
    }

    /** @param array<string, mixed> $config */
    private function inlineStyle(array $config): string
    {
        return sprintf(
            ':root{--scwa-button:%s;--scwa-header:%s;--scwa-bottom:%dpx;--scwa-side:%dpx;}',
            esc_attr((string) $config['button_color']),
            esc_attr((string) $config['header_color']),
            (int) $config['offset_bottom'],
            (int) $config['offset_side']
        );
    }
}
