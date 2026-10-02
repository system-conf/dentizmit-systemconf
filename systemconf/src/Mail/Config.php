<?php

declare(strict_types=1);

namespace Systemconf\Mail;

/**
 * SMTP ayar şeması. Şifre veritabanında saklanır, koda ya da kayıtlara yazılmaz.
 */
final class Config
{
    public const OPTION = 'systemconf_mail';

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'enabled'    => false,
            'host'       => 'localhost',
            'port'       => 465,
            'encryption' => 'ssl',
            'username'   => '',
            'password'   => '',
            'from_email' => '',
            'from_name'  => get_bloginfo('name'),
        ];
    }

    /** @return array<string, mixed> */
    public static function load(): array
    {
        $stored = get_option(self::OPTION, []);

        if (!is_array($stored)) {
            $stored = [];
        }

        return self::sanitize(array_merge(self::defaults(), $stored), $stored);
    }

    /**
     * @param mixed                $raw
     * @param array<string, mixed> $previous boş şifre gönderilirse eski şifre korunur
     * @return array<string, mixed>
     */
    public static function sanitize($raw, array $previous = []): array
    {
        $raw = is_array($raw) ? $raw : [];
        $d = self::defaults();

        $encryption = isset($raw['encryption']) ? sanitize_key((string) $raw['encryption']) : $d['encryption'];
        if (!in_array($encryption, ['none', 'ssl', 'tls'], true)) {
            $encryption = $d['encryption'];
        }

        $password = (string) ($raw['password'] ?? '');
        if ($password === '' && isset($previous['password'])) {
            $password = (string) $previous['password'];
        }

        $fromEmail = sanitize_email((string) ($raw['from_email'] ?? ''));

        return [
            'enabled'    => !empty($raw['enabled']),
            'host'       => sanitize_text_field((string) ($raw['host'] ?? $d['host'])),
            'port'       => max(1, min(65535, (int) ($raw['port'] ?? $d['port']))),
            'encryption' => $encryption,
            'username'   => sanitize_text_field((string) ($raw['username'] ?? '')),
            'password'   => $password,
            'from_email' => is_email($fromEmail) ? $fromEmail : '',
            'from_name'  => sanitize_text_field((string) ($raw['from_name'] ?? $d['from_name'])),
        ];
    }

    /** Settings API kaydı için: formdan gelen veriyi mevcut kayıtla birleştirir. */
    public static function sanitizeForSave($raw): array
    {
        $previous = get_option(self::OPTION, []);

        return self::sanitize($raw, is_array($previous) ? $previous : []);
    }
}
