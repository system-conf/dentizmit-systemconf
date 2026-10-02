<?php

declare(strict_types=1);

namespace Systemconf\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Systemconf\ModuleInterface;

/**
 * E-posta modülü: WordPress'in tüm e-postalarını SMTP üzerinden gönderir.
 * Sunucunun yerel mail() işlevi kapalı olduğu için gerekli.
 */
final class Module implements ModuleInterface
{
    private const TEST_ACTION = 'systemconf_mail_test';
    private const NOTICE_TRANSIENT = 'systemconf_mail_test_result';

    public function slug(): string
    {
        return 'mail';
    }

    public function title(): string
    {
        return 'E-posta (SMTP)';
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('phpmailer_init', [$this, 'configureMailer']);
        add_filter('wp_mail_from', [$this, 'fromEmail']);
        add_filter('wp_mail_from_name', [$this, 'fromName']);
        add_action('admin_post_' . self::TEST_ACTION, [$this, 'sendTest']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_mail_group',
            Config::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [Config::class, 'sanitizeForSave'],
                'default'           => Config::defaults(),
            ]
        );
    }

    public function renderSettings(): void
    {
        (new Settings(self::TEST_ACTION, self::NOTICE_TRANSIENT))->render();
    }

    public function configureMailer(PHPMailer $mailer): void
    {
        $c = Config::load();

        if (!$c['enabled'] || $c['host'] === '' || $c['username'] === '') {
            return;
        }

        $mailer->isSMTP();
        $mailer->Host = (string) $c['host'];
        $mailer->Port = (int) $c['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = (string) $c['username'];
        $mailer->Password = (string) $c['password'];
        $mailer->SMTPSecure = $c['encryption'] === 'none' ? '' : (string) $c['encryption'];
        $mailer->SMTPAutoTLS = $c['encryption'] !== 'none';
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = 15; // Sunucu yanıt vermezse sayfa dakikalarca askıda kalmasın.
    }

    public function fromEmail(string $email): string
    {
        $c = Config::load();

        return $c['enabled'] && $c['from_email'] !== '' ? (string) $c['from_email'] : $email;
    }

    public function fromName(string $name): string
    {
        $c = Config::load();

        return $c['enabled'] && $c['from_name'] !== '' ? (string) $c['from_name'] : $name;
    }

    /** Panelden "test e-postası gönder" isteği. */
    public function sendTest(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Yetkiniz yok.', 'systemconf'));
        }

        check_admin_referer(self::TEST_ACTION);

        $to = sanitize_email((string) ($_POST['test_to'] ?? ''));
        if (!is_email($to)) {
            $to = (string) get_option('admin_email');
        }

        $sent = wp_mail(
            $to,
            'Systemconf SMTP testi - ' . get_bloginfo('name'),
            "Bu bir test e-postasıdır. Bunu görüyorsanız SMTP ayarları çalışıyor.\n\nGönderim zamanı: " . current_time('d.m.Y H:i')
        );

        $error = get_option(\Systemconf\Forms\Mailer::LAST_ERROR_OPTION);
        $detail = $sent ? '' : (is_array($error) ? (string) ($error['message'] ?? '') : '');

        set_transient(self::NOTICE_TRANSIENT, ['ok' => $sent, 'to' => $to, 'detail' => $detail], MINUTE_IN_SECONDS);

        wp_safe_redirect(add_query_arg(['page' => 'systemconf', 'tab' => $this->slug()], admin_url('admin.php')));
        exit;
    }
}
