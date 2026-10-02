<?php

declare(strict_types=1);

namespace Systemconf\Navbar;

use Systemconf\ModuleInterface;

/**
 * Yeni üst menü: ElementsKit "header" şablonunun (id 139) yerine geçen,
 * WordPress menüsünü kullanan kendi üst menümüz. Kapalıyken hiçbir şey basmaz.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'navbar';
    }

    public function title(): string
    {
        return 'Üst Menü (yeni)';
    }

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerLocation']);
        add_action('admin_init', [$this, 'registerSetting']);
        // Bloklar modülü arama stilini 10 önceliğinde kaydediyor; ondan sonra çalış.
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 20);
        add_action('wp_body_open', [$this, 'renderNavbar'], 5);
    }

    public function registerLocation(): void
    {
        register_nav_menu(Config::LOCATION, __('Systemconf üst menü', 'systemconf'));
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_navbar_group',
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

        if ($config['load_font']) {
            wp_enqueue_style(
                'systemconf-navbar-font',
                'https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap',
                [],
                null
            );
        }

        // Büyüteç düğmesinin stili sayfanın başında yüklensin (kısa kod geç çalışıyor).
        if ($config['show_search'] && wp_style_is('systemconf-blocks', 'registered')) {
            wp_enqueue_style('systemconf-blocks');
        }

        wp_enqueue_style('systemconf-navbar', SYSTEMCONF_URL . 'assets/navbar/navbar.css', [], SYSTEMCONF_VERSION);
        wp_add_inline_style('systemconf-navbar', $this->inlineStyle($config));
        wp_enqueue_script('systemconf-navbar', SYSTEMCONF_URL . 'assets/navbar/navbar.js', [], SYSTEMCONF_VERSION, true);
    }

    public function renderNavbar(): void
    {
        // ElementsKit, tema başlığını tamponlayıp atarken wp_body_open'ı ikinci kez
        // çalıştırıyor; menü sayfada yalnızca bir kez basılmalı.
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
        return $config['enabled'] && !is_admin();
    }

    /** @param array<string, mixed> $config */
    private function inlineStyle(array $config): string
    {
        $css = sprintf(
            ':root{--scnb-bar:%s;--scnb-accent:%s;}',
            esc_attr((string) $config['bar_color']),
            esc_attr((string) $config['accent_color'])
        );

        if ($config['hide_elementskit']) {
            // Eski üst menü (ElementsKit şablonu) ve temanın kendi başlığı gizlenir.
            $css .= '.ekit-template-content-header,#site-header.site-header{display:none!important;}';
        }

        if ($config['sticky']) {
            // Sitede html ve body ikisi de overflow:auto; bu durumda body kaydırma
            // kabı sayılıyor ve position:sticky çalışmıyor. html'i görünür yapınca
            // body'nin değeri pencereye geçer, kaydırma davranışı aynı kalır.
            $css .= 'html{overflow:visible!important;}';
        }

        return $css;
    }
}
