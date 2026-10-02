<?php

declare(strict_types=1);

namespace Systemconf\Mail;

/**
 * SMTP ayar formu ve test e-postası düğmesi.
 */
final class Settings
{
    private string $testAction;
    private string $noticeTransient;

    public function __construct(string $testAction, string $noticeTransient)
    {
        $this->testAction = $testAction;
        $this->noticeTransient = $noticeTransient;
    }

    public function render(): void
    {
        $this->renderNotice();
        $this->renderForm();
        $this->renderTest();
    }

    private function renderNotice(): void
    {
        $result = get_transient($this->noticeTransient);
        if (!is_array($result)) {
            return;
        }
        delete_transient($this->noticeTransient);

        if (!empty($result['ok'])) {
            printf('<div class="notice notice-success inline"><p>%s <strong>%s</strong></p></div>', esc_html__('Test e-postası gönderildi:', 'systemconf'), esc_html((string) $result['to']));
            return;
        }

        printf(
            '<div class="notice notice-error inline"><p>%s<br><code>%s</code></p></div>',
            esc_html__('Test e-postası gönderilemedi.', 'systemconf'),
            esc_html((string) ($result['detail'] ?? ''))
        );
    }

    private function renderForm(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        ?>
        <p><?php esc_html_e('Sunucunun yerel posta işlevi kapalı. Buraya bir posta kutusu girildiğinde formlar, şifre sıfırlama ve diğer tüm site e-postaları bu hesap üzerinden gönderilir.', 'systemconf'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_mail_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('SMTP açık', 'systemconf'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($c['enabled']); ?>> <?php esc_html_e('E-postaları SMTP ile gönder', 'systemconf'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-host"><?php esc_html_e('Sunucu', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scm-host" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[host]" value="<?php echo esc_attr((string) $c['host']); ?>">
                        <p class="description"><?php esc_html_e('cPanel posta kutuları için genellikle: mail.dentizmit.com ya da localhost', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-port"><?php esc_html_e('Port ve şifreleme', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scm-port" type="number" min="1" max="65535" name="<?php echo esc_attr($name); ?>[port]" value="<?php echo esc_attr((string) $c['port']); ?>">
                        <select name="<?php echo esc_attr($name); ?>[encryption]">
                            <option value="ssl" <?php selected($c['encryption'], 'ssl'); ?>>SSL (465)</option>
                            <option value="tls" <?php selected($c['encryption'], 'tls'); ?>>TLS (587)</option>
                            <option value="none" <?php selected($c['encryption'], 'none'); ?>><?php esc_html_e('Yok (25)', 'systemconf'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-user"><?php esc_html_e('Kullanıcı adı (e-posta)', 'systemconf'); ?></label></th>
                    <td><input id="scm-user" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[username]" value="<?php echo esc_attr((string) $c['username']); ?>" autocomplete="off"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-pass"><?php esc_html_e('Şifre', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scm-pass" class="regular-text" type="password" name="<?php echo esc_attr($name); ?>[password]" value="" autocomplete="new-password" placeholder="<?php echo $c['password'] !== '' ? esc_attr__('(kayıtlı, değiştirmek için yazın)', 'systemconf') : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-from"><?php esc_html_e('Gönderen adresi', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scm-from" class="regular-text" type="email" name="<?php echo esc_attr($name); ?>[from_email]" value="<?php echo esc_attr((string) $c['from_email']); ?>">
                        <p class="description"><?php esc_html_e('Çoğu sunucu kullanıcı adıyla aynı adresi ister.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scm-fromname"><?php esc_html_e('Gönderen adı', 'systemconf'); ?></label></th>
                    <td><input id="scm-fromname" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[from_name]" value="<?php echo esc_attr((string) $c['from_name']); ?>"></td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }

    private function renderTest(): void
    {
        ?>
        <h2><?php esc_html_e('Test e-postası', 'systemconf'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr($this->testAction); ?>">
            <?php wp_nonce_field($this->testAction); ?>
            <input type="email" name="test_to" class="regular-text" placeholder="<?php echo esc_attr((string) get_option('admin_email')); ?>">
            <?php submit_button(__('Test gönder', 'systemconf'), 'secondary', 'submit', false); ?>
        </form>
        <?php
    }
}
