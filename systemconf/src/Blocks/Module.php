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
        add_shortcode('systemconf_subeler', [$this, 'branches']);
    }

    /** @param array<string, mixed>|string $atts */
    public function branches($atts): string
    {
        wp_enqueue_style('systemconf-blocks');

        $atts = shortcode_atts(['baslik' => ''], is_array($atts) ? $atts : [], 'systemconf_subeler');
        $hero = '';

        if ($atts['baslik'] !== '') {
            wp_enqueue_style('systemconf-theme');
            $hero = $this->heroBand(sanitize_text_field((string) $atts['baslik']));
        }

        return $hero . '<div class="scb-branches-wrap">' . (new BranchCards())->render() . '</div>';
    }

    /** Blog şablonlarındaki koyu başlık şeridini sayfa içinde basar. */
    private function heroBand(string $title): string
    {
        $subtitle = '';
        $crumbs = [
            ['label' => 'Anasayfa', 'url' => home_url('/')],
            ['label' => $title, 'url' => ''],
        ];

        ob_start();
        include SYSTEMCONF_DIR . 'templates/parts/hero.php';

        return (string) ob_get_clean();
    }

    public function registerAssets(): void
    {
        wp_register_style('systemconf-blocks', SYSTEMCONF_URL . 'assets/blocks/blocks.css', [], SYSTEMCONF_VERSION);
        wp_register_script('systemconf-blocks', SYSTEMCONF_URL . 'assets/blocks/blocks.js', [], SYSTEMCONF_VERSION, true);
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

    /** @param array<string, mixed>|string $atts */
    public function search($atts): string
    {
        wp_enqueue_style('systemconf-blocks');

        $atts = shortcode_atts(['stil' => ''], is_array($atts) ? $atts : [], 'systemconf_arama');

        if ($atts['stil'] === 'ikon') {
            wp_enqueue_script('systemconf-blocks');
        }

        return (new SearchForm())->render((string) $atts['stil']);
    }

    public function renderSettings(): void
    {
        echo '<p>' . esc_html__('Bu bloklar Elementor içinde "Kısa kod" bileşeniyle kullanılır:', 'systemconf') . '</p><ul>';
        echo '<li><code>[systemconf_hizmetler]</code> — ' . esc_html__('6 hizmet kartı (dönen kartlar)', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_yazilar skin="cards" count="2"]</code> — ' . esc_html__('son yazılar, büyük kartlar', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_yazilar skin="list" ids="1706,306,1757,1598,1595"]</code> — ' . esc_html__('seçili yazılar, küçük liste', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_subeler]</code> — ' . esc_html__('şube kartları (görsel, harita, adres, düğmeler)', 'systemconf') . '</li>';
        echo '<li><code>[systemconf_arama]</code> — ' . esc_html__('arama kutusu', 'systemconf') . '</li></ul>';
    }
}
