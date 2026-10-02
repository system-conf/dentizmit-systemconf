<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Form modülünün yönetim paneli: ayarlar ve son gönderimler.
 */
final class Settings
{
    public function render(): void
    {
        $this->renderForm();
        $this->renderUsage();
        $this->renderSubmissions();
    }

    private function renderForm(): void
    {
        $c = Config::load();
        $name = Config::OPTION;
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('systemconf_forms_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="scf-recipients"><?php esc_html_e('Alıcı e-postalar', 'systemconf'); ?></label></th>
                    <td>
                        <input id="scf-recipients" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[recipients]" value="<?php echo esc_attr((string) $c['recipients']); ?>">
                        <p class="description"><?php esc_html_e('Virgülle ayırarak birden fazla adres yazabilirsiniz.', 'systemconf'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="scf-cc"><?php esc_html_e('Kopya (CC)', 'systemconf'); ?></label></th>
                    <td><input id="scf-cc" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[cc]" value="<?php echo esc_attr((string) $c['cc']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scf-from"><?php esc_html_e('Gönderen adı', 'systemconf'); ?></label></th>
                    <td><input id="scf-from" class="regular-text" type="text" name="<?php echo esc_attr($name); ?>[from_name]" value="<?php echo esc_attr((string) $c['from_name']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scf-success"><?php esc_html_e('Başarı mesajı', 'systemconf'); ?></label></th>
                    <td><input id="scf-success" class="large-text" type="text" name="<?php echo esc_attr($name); ?>[success_message]" value="<?php echo esc_attr((string) $c['success_message']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="scf-error"><?php esc_html_e('Hata mesajı', 'systemconf'); ?></label></th>
                    <td><input id="scf-error" class="large-text" type="text" name="<?php echo esc_attr($name); ?>[error_message]" value="<?php echo esc_attr((string) $c['error_message']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Sınırlar', 'systemconf'); ?></th>
                    <td>
                        <label><?php esc_html_e('Dosya üst sınırı (MB)', 'systemconf'); ?> <input type="number" min="1" max="20" name="<?php echo esc_attr($name); ?>[max_upload_mb]" value="<?php echo esc_attr((string) $c['max_upload_mb']); ?>"></label>&nbsp;&nbsp;
                        <label><?php esc_html_e('Aynı adresten 10 dakikada en fazla gönderim', 'systemconf'); ?> <input type="number" min="1" max="60" name="<?php echo esc_attr($name); ?>[rate_limit]" value="<?php echo esc_attr((string) $c['rate_limit']); ?>"></label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Kaydet', 'systemconf')); ?>
        </form>
        <?php
    }

    private function renderUsage(): void
    {
        echo '<h2>' . esc_html__('Kısa kodlar', 'systemconf') . '</h2><ul>';
        foreach (Definitions::all() as $id => $def) {
            printf('<li><code>[systemconf_form id="%s"]</code> — %s</li>', esc_html($id), esc_html((string) $def['title']));
        }
        echo '</ul>';
    }

    private function renderSubmissions(): void
    {
        $rows = Repository::latest(50);

        echo '<h2>' . esc_html__('Son gönderimler', 'systemconf') . '</h2>';

        if ($rows === []) {
            echo '<p>' . esc_html__('Henüz gönderim yok.', 'systemconf') . '</p>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__('Form', 'systemconf') . '</th><th>' . esc_html__('İçerik', 'systemconf') . '</th><th>' . esc_html__('E-posta', 'systemconf') . '</th><th>' . esc_html__('Tarih', 'systemconf') . '</th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $payload = json_decode((string) $row['payload'], true);
            $summary = [];
            if (is_array($payload)) {
                foreach ($payload as $key => $value) {
                    $summary[] = '<strong>' . esc_html((string) $key) . ':</strong> ' . esc_html(mb_substr((string) $value, 0, 120));
                }
            }

            printf(
                '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                (int) $row['id'],
                esc_html((string) $row['form_id']),
                implode('<br>', $summary),
                (int) $row['mail_sent'] === 1 ? esc_html__('Gönderildi', 'systemconf') : esc_html__('Gönderilemedi', 'systemconf'),
                esc_html((string) $row['created_at'])
            );
        }

        echo '</tbody></table>';
    }
}
