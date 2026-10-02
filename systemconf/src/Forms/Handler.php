<?php

declare(strict_types=1);

namespace Systemconf\Forms;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Form gönderimini alan REST ucu: spam tuzağı, hız sınırı, doğrulama,
 * çift gönderim koruması, kayıt ve e-posta.
 */
final class Handler
{
    public const ROUTE_NAMESPACE = 'systemconf/v1';

    public function registerRoutes(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, '/forms/(?P<form>[a-z0-9_]+)', [
            'methods'             => 'POST',
            'callback'            => [$this, 'submit'],
            'permission_callback' => '__return_true',
        ]);
    }

    /** @return WP_REST_Response|WP_Error */
    public function submit(WP_REST_Request $request)
    {
        $formId = sanitize_key((string) $request->get_param('form'));
        $definition = Definitions::get($formId);

        if ($definition === null) {
            return $this->fail('Form bulunamadı.', 404);
        }

        $config = Config::load();
        $params = $request->get_params();

        // Spam tuzağı: gerçek kullanıcılar bu gizli alanı doldurmaz.
        if (!empty($params['sc_website'])) {
            return $this->ok($config['success_message']); // Botu bilgilendirmeden sessizce geç.
        }

        // En az 3 saniye formda kalınmış olmalı.
        $rendered = (int) ($params['sc_ts'] ?? 0);
        if ($rendered <= 0 || time() - $rendered < 3) {
            return $this->fail('Lütfen formu tekrar gönderin.', 429);
        }

        $ip = $this->clientIp();
        if (!$this->withinRateLimit($ip, (int) $config['rate_limit'])) {
            return $this->fail('Çok fazla deneme yaptınız, lütfen birkaç dakika sonra tekrar deneyin.', 429);
        }

        $validator = new Validator();
        if (!$validator->run($definition, $params)) {
            return new WP_REST_Response([
                'ok'      => false,
                'message' => 'Lütfen işaretli alanları kontrol edin.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $payload = $validator->clean();
        $attachment = null;

        $fileField = $this->fileField($definition);
        if ($fileField !== null) {
            $files = $request->get_file_params();
            $stored = (new Uploads())->store($files[$fileField['id']] ?? [], (int) $config['max_upload_mb']);

            if ($stored instanceof WP_Error) {
                if (!empty($fileField['required']) || $stored->get_error_code() !== 'no_file') {
                    return new WP_REST_Response([
                        'ok'      => false,
                        'message' => 'Lütfen işaretli alanları kontrol edin.',
                        'errors'  => [$fileField['id'] => $stored->get_error_message()],
                    ], 422);
                }
            } else {
                $attachment = $stored;
                $payload[$fileField['id']] = basename($stored);
            }
        }

        $token = sanitize_text_field((string) ($params['sc_token'] ?? ''));
        $dedupKey = hash('sha256', $formId . '|' . ($token !== '' ? $token : wp_json_encode($payload) . '|' . $ip));

        $userAgent = (string) ($request->get_header('user_agent') ?? '');
        $id = Repository::insert($formId, $dedupKey, $payload, $attachment, $ip, $userAgent);

        if ($id === null) {
            // Aynı gönderim daha önce kaydedildi; kullanıcıya başarı mesajı yeter.
            return $this->ok($config['success_message']);
        }

        $sent = (new Mailer())->send($definition, $payload, $attachment, $config);

        if ($sent) {
            Repository::markMailSent($id);
        }

        // Kayıt veritabanında; e-posta gitmese bile talep kaybolmadı, panelde görünür.
        return $this->ok($config['success_message']);
    }

    /** @param array<string, mixed> $definition
     *  @return array<string, mixed>|null */
    private function fileField(array $definition): ?array
    {
        foreach ($definition['fields'] as $field) {
            if ($field['type'] === 'file') {
                return $field;
            }
        }

        return null;
    }

    private function withinRateLimit(string $ip, int $limit): bool
    {
        $key = 'systemconf_form_rl_' . md5($ip);
        $count = (int) get_transient($key);

        if ($count >= $limit) {
            return false;
        }

        set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);

        return true;
    }

    private function clientIp(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }

    private function ok(string $message): WP_REST_Response
    {
        return new WP_REST_Response(['ok' => true, 'message' => $message], 200);
    }

    private function fail(string $message, int $status): WP_REST_Response
    {
        return new WP_REST_Response(['ok' => false, 'message' => $message], $status);
    }
}
