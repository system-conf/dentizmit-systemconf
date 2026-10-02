<?php

declare(strict_types=1);

namespace Systemconf\Popup;

/**
 * Popup modülünün ayar şeması: varsayılanlar, okuma ve sınırda doğrulama.
 * Tüm alanlar tek tek açıkça eşlenir; bilinmeyen anahtar kabul edilmez.
 *
 * Varsayılanlar, Popup Maker'daki "Başiskele Şubemiz Açıldı" popup'ının
 * (ID 4202) birebir karşılığıdır.
 */
final class Config
{
    public const OPTION = 'systemconf_popup';

    public const SHOW_ALL = 'all';
    public const SHOW_HOME = 'home';
    public const SHOW_PAGES = 'pages';

    public const TRIGGER_LOAD = 'load';
    public const TRIGGER_EXIT = 'exit';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'       => true,
            'title'         => 'Başiskele Şubemiz Açıldı',
            'content'       => '<img class="aligncenter size-large" src="https://dentizmit.com/wp-content/uploads/2026/04/basikele-sube-1024x1024.jpeg" alt="Başiskele Şubemiz Açıldı" width="800" height="800" />',
            'image_url'     => '',
            'delay_seconds' => 0.5,
            'repeat_days'   => 30,
            'show_on'       => self::SHOW_ALL,
            'page_ids'      => [],
            'trigger'       => self::TRIGGER_LOAD,
            'width_px'      => 520,
            'bg_color'      => '#ffffff',
            'accent_color'  => '#000000',
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

        $showOn = isset($raw['show_on']) ? sanitize_key((string) $raw['show_on']) : $d['show_on'];
        if (!in_array($showOn, [self::SHOW_ALL, self::SHOW_HOME, self::SHOW_PAGES], true)) {
            $showOn = $d['show_on'];
        }

        $trigger = isset($raw['trigger']) ? sanitize_key((string) $raw['trigger']) : $d['trigger'];
        if (!in_array($trigger, [self::TRIGGER_LOAD, self::TRIGGER_EXIT], true)) {
            $trigger = $d['trigger'];
        }

        return [
            'enabled'       => !empty($raw['enabled']),
            'title'         => sanitize_text_field((string) ($raw['title'] ?? $d['title'])),
            'content'       => wp_kses_post((string) ($raw['content'] ?? $d['content'])),
            'image_url'     => esc_url_raw((string) ($raw['image_url'] ?? $d['image_url'])),
            'delay_seconds' => self::clampFloat($raw['delay_seconds'] ?? $d['delay_seconds'], 0.0, 60.0),
            'repeat_days'   => self::clampInt($raw['repeat_days'] ?? $d['repeat_days'], 0, 365),
            'show_on'       => $showOn,
            'page_ids'      => self::sanitizeIds($raw['page_ids'] ?? $d['page_ids']),
            'trigger'       => $trigger,
            'width_px'      => self::clampInt($raw['width_px'] ?? $d['width_px'], 240, 1400),
            'bg_color'      => self::sanitizeColor((string) ($raw['bg_color'] ?? ''), (string) $d['bg_color']),
            'accent_color'  => self::sanitizeColor((string) ($raw['accent_color'] ?? ''), (string) $d['accent_color']),
        ];
    }

    /**
     * İçerik değişince eski "kapattı" kaydı geçersiz olsun diye kısa bir parmak izi.
     *
     * @param array<string, mixed> $config
     */
    public static function fingerprint(array $config): string
    {
        return substr(md5((string) $config['content'] . '|' . (string) $config['image_url']), 0, 8);
    }

    /**
     * "12, 15" gibi metni ya da diziyi tekil, pozitif tam sayı listesine çevirir.
     *
     * @param mixed $value
     * @return array<int, int>
     */
    private static function sanitizeIds($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\s,;]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $item) {
            $id = absint($item);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /** @param mixed $value */
    private static function clampInt($value, int $min, int $max): int
    {
        return max($min, min($max, (int) $value));
    }

    /** @param mixed $value */
    private static function clampFloat($value, float $min, float $max): float
    {
        $number = is_numeric($value) ? (float) $value : $min;

        return max($min, min($max, $number));
    }

    private static function sanitizeColor(string $value, string $fallback): string
    {
        $clean = sanitize_hex_color($value);

        return is_string($clean) && $clean !== '' ? $clean : $fallback;
    }
}
