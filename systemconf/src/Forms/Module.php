<?php

declare(strict_types=1);

namespace Systemconf\Forms;

use Systemconf\ModuleInterface;

/**
 * Form modülü: kısa kod, varlıklar, REST ucu, ayar kaydı ve tablo kurulumu.
 */
final class Module implements ModuleInterface
{
    public function slug(): string
    {
        return 'forms';
    }

    public function title(): string
    {
        return 'Formlar';
    }

    public function register(): void
    {
        add_action('init', [Repository::class, 'ensureSchema']);
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('rest_api_init', [new Handler(), 'registerRoutes']);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_shortcode('systemconf_form', [$this, 'shortcode']);
    }

    public function registerSetting(): void
    {
        register_setting(
            'systemconf_forms_group',
            Config::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [Config::class, 'sanitize'],
                'default'           => Config::defaults(),
            ]
        );
    }

    public function renderSettings(): void
    {
        (new Settings())->render();
    }

    /** Varlıklar kaydedilir; yalnızca kısa kod kullanılan sayfada yüklenir. */
    public function registerAssets(): void
    {
        wp_register_style('systemconf-forms', SYSTEMCONF_URL . 'assets/forms/form.css', [], SYSTEMCONF_VERSION);
        wp_register_script('systemconf-forms', SYSTEMCONF_URL . 'assets/forms/form.js', [], SYSTEMCONF_VERSION, true);

        $config = Config::load();
        wp_localize_script('systemconf-forms', 'systemconfForms', [
            'endpoint'     => esc_url_raw(rest_url(Handler::ROUTE_NAMESPACE . '/forms/')),
            'errorMessage' => $config['error_message'],
            'sending'      => 'Gönderiliyor…',
        ]);
    }

    /** @param array<string, mixed>|string $atts */
    public function shortcode($atts): string
    {
        $atts = shortcode_atts(['id' => ''], is_array($atts) ? $atts : [], 'systemconf_form');
        $formId = sanitize_key((string) $atts['id']);
        $definition = Definitions::get($formId);

        if ($definition === null) {
            return current_user_can('manage_options')
                ? '<p><em>systemconf: "' . esc_html($formId) . '" adlı form tanımlı değil.</em></p>'
                : '';
        }

        wp_enqueue_style('systemconf-forms');
        wp_enqueue_script('systemconf-forms');

        return (new Renderer())->render($formId, $definition);
    }
}
