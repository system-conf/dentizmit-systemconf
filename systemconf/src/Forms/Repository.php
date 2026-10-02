<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Form gönderimlerinin veritabanı kaydı.
 * Çift gönderim koruması uygulama kontrolüyle değil, dedup_key üzerindeki
 * UNIQUE kısıtıyla sağlanır; kontrol ve yazma tek INSERT içinde olur.
 */
final class Repository
{
    private const SCHEMA_VERSION = '1';
    private const SCHEMA_OPTION = 'systemconf_forms_schema';

    public static function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'systemconf_form_submissions';
    }

    /** Tablo yoksa ya da şema eskiyse oluşturur/günceller. */
    public static function ensureSchema(): void
    {
        if (get_option(self::SCHEMA_OPTION) === self::SCHEMA_VERSION) {
            return;
        }

        global $wpdb;
        $table = self::tableName();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id VARCHAR(40) NOT NULL,
            dedup_key CHAR(64) NOT NULL,
            payload LONGTEXT NOT NULL,
            attachment VARCHAR(500) NULL,
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            mail_sent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY dedup_key (dedup_key),
            KEY form_id (form_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::SCHEMA_OPTION, self::SCHEMA_VERSION, false);
    }

    /**
     * Kaydı ekler. Aynı dedup_key daha önce yazıldıysa null döner (çift gönderim).
     *
     * @param array<string, string> $payload
     */
    public static function insert(string $formId, string $dedupKey, array $payload, ?string $attachment, string $ip, string $userAgent): ?int
    {
        global $wpdb;

        $wpdb->suppress_errors(true);
        $inserted = $wpdb->insert(
            self::tableName(),
            [
                'form_id'    => $formId,
                'dedup_key'  => $dedupKey,
                'payload'    => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
                'attachment' => $attachment,
                'ip'         => $ip,
                'user_agent' => mb_substr($userAgent, 0, 255),
                'mail_sent'  => 0,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
        $wpdb->suppress_errors(false);

        if ($inserted === false) {
            return null;
        }

        return (int) $wpdb->insert_id;
    }

    public static function markMailSent(int $id): void
    {
        global $wpdb;

        $wpdb->update(self::tableName(), ['mail_sent' => 1], ['id' => $id], ['%d'], ['%d']);
    }

    /** @return array<int, array<string, mixed>> */
    public static function latest(int $limit = 50): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, form_id, payload, attachment, mail_sent, created_at FROM ' . self::tableName() . ' ORDER BY id DESC LIMIT %d',
                $limit
            ),
            ARRAY_A
        );

        return is_array($rows) ? $rows : [];
    }
}
