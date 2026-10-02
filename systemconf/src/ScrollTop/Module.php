<?php

declare(strict_types=1);

namespace Systemconf\ScrollTop;

use Systemconf\ModuleInterface;

/**
 * Yukarı Çık modülü: "To Top" eklentisinin yerine geçen sayfa başına dönüş düğmesi.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'scrolltop';
    }

    public function title(): string
    {
        return 'Yukarı Çık';
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_footer', [$this, 'renderButton']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_scrolltop_group',
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
            'systemconf-scrolltop',
            SYSTEMCONF_URL . 'assets/scrolltop/button.css',
            [],
            SYSTEMCONF_VERSION
        );

        wp_enqueue_script(
            'systemconf-scrolltop',
            SYSTEMCONF_URL . 'assets/scrolltop/button.js',
            [],
            SYSTEMCONF_VERSION,
            true
        );

        wp_localize_script('systemconf-scrolltop', 'systemconfScrollTop', [
            'showAfter' => $config['show_after'],
            'smooth'    => $config['smooth'] ? 1 : 0,
        ]);

        wp_add_inline_style('systemconf-scrolltop', $this->inlineStyle($config));
    }

    public function renderButton(): void
    {
        // wp_footer kancası bazı tema/eklenti bileşimlerinde iki kez çalışıyor;
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
        (new View())->render();
    }

    /** @param array<string, mixed> $config */
    private function shouldRender(array $config): bool
    {
        return (bool) $config['enabled'] && !is_admin();
    }

    /** @param array<string, mixed> $config */
    private function inlineStyle(array $config): string
    {
        $sideProperty = $config['position'] === 'left' ? 'left' : 'right';

        return sprintf(
            ':root{--scst-bottom:%dpx;--scst-side:%dpx;--scst-size:%dpx;--scst-bg:%s;--scst-icon:%s;--scst-radius:%s;}.scst{%s:calc(var(--sc-gutter,0px) + var(--scst-side)) !important;}',
            (int) $config['offset_bottom'],
            (int) $config['offset_side'],
            (int) $config['size'],
            esc_attr((string) $config['bg_color']),
            esc_attr((string) $config['icon_color']),
            (int) $config['radius'] >= 50 ? '50%' : (int) $config['radius'] . 'px',
            $sideProperty
        );
    }
}
