<?php

declare(strict_types=1);

namespace Systemconf\Layout;

/**
 * Kutulu düzenin yönetim paneli formu.
 */
final class Settings
{
    public function render(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_layout_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Kutulu düzen açık', 'systemconf'); ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('Site içeriğini azami genişlikte ortala, iki yanda zemin rengi göster', 'systemconf'); ?></label>
                        <p class="description"><?php esc_html_e('Ekran bu genişlikten darsa hiçbir şey değişmez.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sclay-width"><?php esc_html_e('Site azami genişliği (px)', 'systemconf'); ?></label></th>
                    <td>
                        <input id="sclay-width" type="number" min="<?php echo esc_attr((string) Config::MIN_WIDTH); ?>" max="<?php echo esc_attr((string) Config::MAX_WIDTH); ?>" step="10" name="<?php echo esc_attr($name); ?>[max_width]" value="<?php echo esc_attr((string) $c['max_width']); ?>">
                        <p class="description">
                            <?php
                            printf(
                                /* translators: 1: en dar, 2: en geniş değer */
                                esc_html__('%1$d ile %2$d arası. Önerilen: 1440.', 'systemconf'),
                                (int) Config::MIN_WIDTH,
                                (int) Config::MAX_WIDTH
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Renkler', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Kenar zemini', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[outer_color]" value="<?php echo esc_attr((string) $c['outer_color']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Kutu zemini', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[inner_color]" value="<?php echo esc_attr((string) $c['inner_color']); ?>"></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Gölge', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[shadow]" value="1" <?php checked($c['shadow']); ?>> <?php esc_html_e('Kutunun kenarlarına hafif gölge ver', 'systemconf'); ?></label></td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
