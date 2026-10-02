<?php

declare(strict_types=1);

namespace Systemconf\Popup;

/**
 * Popup modülünün yönetim paneli formu.
 */
final class Settings
{
    public function render(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        $ids = is_array($c['page_ids']) ? implode(', ', $c['page_ids']) : '';
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_popup_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Popup açık', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('Sitede popup\'ı göster', 'systemconf'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scpu-title-field"><?php esc_html_e('Başlık', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scpu-title-field" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[title]" value="<?php echo esc_attr((string) $c['title']); ?>">
                        <p class="description"><?php esc_html_e('Ekranda görünmez; ekran okuyucular ve görsel açıklaması için kullanılır.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('İçerik', 'systemconf'); ?></th>
                    <td>
                        <?php
                        wp_editor((string) $c['content'], 'systemconf_popup_content', [
                            'textarea_name' => $name . '[content]',
                            'textarea_rows' => 10,
                            'media_buttons' => true,
                        ]);
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scpu-image"><?php esc_html_e('Üst görsel adresi', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scpu-image" class="large-text" type="url" name="<?php echo esc_attr($name); ?>[image_url]" value="<?php echo esc_attr((string) $c['image_url']); ?>">
                        <p class="description"><?php esc_html_e('İsteğe bağlı. Doluysa içeriğin üstünde gösterilir.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Ne zaman açılsın', 'systemconf'); ?></th>
                    <td>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[trigger]" value="load" <?php checked($c['trigger'], 'load'); ?>> <?php esc_html_e('Sayfa açılınca', 'systemconf'); ?></label><br>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[trigger]" value="exit" <?php checked($c['trigger'], 'exit'); ?>> <?php esc_html_e('Ziyaretçi sayfadan çıkmak üzereyken (yalnızca bilgisayarda)', 'systemconf'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scpu-delay"><?php esc_html_e('Açılma gecikmesi (saniye)', 'systemconf'); ?></label></th>
                    <td><input id="scpu-delay" type="number" min="0" max="60" step="0.5" name="<?php echo esc_attr($name); ?>[delay_seconds]" value="<?php echo esc_attr((string) $c['delay_seconds']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scpu-repeat"><?php esc_html_e('Kapatınca kaç gün gösterilmesin', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scpu-repeat" type="number" min="0" max="365" name="<?php echo esc_attr($name); ?>[repeat_days]" value="<?php echo esc_attr((string) $c['repeat_days']); ?>">
                        <p class="description"><?php esc_html_e('0 yazarsanız her ziyarette yeniden gösterilir. İçerik değişirse popup herkese yeniden çıkar.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Hangi sayfalarda', 'systemconf'); ?></th>
                    <td>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[show_on]" value="all" <?php checked($c['show_on'], 'all'); ?>> <?php esc_html_e('Tüm sayfalarda', 'systemconf'); ?></label><br>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[show_on]" value="home" <?php checked($c['show_on'], 'home'); ?>> <?php esc_html_e('Yalnızca ana sayfada', 'systemconf'); ?></label><br>
                        <label><input type="radio" name="<?php echo esc_attr($name); ?>[show_on]" value="pages" <?php checked($c['show_on'], 'pages'); ?>> <?php esc_html_e('Yalnızca aşağıda yazdığım sayfalarda', 'systemconf'); ?></label>
                        <p><input class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[page_ids]" value="<?php echo esc_attr($ids); ?>" placeholder="12, 48, 130"></p>
                        <p class="description"><?php esc_html_e('Sayfa veya yazı numaraları, virgülle ayrılmış.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scpu-width"><?php esc_html_e('Kutu genişliği (px)', 'systemconf'); ?></label></th>
                    <td><input id="scpu-width" type="number" min="240" max="1400" name="<?php echo esc_attr($name); ?>[width_px]" value="<?php echo esc_attr((string) $c['width_px']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Renkler', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Arka plan', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[bg_color]" value="<?php echo esc_attr((string) $c['bg_color']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Çerçeve ve kapatma düğmesi', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[accent_color]" value="<?php echo esc_attr((string) $c['accent_color']); ?>"></label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }
}
