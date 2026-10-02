<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Gelen form verisini tanıma göre alan alan doğrular ve temizler.
 */
final class Validator
{
    /** @var array<string, string> alan id => hata mesajı */
    private array $errors = [];

    /** @var array<string, string> alan id => temiz değer */
    private array $clean = [];

    /**
     * @param array<string, mixed> $definition
     * @param array<string, mixed> $input
     */
    public function run(array $definition, array $input): bool
    {
        $this->errors = [];
        $this->clean = [];

        foreach ($definition['fields'] as $field) {
            $id = (string) $field['id'];
            $type = (string) $field['type'];

            if ($type === 'file') {
                continue; // Dosya alanı Handler tarafından ayrıca işlenir.
            }

            $raw = isset($input[$id]) ? (string) $input[$id] : '';
            $value = $this->sanitizeByType($type, $raw);

            if ($type === 'checkbox') {
                $value = $raw !== '' ? 'Evet' : '';
            }

            if (!empty($field['required']) && $value === '') {
                $this->errors[$id] = 'Bu alan zorunludur.';
                continue;
            }

            if ($value !== '' && !$this->isValidByType($type, $value, $field)) {
                $this->errors[$id] = $this->messageByType($type);
                continue;
            }

            $this->clean[$id] = $value;
        }

        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, string> */
    public function clean(): array
    {
        return $this->clean;
    }

    private function sanitizeByType(string $type, string $raw): string
    {
        switch ($type) {
            case 'textarea':
                return trim(sanitize_textarea_field($raw));
            case 'email':
                return trim(sanitize_email($raw));
            default:
                return trim(sanitize_text_field($raw));
        }
    }

    /** @param array<string, mixed> $field */
    private function isValidByType(string $type, string $value, array $field): bool
    {
        switch ($type) {
            case 'email':
                return (bool) is_email($value);
            case 'tel':
                $digits = preg_replace('/\D+/', '', $value) ?? '';
                return strlen($digits) >= 10 && strlen($digits) <= 15;
            case 'date':
                $dt = \DateTime::createFromFormat('Y-m-d', $value);
                return $dt !== false && $dt->format('Y-m-d') === $value;
            case 'time':
                return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value);
            case 'select':
                return in_array($value, $field['options'] ?? [], true);
            case 'text':
                return mb_strlen($value) <= 200;
            case 'textarea':
                return mb_strlen($value) <= 3000;
            default:
                return true;
        }
    }

    private function messageByType(string $type): string
    {
        switch ($type) {
            case 'email':
                return 'Geçerli bir e-posta adresi girin.';
            case 'tel':
                return 'Geçerli bir telefon numarası girin.';
            case 'date':
                return 'Geçerli bir tarih seçin.';
            case 'time':
                return 'Geçerli bir saat seçin.';
            case 'select':
                return 'Listeden bir seçim yapın.';
            default:
                return 'Bu alan çok uzun.';
        }
    }
}
