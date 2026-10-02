<?php

declare(strict_types=1);

namespace Systemconf\Header;

use Systemconf\ModuleInterface;

/**
 * Yapışkan üst menü. Elementor Pro'nun "sticky" ayarının yerine geçer:
 * ElementsKit üst menü şablonundaki bölümler aşağı kaydırınca sabit kalır.
 */
final class Module implements ModuleInterface
{
    private const OPTION = 'systemconf_header_sticky_ids';

    public function slug(): string
    {
        return 'header';
    }

    public function title(): string
    {
        return 'Üst Menü';
    }

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    /** @return array<int, string> Elementor öğe kimlikleri */
    public static function elementIds(): array
    {
        $stored = get_option(self::OPTION, '45c11e1, 3d04d53');
        $ids = array_filter(array_map(static fn($v): string => sanitize_key(trim((string) $v)), explode(',', (string) $stored)));

        return array_values($ids);
    }

    public function enqueue(): void
    {
        $ids = self::elementIds();

        if ($ids === [] || is_admin()) {
            return;
        }

        wp_enqueue_style('systemconf-header', SYSTEMCONF_URL . 'assets/header/sticky.css', [], SYSTEMCONF_VERSION);
        wp_enqueue_script('systemconf-header', SYSTEMCONF_URL . 'assets/header/sticky.js', [], SYSTEMCONF_VERSION, true);
        wp_localize_script('systemconf-header', 'systemconfHeader', [
            'selectors' => array_map(static fn(string $id): string => '.elementor-element-' . $id, $ids),
        ]);
    }

    public function renderSettings(): void
    {
        if (isset($_POST['systemconf_header_nonce']) && wp_verify_nonce((string) $_POST['systemconf_header_nonce'], 'systemconf_header') && current_user_can('manage_options')) {
            update_option(self::OPTION, sanitize_text_field((string) ($_POST['systemconf_header_sticky_ids'] ?? '')), true);
            echo '<div class="notice notice-success inline"><p>' . esc_html__('Kaydedildi.', 'systemconf') . '</p></div>';
        }
        ?>
        <form method="post">
            <?php wp_nonce_field('systemconf_header', 'systemconf_header_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="sch-ids"><?php esc_html_e('Yapışkan bölüm kimlikleri', 'systemconf'); ?></label></th>
                    <td>
                        <input id="sch-ids" class="regular-text" type="text" name="systemconf_header_sticky_ids" value="<?php echo esc_attr(implode(', ', self::elementIds())); ?>">
                        <p class="description"><?php esc_html_e('ElementsKit üst menü şablonundaki Elementor bölüm kimlikleri, virgülle. Boş bırakılırsa yapışkan menü kapanır.', 'systemconf'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
