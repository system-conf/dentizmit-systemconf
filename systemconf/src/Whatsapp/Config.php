<?php

declare(strict_types=1);

namespace Systemconf\Whatsapp;

/**
 * WhatsApp modülünün ayar şeması: varsayılanlar, okuma ve sınırda doğrulama.
 * Tüm alanlar tek tek açıkça eşlenir; bilinmeyen anahtar kabul edilmez.
 */
final class Config
{
    public const OPTION = 'systemconf_whatsapp';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'       => true,
            'phone'         => '905074417070',
            'tooltip'       => 'Whatsapp Destek',
            'header_title'  => 'Dent İzmit Destek',
            'welcome_text'  => "Merhaba 👋\nSize nasıl yardımcı olabiliriz?",
            'cta_label'     => 'Sohbeti Başlat',
            'prefill'       => '',
            'position'      => 'left',
            'delay_seconds' => 3,
            'offset_bottom' => 20,
            'offset_side'   => 20,
            'button_color'  => '#25d366',
            'header_color'  => '#dd0000',
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

        $position = isset($raw['position']) ? sanitize_key((string) $raw['position']) : $d['position'];
        if (!in_array($position, ['left', 'right'], true)) {
            $position = $d['position'];
        }

        return [
            'enabled'       => !empty($raw['enabled']),
            'phone'         => self::sanitizePhone((string) ($raw['phone'] ?? $d['phone'])),
            'tooltip'       => sanitize_text_field((string) ($raw['tooltip'] ?? $d['tooltip'])),
            'header_title'  => sanitize_text_field((string) ($raw['header_title'] ?? $d['header_title'])),
            'welcome_text'  => sanitize_textarea_field((string) ($raw['welcome_text'] ?? $d['welcome_text'])),
            'cta_label'     => sanitize_text_field((string) ($raw['cta_label'] ?? $d['cta_label'])),
            'prefill'       => sanitize_textarea_field((string) ($raw['prefill'] ?? $d['prefill'])),
            'position'      => $position,
            'delay_seconds' => self::clampInt($raw['delay_seconds'] ?? $d['delay_seconds'], 0, 60),
            'offset_bottom' => self::clampInt($raw['offset_bottom'] ?? $d['offset_bottom'], 0, 200),
            'offset_side'   => self::clampInt($raw['offset_side'] ?? $d['offset_side'], 0, 200),
            'button_color'  => self::sanitizeColor((string) ($raw['button_color'] ?? ''), (string) $d['button_color']),
            'header_color'  => self::sanitizeColor((string) ($raw['header_color'] ?? ''), (string) $d['header_color']),
        ];
    }

    /** Sadece rakam bırakır; başındaki 0'ı Türkiye ülke koduna çevirir. */
    private static function sanitizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) === 10) {
            $digits = '90' . $digits;
        } elseif (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = '9' . $digits;
        }

        return $digits;
    }

    /** @param mixed $value */
    private static function clampInt($value, int $min, int $max): int
    {
        $int = (int) $value;

        return max($min, min($max, $int));
    }

    private static function sanitizeColor(string $value, string $fallback): string
    {
        $clean = sanitize_hex_color($value);

        return is_string($clean) && $clean !== '' ? $clean : $fallback;
    }
}
