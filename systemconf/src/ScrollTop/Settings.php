<?php

declare(strict_types=1);

namespace Systemconf\ScrollTop;

/**
 * Yukarı Çık modülünün yönetim paneli formu.
 */
final class Settings
{
    public function render(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_scrolltop_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Düğme açık', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('Sitede yukarı çık düğmesini göster', 'systemconf'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Konum', 'systemconf'); ?></th>
                    <td>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[position]" value="left" <?php checked($c['position'], 'left'); ?>> <?php esc_html_e('Sol alt', 'systemconf'); ?></label>&nbsp;&nbsp;
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[position]" value="right" <?php checked($c['position'], 'right'); ?>> <?php esc_html_e('Sağ alt', 'systemconf'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Kenar boşlukları (px)', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Alt', 'systemconf'); ?> <input type="number" min="0" max="300" name="<?php echo esc_attr($name); ?>[offset_bottom]" value="<?php echo esc_attr((string) $c['offset_bottom']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Yan', 'systemconf'); ?> <input type="number" min="0" max="300" name="<?php echo esc_attr($name); ?>[offset_side]" value="<?php echo esc_attr((string) $c['offset_side']); ?>"></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scst-size"><?php esc_html_e('Boyut (px)', 'systemconf'); ?></label></th>
                    <td><input id="scst-size" type="number" min="24" max="96" name="<?php echo esc_attr($name); ?>[size]" value="<?php echo esc_attr((string) $c['size']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scst-radius"><?php esc_html_e('Köşe yuvarlaklığı (px)', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scst-radius" type="number" min="0" max="50" name="<?php echo esc_attr($name); ?>[radius]" value="<?php echo esc_attr((string) $c['radius']); ?>">
                        <p class="description"><?php esc_html_e('0 kare, 50 tam daire.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Renkler', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Zemin', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[bg_color]" value="<?php echo esc_attr((string) $c['bg_color']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Ok', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[icon_color]" value="<?php echo esc_attr((string) $c['icon_color']); ?>"></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scst-after"><?php esc_html_e('Görünme eşiği (px)', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scst-after" type="number" min="0" max="5000" name="<?php echo esc_attr($name); ?>[show_after]" value="<?php echo esc_attr((string) $c['show_after']); ?>">
                        <p class="description"><?php esc_html_e('Sayfa bu kadar piksel kaydırılınca düğme belirir.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Yumuşak kaydırma', 'systemconf'); ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo esc_attr($name); ?>[smooth]" value="1" <?php checked($c['smooth']); ?>> <?php esc_html_e('Yukarı çıkarken akıcı kaydır', 'systemconf'); ?></label>
                        <p class="description"><?php esc_html_e('Ziyaretçi cihazında "hareketi azalt" açıksa kaydırma anında olur.', 'systemconf'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
