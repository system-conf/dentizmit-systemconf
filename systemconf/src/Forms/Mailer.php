<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Form gönderimini e-posta olarak iletir.
 */
final class Mailer
{
    public const LAST_ERROR_OPTION = 'systemconf_forms_last_mail_error';

    /** wp_mail başarısız olduğunda teknik nedeni panelde göstermek için saklar. */
    public static function captureFailure(\WP_Error $error): void
    {
        $data = $error->get_error_data();
        update_option(self::LAST_ERROR_OPTION, [
            'message' => $error->get_error_message(),
            'code'    => $error->get_error_code(),
            'detail'  => is_array($data) && isset($data['phpmailer_exception_code']) ? (string) $data['phpmailer_exception_code'] : '',
            'time'    => current_time('mysql'),
        ], false);
        error_log('[systemconf] wp_mail hatası: ' . $error->get_error_message());
    }

    /**
     * @param array<string, mixed>  $definition
     * @param array<string, string> $payload  alan id => değer
     * @param array<string, mixed>  $config
     */
    public function send(array $definition, array $payload, ?string $attachmentPath, array $config): bool
    {
        $to = Config::recipientList((string) $config['recipients']);

        if ($to === []) {
            error_log('[systemconf] Form e-postası gönderilemedi: alıcı tanımlı değil.');
            return false;
        }

        $labels = [];
        foreach ($definition['fields'] as $field) {
            $labels[(string) $field['id']] = (string) $field['label'];
        }

        $lines = [];
        foreach ($payload as $id => $value) {
            $lines[] = ($labels[$id] ?? $id) . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'Gönderim zamanı: ' . current_time('d.m.Y H:i');
        $lines[] = 'Site: ' . home_url('/');

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('From: %s <%s>', $this->sanitizeHeader((string) $config['from_name']), $this->fromAddress()),
        ];

        $replyTo = $payload['eposta'] ?? '';
        if ($replyTo !== '' && is_email($replyTo)) {
            $name = $this->sanitizeHeader($payload['ad_soyad'] ?? '');
            $headers[] = $name !== '' ? sprintf('Reply-To: %s <%s>', $name, $replyTo) : 'Reply-To: ' . $replyTo;
        }

        foreach (Config::recipientList((string) $config['cc']) as $cc) {
            $headers[] = 'Cc: ' . $cc;
        }

        $subject = $definition['subject'] . ' - ' . get_bloginfo('name');
        $attachments = $attachmentPath !== null && is_readable($attachmentPath) ? [$attachmentPath] : [];

        $sent = wp_mail($to, $subject, implode("\n", $lines), $headers, $attachments);

        if (!$sent) {
            error_log('[systemconf] wp_mail başarısız: form=' . ($definition['title'] ?? '?'));
        }

        return $sent;
    }

    /** Sitenin alan adıyla uyumlu, SPF'ye takılmayan bir gönderen adresi. */
    private function fromAddress(): string
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        $host = is_string($host) ? preg_replace('/^www\./', '', $host) : 'localhost';

        return 'noreply@' . $host;
    }

    private function sanitizeHeader(string $value): string
    {
        return trim(preg_replace('/[\r\n"<>]+/', ' ', $value) ?? '');
    }
}
