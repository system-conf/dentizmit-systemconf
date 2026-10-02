<?php

declare(strict_types=1);

namespace Systemconf\Layout;

use Systemconf\ModuleInterface;

/**
 * Kutulu düzen: site içeriği belirlenen azami genişlikte ortalanır, iki yanda
 * gri zemin görünür. Sabit konumlu öğeler (WhatsApp, yukarı çık, menü paneli)
 * kutunun kenarına hizalanır. Kapalı gelir; panelden açılır.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'layout';
    }

    public function title(): string
    {
        return 'Kutulu Düzen';
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_layout_group',
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

        if (!$config['enabled'] || is_admin()) {
            return;
        }

        wp_enqueue_style('systemconf-layout', SYSTEMCONF_URL . 'assets/layout/boxed.css', [], SYSTEMCONF_VERSION);
        wp_add_inline_style('systemconf-layout', $this->inlineStyle($config));
    }

    /** @param array<string, mixed> $config */
    private function inlineStyle(array $config): string
    {
        return sprintf(
            ':root{--sclay-max:%dpx;--sclay-outer:%s;--sclay-inner:%s;--sclay-shadow:%s;}',
            (int) $config['max_width'],
            esc_attr((string) $config['outer_color']),
            esc_attr((string) $config['inner_color']),
            $config['shadow'] ? '0 0 30px rgba(0,0,0,.12)' : 'none'
        );
    }
}
