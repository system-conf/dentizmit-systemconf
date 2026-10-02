<?php

declare(strict_types=1);

namespace Systemconf\Tools;

use Systemconf\Forms\Mailer;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Yönetici REST uçları: systemconf ayarlarını okuma/yazma ve e-posta testi.
 * Yalnızca "systemconf_" ile başlayan ayarlara izin verilir.
 */
final class Options
{
    public const ROUTE_NAMESPACE = 'systemconf/v1';
    private const PREFIX = 'systemconf_';
    private const SECRET_KEYS = ['password'];

    public function registerRoutes(): void
    {
        $admin = static fn(): bool => current_user_can('manage_options');

        register_rest_route(self::ROUTE_NAMESPACE, '/option/(?P<name>[a-z0-9_]+)', [
            ['methods' => 'GET', 'callback' => [$this, 'read'], 'permission_callback' => $admin],
            ['methods' => 'POST', 'callback' => [$this, 'write'], 'permission_callback' => $admin],
        ]);

        register_rest_route(self::ROUTE_NAMESPACE, '/mail-test', [
            'methods'             => 'POST',
            'callback'            => [$this, 'mailTest'],
            'permission_callback' => $admin,
        ]);
    }

    /** Ayarı döndürür; şifre alanları maskelenir. */
    public function read(WP_REST_Request $request)
    {
        $name = $this->allowedName($request);
        if ($name instanceof WP_Error) {
            return $name;
        }

        return new WP_REST_Response(['name' => $name, 'value' => $this->mask(get_option($name))]);
    }

    /**
     * Ayarı yazar. Gövde: {value: ..., merge: true|false}
     * merge=true ise dizi ayarlarda yalnızca gönderilen anahtarlar değişir (şifre korunur).
     */
    public function write(WP_REST_Request $request)
    {
        $name = $this->allowedName($request);
        if ($name instanceof WP_Error) {
            return $name;
        }

        $value = $request->get_param('value');
        $merge = (bool) $request->get_param('merge');

        if ($value === null) {
            return new WP_Error('systemconf_bad_request', 'value zorunlu.', ['status' => 400]);
        }

        $current = get_option($name);

        if ($merge) {
            if (!is_array($value)) {
                return new WP_Error('systemconf_bad_request', 'merge için value bir nesne olmalı.', ['status' => 400]);
            }
            $value = array_merge(is_array($current) ? $current : [], $value);
        }

        $sanitized = $this->sanitizeViaRegisteredCallback($name, $value);
        update_option($name, $sanitized);

        return new WP_REST_Response(['name' => $name, 'value' => $this->mask(get_option($name))]);
    }

    /** Gövde: {to: "adres"} — wp_mail ile test gönderir, sonucu ve son hatayı döndürür. */
    public function mailTest(WP_REST_Request $request): WP_REST_Response
    {
        $to = sanitize_email((string) $request->get_param('to'));
        if (!is_email($to)) {
            $to = (string) get_option('admin_email');
        }

        delete_option(Mailer::LAST_ERROR_OPTION);

        $start = microtime(true);
        $sent = wp_mail(
            $to,
            'Systemconf SMTP testi - ' . get_bloginfo('name'),
            "Bu bir test e-postasıdır. Görüyorsanız SMTP ayarları çalışıyor.\nZaman: " . current_time('d.m.Y H:i')
        );

        $error = get_option(Mailer::LAST_ERROR_OPTION);

        return new WP_REST_Response([
            'sent'     => $sent,
            'to'       => $to,
            'seconds'  => round(microtime(true) - $start, 1),
            'error'    => is_array($error) ? ($error['message'] ?? '') : '',
        ]);
    }

    /** @return string|WP_Error */
    private function allowedName(WP_REST_Request $request)
    {
        $name = sanitize_key((string) $request->get_param('name'));

        if (strpos($name, self::PREFIX) !== 0) {
            return new WP_Error('systemconf_forbidden_option', 'Yalnızca systemconf_ ayarları.', ['status' => 403]);
        }

        return $name;
    }

    /**
     * register_setting ile kayıtlı temizleme işlevini uygular (Settings API ile aynı yol).
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitizeViaRegisteredCallback(string $name, $value)
    {
        return apply_filters('sanitize_option_' . $name, $value, $name, $value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function mask($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach (self::SECRET_KEYS as $key) {
            if (isset($value[$key]) && $value[$key] !== '') {
                $value[$key] = '********';
            }
        }

        return $value;
    }
}
