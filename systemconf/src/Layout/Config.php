<?php

declare(strict_types=1);

namespace Systemconf\Layout;

/**
 * Kutulu düzen ayar şeması: varsayılanlar, okuma ve sınırda doğrulama.
 * Tüm alanlar tek tek açıkça eşlenir; bilinmeyen anahtar kabul edilmez.
 */
final class Config
{
    public const OPTION = 'systemconf_layout';

    /**
     * En dar kutu genişliği. Üst menü 1201px ve üzerinde uzun düğme yazısını
     * gösterdiği için kutu bundan dar olamaz (navbar.css'teki hesaba bakın).
     */
    public const MIN_WIDTH = 1280;
    public const MAX_WIDTH = 2560;

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'     => false,
            'max_width'   => 1440,
            'outer_color' => '#e9e9e9',
            'inner_color' => '#ffffff',
            'shadow'      => true,
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
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public static function sanitize($raw): array
    {
        $raw = is_array($raw) ? $raw : [];
        $d = self::defaults();

        return [
            'enabled'     => !empty($raw['enabled']),
            'max_width'   => max(self::MIN_WIDTH, min(self::MAX_WIDTH, (int) ($raw['max_width'] ?? $d['max_width']))),
            'outer_color' => self::sanitizeColor((string) ($raw['outer_color'] ?? ''), (string) $d['outer_color']),
            'inner_color' => self::sanitizeColor((string) ($raw['inner_color'] ?? ''), (string) $d['inner_color']),
            'shadow'      => !empty($raw['shadow']),
        ];
    }

    private static function sanitizeColor(string $value, string $fallback): string
    {
        $clean = sanitize_hex_color($value);

        return is_string($clean) && $clean !== '' ? $clean : $fallback;
    }
}
