<?php

declare(strict_types=1);

namespace Systemconf\Whatsapp;

/**
 * WhatsApp modülünün yönetim paneli formu.
 */
final class Settings
{
    public function render(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_whatsapp_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Düğme açık', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('Sitede WhatsApp düğmesini göster', 'systemconf'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-phone"><?php esc_html_e('Telefon numarası', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scwa-phone" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[phone]" value="<?php echo esc_attr((string) $c['phone']); ?>">
                        <p class="description"><?php esc_html_e('Ülke koduyla, boşluksuz: 905074417070. 0 ile başlayan 11 haneli numara otomatik çevrilir.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-tooltip"><?php esc_html_e('Düğme balonu', 'systemconf'); ?></label></th>
                    <td><input id="scwa-tooltip" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[tooltip]" value="<?php echo esc_attr((string) $c['tooltip']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-header"><?php esc_html_e('Sohbet kutusu başlığı', 'systemconf'); ?></label></th>
                    <td><input id="scwa-header" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[header_title]" value="<?php echo esc_attr((string) $c['header_title']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-welcome"><?php esc_html_e('Karşılama mesajı', 'systemconf'); ?></label></th>
                    <td><textarea id="scwa-welcome" class="large-text" rows="3" name="<?php echo esc_attr($name); ?>[welcome_text]"><?php echo esc_textarea((string) $c['welcome_text']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-cta"><?php esc_html_e('Düğme yazısı', 'systemconf'); ?></label></th>
                    <td><input id="scwa-cta" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[cta_label]" value="<?php echo esc_attr((string) $c['cta_label']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-prefill"><?php esc_html_e('WhatsApp\'ta hazır gelen mesaj', 'systemconf'); ?></label></th>
                    <td>
                        <textarea id="scwa-prefill" class="large-text" rows="2" name="<?php echo esc_attr($name); ?>[prefill]"><?php echo esc_textarea((string) $c['prefill']); ?></textarea>
                        <p class="description"><?php esc_html_e('Boş bırakılırsa WhatsApp boş mesajla açılır.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Konum', 'systemconf'); ?></th>
                    <td>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[position]" value="left" <?php checked($c['position'], 'left'); ?>> <?php esc_html_e('Sol alt', 'systemconf'); ?></label>&nbsp;&nbsp;
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[position]" value="right" <?php checked($c['position'], 'right'); ?>> <?php esc_html_e('Sağ alt', 'systemconf'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scwa-delay"><?php esc_html_e('Görünme gecikmesi (saniye)', 'systemconf'); ?></label></th>
                    <td><input id="scwa-delay" type="number" min="0" max="60" name="<?php echo esc_attr($name); ?>[delay_seconds]" value="<?php echo esc_attr((string) $c['delay_seconds']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Kenar boşlukları (px)', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Alt', 'systemconf'); ?> <input type="number" min="0" max="200" name="<?php echo esc_attr($name); ?>[offset_bottom]" value="<?php echo esc_attr((string) $c['offset_bottom']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Yan', 'systemconf'); ?> <input type="number" min="0" max="200" name="<?php echo esc_attr($name); ?>[offset_side]" value="<?php echo esc_attr((string) $c['offset_side']); ?>"></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Renkler', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Düğme', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[button_color]" value="<?php echo esc_attr((string) $c['button_color']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Kutu başlığı', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[header_color]" value="<?php echo esc_attr((string) $c['header_color']); ?>"></label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
