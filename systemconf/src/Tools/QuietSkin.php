<?php

declare(strict_types=1);

namespace Systemconf\Tools;

if (!class_exists('\WP_Upgrader_Skin')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

/**
 * Yükseltici çıktısını ekrana basmak yerine bellekte toplar (REST yanıtı için).
 */
final class QuietSkin extends \WP_Upgrader_Skin
{
    /** @var array<int, string> */
    private array $log = [];

    public function header(): void
    {
    }

    public function footer(): void
    {
    }

    /** @param string|\WP_Error $errors */
    public function error($errors): void
    {
        $this->log[] = 'HATA: ' . (is_wp_error($errors) ? $errors->get_error_message() : (string) $errors);
    }

    /** @param string $feedback */
    public function feedback($feedback, ...$args): void
    {
        if (isset($this->upgrader->strings[$feedback])) {
            $feedback = $this->upgrader->strings[$feedback];
        }

        if ($args !== []) {
            $feedback = vsprintf((string) $feedback, $args);
        }

        $this->log[] = wp_strip_all_tags((string) $feedback);
    }

    /** @return array<int, string> */
    public function messages(): array
    {
        return $this->log;
    }
}
