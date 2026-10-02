<?php

declare(strict_types=1);

namespace Systemconf\Theme;

use Systemconf\ModuleInterface;

/**
 * Blog yazısı, yazı arşivi ve 404 sayfaları için kendi şablonlarımız.
 * Elementor Pro tema oluşturucusunun yerine geçer; üst menü ve alt bilgi
 * ElementsKit'ten gelmeye devam eder (get_header/get_footer).
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'theme';
    }

    public function title(): string
    {
        return 'Blog Şablonları';
    }

    public function register(): void
    {
        add_filter('template_include', [$this, 'pickTemplate'], 99);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
    }

    public function registerAssets(): void
    {
        wp_register_style('systemconf-theme', SYSTEMCONF_URL . 'assets/theme/blog.css', ['systemconf-blocks'], SYSTEMCONF_VERSION);
    }

    public function pickTemplate(string $template): string
    {
        if (!self::enabled()) {
            return $template;
        }

        $file = null;

        if (is_singular('post')) {
            $file = 'single-post.php';
        } elseif (is_404()) {
            $file = '404.php';
        } elseif (is_home() || is_category() || is_tag() || is_author() || is_date() || (is_search() && !is_admin())) {
            $file = 'archive-post.php';
        }

        if ($file === null) {
            return $template;
        }

        $path = SYSTEMCONF_DIR . 'templates/' . $file;

        if (!is_readable($path)) {
            return $template;
        }

        wp_enqueue_style('systemconf-blocks');
        wp_enqueue_style('systemconf-theme');

        return $path;
    }

    /** Elementor Pro tema şablonları hâlâ devredeyken çakışmamak için kapatılabilir. */
    public static function enabled(): bool
    {
        return (bool) get_option('systemconf_theme_enabled', true);
    }

    public function renderSettings(): void
    {
        if (isset($_POST['systemconf_theme_enabled_nonce']) && wp_verify_nonce((string) $_POST['systemconf_theme_enabled_nonce'], 'systemconf_theme_enabled') && current_user_can('manage_options')) {
            update_option('systemconf_theme_enabled', !empty($_POST['systemconf_theme_enabled']), true);
            echo '<div class="notice notice-success inline"><p>' . esc_html__('Kaydedildi.', 'systemconf') . '</p></div>';
        }

        $enabled = self::enabled();
        ?>
        <form method="post">
            <?php wp_nonce_field('systemconf_theme_enabled', 'systemconf_theme_enabled_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Şablonlar açık', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="systemconf_theme_enabled" value="1" <?php checked($enabled); ?>> <?php esc_html_e('Blog yazısı, arşiv ve 404 sayfalarını systemconf şablonlarıyla göster', 'systemconf'); ?></label></td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
