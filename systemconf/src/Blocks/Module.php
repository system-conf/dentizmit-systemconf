<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

use Systemconf\ModuleInterface;

/**
 * Elementor Pro bileşenlerinin yerine geçen kısa kod blokları.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'blocks';
    }

    public function title(): string
    {
        return 'Bloklar';
    }

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_shortcode('systemconf_hizmetler', [$this, 'services']);
        add_shortcode('systemconf_yazilar', [$this, 'posts']);
        add_shortcode('systemconf_arama', [$this, 'search']);
    }

    public function registerAssets(): void
    {
        wp_register_style('systemconf-blocks', SYSTEMCONF_URL . 'assets/blocks/blocks.css', [], SYSTEMCONF_VERSION);
    }

    /** @param array<string, mixed>|string $atts */
    public function services($atts): string
    {
        wp_enqueue_style('systemconf-blocks');

        $atts = shortcode_atts(['kart' => 0], is_array($atts) ? $atts : [], 'systemconf_hizmetler');

        return (new ServiceCards())->render((int) $atts['kart']);
    }

    /** @param array<string, mixed>|string $atts */
    public function posts($atts): string
    {
        wp_enqueue_style('systemconf-blocks');

        $atts = shortcode_atts([
            'skin'            => 'cards',
            'count'           => 2,
            'ids'             => '',
            'exclude_current' => '',
        ], is_array($atts) ? $atts : [], 'systemconf_yazilar');

        return (new PostsList())->render($atts);
    }

    public function search(): string
    {
        wp_enqueue_style('systemconf-blocks');

        return (new SearchForm())->render();
    }

    public function renderSettings(): void
    {
        echo '<p>' . esc_html__('Bu bloklar Elementor içinde "Kısa kod" bileşeniyle kullanılır:', 'systemconf') . '</p><ul>';
        echo '<li><code>[systemconf_hizmetler]</code> — ' . esc_html__('6 hizmet kartı (dönen kartlar)', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_yazilar skin="cards" count="2"]</code> — ' . esc_html__('son yazılar, büyük kartlar', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_yazilar skin="list" ids="1706,306,1757,1598,1595"]</code> — ' . esc_html__('seçili yazılar, küçük liste', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_arama]</code> — ' . esc_html__('arama kutusu', 'systemconf') . '</li></ul>';
    }
}
