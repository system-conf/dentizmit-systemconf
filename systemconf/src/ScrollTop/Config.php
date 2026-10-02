<?php

declare(strict_types=1);

namespace Systemconf\ScrollTop;

/**
 * Yukarı Çık modülünün ayar şeması: varsayılanlar, okuma ve sınırda doğrulama.
 * Varsayılanlar, yerine geçtiği "To Top" eklentisinin canlı ayarlarından alındı
 * (42 px kare = 32 px simge + 2x5 px iç boşluk, %16 köşe ≈ 7 px).
 */
final class Config
{
    public const OPTION = 'systemconf_scrolltop';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'       => true,
            'position'      => 'right',
            'offset_bottom' => 20,
            'offset_side'   => 20,
            'size'          => 42,
            'bg_color'      => '#dd0000',
            'icon_color'    => '#ffffff',
            'radius'        => 7,
            'show_after'    => 100,
            'smooth'        => true,
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

        $position = isset($raw['position']) ? sanitize_key((string) $raw['position']) : $d['position'];
        if (!in_array($position, ['left', 'right'], true)) {
            $position = $d['position'];
        }

        return [
            'enabled'       => !empty($raw['enabled']),
            'position'      => $position,
            'offset_bottom' => self::clampInt($raw['offset_bottom'] ?? $d['offset_bottom'], 0, 300),
            'offset_side'   => self::clampInt($raw['offset_side'] ?? $d['offset_side'], 0, 300),
            'size'          => self::clampInt($raw['size'] ?? $d['size'], 24, 96),
            'bg_color'      => self::sanitizeColor((string) ($raw['bg_color'] ?? ''), (string) $d['bg_color']),
            'icon_color'    => self::sanitizeColor((string) ($raw['icon_color'] ?? ''), (string) $d['icon_color']),
            'radius'        => self::clampInt($raw['radius'] ?? $d['radius'], 0, 50),
            'show_after'    => self::clampInt($raw['show_after'] ?? $d['show_after'], 0, 5000),
            'smooth'        => !empty($raw['smooth']),
        ];
    }

    /** @param mixed $value */
    private static function clampInt($value, int $min, int $max): int
    {
        return max($min, min($max, (int) $value));
    }

    private static function sanitizeColor(string $value, string $fallback): string
    {
        $clean = sanitize_hex_color($value);

        return is_string($clean) && $clean !== '' ? $clean : $fallback;
    }
}
