<?php

declare(strict_types=1);

namespace Systemconf\Navbar;

/**
 * Yeni üst menünün yönetim paneli formu.
 */
final class Settings
{
    public function render(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        $menus = wp_get_nav_menus();
        ?>
        <?php $this->renderMenuWarning((int) $c['menu_id']); ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_navbar_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Üst menü açık', 'systemconf'); ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('Sitede yeni üst menüyü göster', 'systemconf'); ?></label>
                        <p class="description"><?php esc_html_e('Kapalıyken sitede hiçbir şey değişmez; ElementsKit üst menüsü görünmeye devam eder.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scnb-menu"><?php esc_html_e('Kullanılacak menü', 'systemconf'); ?></label></th>
                    <td>
                        <select id="scnb-menu" name="<?php echo esc_attr($name); ?>[menu_id]">
                            <option value="0" <?php selected((int) $c['menu_id'], 0); ?>><?php esc_html_e('— "Systemconf üst menü" konumuna atanan menü —', 'systemconf'); ?></option>
                            <?php foreach ($menus as $menu) : ?>
                                <option value="<?php echo esc_attr((string) $menu->term_id); ?>" <?php selected((int) $c['menu_id'], (int) $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Menü maddeleri Görünüm > Menüler ekranından düzenlenir.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scnb-logo"><?php esc_html_e('Logo adresi', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scnb-logo" class="large-text" type="url" name="<?php echo esc_attr($name); ?>[logo_url]" value="<?php echo esc_attr((string) $c['logo_url']); ?>">
                        <p class="description"><?php esc_html_e('Boş bırakılırsa site adı yazı olarak gösterilir.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scnb-alt"><?php esc_html_e('Logo açıklaması', 'systemconf'); ?></label></th>
                    <td><input id="scnb-alt" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[logo_alt]" value="<?php echo esc_attr((string) $c['logo_alt']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Randevu düğmesi', 'systemconf'); ?></th>
                    <td>
                        <p><label><?php esc_html_e('Yazı', 'systemconf'); ?> <input class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[cta_label]" value="<?php echo esc_attr((string) $c['cta_label']); ?>"></label></p>
                        <p><label><?php esc_html_e('Kısa yazı (dar ekranlar)', 'systemconf'); ?> <input type="text" name="<?php echo esc_attr($name); ?>[cta_short]" value="<?php echo esc_attr((string) $c['cta_short']); ?>"></label></p>
                        <p><label><?php esc_html_e('Bağlantı', 'systemconf'); ?> <input class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[cta_url]" value="<?php echo esc_attr((string) $c['cta_url']); ?>"></label></p>
                        <p class="description"><?php esc_html_e('Yazı ya da bağlantı boşsa düğme gösterilmez.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Sosyal medya', 'systemconf'); ?></th>
                    <td>
                        <?php $this->urlField('facebook_url', 'Facebook', (string) $c['facebook_url']); ?>
                        <?php $this->urlField('instagram_url', 'Instagram', (string) $c['instagram_url']); ?>
                        <?php $this->urlField('twitter_url', 'Twitter', (string) $c['twitter_url']); ?>
                        <p class="description"><?php esc_html_e('Boş bırakılan simge gösterilmez.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Seçenekler', 'systemconf'); ?></th>
                    <td>
                        <?php $this->checkbox('show_search', __('Büyüteç (tam ekran arama) göster', 'systemconf'), (bool) $c['show_search']); ?>
                        <?php $this->checkbox('sticky', __('Aşağı kaydırınca koyu menü satırı üstte sabit kalsın', 'systemconf'), (bool) $c['sticky']); ?>
                        <?php $this->checkbox('hide_elementskit', __('Eski üst menüyü (ElementsKit şablonu) ve temanın kendi başlığını gizle', 'systemconf'), (bool) $c['hide_elementskit']); ?>
                        <?php $this->checkbox('load_font', __('Poppins yazı tipini Google Fonts\'tan yükle', 'systemconf'), (bool) $c['load_font']); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Renkler', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Menü satırı', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[bar_color]" value="<?php echo esc_attr((string) $c['bar_color']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Vurgu (düğme, etkin madde)', 'systemconf'); ?> <input type="color" name="<?php echo esc_attr($name); ?>[accent_color]" value="<?php echo esc_attr((string) $c['accent_color']); ?>"></label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }

    /** Seçili menü silinmişse ya da konuma menü atanmamışsa uyarır. */
    private function renderMenuWarning(int $menuId): void
    {
        $ok = $menuId > 0 ? (bool) wp_get_nav_menu_object($menuId) : has_nav_menu(Config::LOCATION);

        if ($ok) {
            return;
        }

        echo '<div class="notice notice-warning inline"><p>'
            . esc_html__('Seçili menü bulunamadı; üst menüde menü maddeleri görünmeyecek. Aşağıdan bir menü seçin.', 'systemconf')
            . '</p></div>';
    }

    private function urlField(string $key, string $label, string $value): void
    {
        printf(
            '<p><label>%1$s <input class="regular-text" type="url" name="%2$s[%3$s]" value="%4$s"></label></p>',
            esc_html($label),
            esc_attr(Config::OPTION),
            esc_attr($key),
            esc_attr($value)
        );
    }

    private function checkbox(string $key, string $label, bool $checked): void
    {
        printf(
            '<p><label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label></p>',
            esc_attr(Config::OPTION),
            esc_attr($key),
            checked($checked, true, false),
            esc_html($label)
        );
    }
}
