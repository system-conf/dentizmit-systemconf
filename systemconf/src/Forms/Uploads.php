<?php

declare(strict_types=1);

namespace Systemconf\Forms;

use WP_Error;

/**
 * Form dosya eklerini (özgeçmiş vb.) güvenli biçimde kaydeder.
 */
final class Uploads
{
    private const ALLOWED = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    /**
     * @param array<string, mixed> $file $_FILES öğesi
     * @return string|WP_Error kaydedilen dosyanın tam yolu
     */
    public function store(array $file, int $maxMb)
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return new WP_Error('no_file', 'Dosya seçilmedi.');
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'Dosya yüklenemedi, tekrar deneyin.');
        }

        if ((int) $file['size'] > $maxMb * 1024 * 1024) {
            return new WP_Error('too_large', sprintf('Dosya en fazla %d MB olabilir.', $maxMb));
        }

        $check = wp_check_filetype_and_ext((string) $file['tmp_name'], (string) $file['name'], self::ALLOWED);

        if (empty($check['ext']) || empty($check['type']) || !isset(self::ALLOWED[$check['ext']])) {
            return new WP_Error('bad_type', 'Yalnızca PDF, Word, JPG ve PNG dosyaları kabul edilir.');
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        add_filter('upload_dir', [$this, 'uploadDir']);
        $result = wp_handle_upload($file, [
            'test_form' => false,
            'mimes'     => self::ALLOWED,
            'unique_filename_callback' => [$this, 'uniqueName'],
        ]);
        remove_filter('upload_dir', [$this, 'uploadDir']);

        if (!is_array($result) || isset($result['error']) || empty($result['file'])) {
            $message = is_array($result) && isset($result['error']) ? (string) $result['error'] : 'Dosya kaydedilemedi.';
            error_log('[systemconf] Dosya yükleme hatası: ' . $message);

            return new WP_Error('store_failed', 'Dosya kaydedilemedi, tekrar deneyin.');
        }

        return (string) $result['file'];
    }

    /**
     * Ekler ayrı, listelenmeyen bir klasöre gider.
     *
     * @param array<string, mixed> $dirs
     * @return array<string, mixed>
     */
    public function uploadDir(array $dirs): array
    {
        $sub = '/systemconf-forms';
        $dirs['path'] = $dirs['basedir'] . $sub;
        $dirs['url'] = $dirs['baseurl'] . $sub;
        $dirs['subdir'] = $sub;

        if (!is_dir($dirs['path'])) {
            wp_mkdir_p($dirs['path']);
            file_put_contents($dirs['path'] . '/index.html', '');
            file_put_contents($dirs['path'] . '/.htaccess', "Options -Indexes\n<FilesMatch \"\\.(php|phtml|php5|phar)$\">\nDeny from all\n</FilesMatch>\n");
        }

        return $dirs;
    }

    public function uniqueName(string $dir, string $name, string $ext): string
    {
        $base = sanitize_file_name(pathinfo($name, PATHINFO_FILENAME));
        $base = mb_substr($base !== '' ? $base : 'dosya', 0, 40);

        return gmdate('Ymd-His') . '-' . wp_generate_password(8, false) . '-' . $base . $ext;
    }
}
