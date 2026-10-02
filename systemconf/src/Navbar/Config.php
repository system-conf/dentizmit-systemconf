<?php

declare(strict_types=1);

namespace Systemconf\Navbar;

/**
 * Yeni üst menünün ayar şeması: varsayılanlar, okuma ve sınırda doğrulama.
 * Tüm alanlar tek tek açıkça eşlenir; bilinmeyen anahtar kabul edilmez.
 *
 * Varsayılanlar, ElementsKit "header" şablonundaki (id 139) değerlerin
 * birebir karşılığıdır. Modül kapalı gelir; panelden açılır.
 */
final class Config
{
    public const OPTION = 'systemconf_navbar';

    /** Modülün kaydettiği tema menü konumu (Görünüm > Menüler). */
    public const LOCATION = 'systemconf_header';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'          => false,
            'menu_id'          => 9,
            'logo_url'         => 'https://dentizmit.com/wp-content/uploads/2022/12/Varlik-2.svg',
            'logo_alt'         => 'Dent İzmit Ağız ve Diş Sağlığı Polikliniği',
            'cta_label'        => 'Randevu İçin Tıklayınız',
            'cta_short'        => 'Randevu Al',
            'cta_url'          => '/online-randevu/',
            'facebook_url'     => 'https://www.facebook.com/dentizmit',
            'instagram_url'    => 'https://www.instagram.com/dentizmitdis/',
            'twitter_url'      => 'https://twitter.com/Dentizmit',
            'show_search'      => true,
            'sticky'           => true,
            'hide_elementskit' => true,
            'load_font'        => true,
            'bar_color'        => '#313131',
            'accent_color'     => '#af0505',
        ];
    }

    /** @return array<string, mixed> */
    public static function load(): array
    {
        $stored = get_option(self::OPTION, []);

        if (!is_array($stored)) {
            $stored = [];
        }

        return self::sanitize(array_merge(self::defaults(), $stored));
    }

    /**
     * Formdan ya da veritabanından gelen ham diziyi güvenli şemaya çevirir.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public static function sanitize($raw): array
    {
        $raw = is_array($raw) ? $raw : [];
        $d = self::defaults();

        return [
            'enabled'          => !empty($raw['enabled']),
            'menu_id'          => absint($raw['menu_id'] ?? $d['menu_id']),
            'logo_url'         => esc_url_raw((string) ($raw['logo_url'] ?? $d['logo_url'])),
            'logo_alt'         => sanitize_text_field((string) ($raw['logo_alt'] ?? $d['logo_alt'])),
            'cta_label'        => sanitize_text_field((string) ($raw['cta_label'] ?? $d['cta_label'])),
            'cta_short'        => sanitize_text_field((string) ($raw['cta_short'] ?? $d['cta_short'])),
            'cta_url'          => esc_url_raw((string) ($raw['cta_url'] ?? $d['cta_url'])),
            'facebook_url'     => esc_url_raw((string) ($raw['facebook_url'] ?? $d['facebook_url'])),
            'instagram_url'    => esc_url_raw((string) ($raw['instagram_url'] ?? $d['instagram_url'])),
            'twitter_url'      => esc_url_raw((string) ($raw['twitter_url'] ?? $d['twitter_url'])),
            'show_search'      => !empty($raw['show_search']),
            'sticky'           => !empty($raw['sticky']),
            'hide_elementskit' => !empty($raw['hide_elementskit']),
            'load_font'        => !empty($raw['load_font']),
            'bar_color'        => self::sanitizeColor((string) ($raw['bar_color'] ?? ''), (string) $d['bar_color']),
            'accent_color'     => self::sanitizeColor((string) ($raw['accent_color'] ?? ''), (string) $d['accent_color']),
        ];
    }

    private static function sanitizeColor(string $value, string $fallback): string
    {
        $clean = sanitize_hex_color($value);

        return is_string($clean) && $clean !== '' ? $clean : $fallback;
    }
}
