<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Form modülünün ayar şeması: alıcılar, gönderen adı, mesajlar.
 */
final class Config
{
    public const OPTION = 'systemconf_forms';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'recipients'      => 'info@dentizmit.com',
            'cc'              => 'mangomedya@yandex.com',
            'from_name'       => 'Dent İzmit Web Sitesi',
            'success_message' => 'Mesajınız alındı, en kısa sürede sizinle iletişime geçeceğiz.',
            'error_message'   => 'Bir sorun oluştu, lütfen biraz sonra tekrar deneyin ya da bizi arayın.',
            'max_upload_mb'   => 5,
            'rate_limit'      => 5,
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
            'recipients'      => self::sanitizeEmailList((string) ($raw['recipients'] ?? $d['recipients'])),
            'cc'              => self::sanitizeEmailList((string) ($raw['cc'] ?? '')),
            'from_name'       => sanitize_text_field((string) ($raw['from_name'] ?? $d['from_name'])),
            'success_message' => sanitize_text_field((string) ($raw['success_message'] ?? $d['success_message'])),
            'error_message'   => sanitize_text_field((string) ($raw['error_message'] ?? $d['error_message'])),
            'max_upload_mb'   => max(1, min(20, (int) ($raw['max_upload_mb'] ?? $d['max_upload_mb']))),
            'rate_limit'      => max(1, min(60, (int) ($raw['rate_limit'] ?? $d['rate_limit']))),
        ];
    }

    /** @return array<int, string> */
    public static function recipientList(string $value): array
    {
        $out = [];
        foreach (explode(',', $value) as $item) {
            $email = sanitize_email(trim($item));
            if ($email !== '' && is_email($email)) {
                $out[] = $email;
            }
        }

        return array_values(array_unique($out));
    }

    private static function sanitizeEmailList(string $value): string
    {
        return implode(', ', self::recipientList($value));
    }
}
