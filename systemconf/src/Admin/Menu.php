<?php

declare(strict_types=1);

namespace Systemconf\Admin;

use Systemconf\ModuleInterface;

/**
 * Yönetim panelindeki "Systemconf" menüsü ve modül sekmeleri.
 */
final class Menu
{
    public const PAGE_SLUG = 'systemconf';

    /** @var array<int, ModuleInterface> */
    private array $modules;

    /** @param array<int, ModuleInterface> $modules */
    public function __construct(array $modules)
    {
        $this->modules = $modules;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
    }

    public function addMenu(): void
    {
        add_menu_page(
            'Systemconf',
            'Systemconf',
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage'],
            'dashicons-admin-generic',
            58
        );
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bu sayfaya erişim yetkiniz yok.', 'systemconf'));
        }

        $active = $this->activeModule();

        echo '<div class="wrap"><h1>Systemconf</h1>';
        $this->renderTabs($active);

        if ($active !== null) {
            $active->renderSettings();
        }

        echo '</div>';
    }

    private function activeModule(): ?ModuleInterface
    {
        $requested = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : '';

        foreach ($this->modules as $module) {
            if ($module->slug() === $requested) {
                return $module;
            }
        }

        return $this->modules[0] ?? null;
    }

    private function renderTabs(?ModuleInterface $active): void
    {
        echo '<h2 class="nav-tab-wrapper">';

        foreach ($this->modules as $module) {
            $url = add_query_arg(
                ['page' => self::PAGE_SLUG, 'tab' => $module->slug()],
                admin_url('admin.php')
            );
            $class = 'nav-tab' . ($active !== null && $active->slug() === $module->slug() ? ' nav-tab-active' : '');

            printf(
                '<a class="%s" href="%s">%s</a>',
                esc_attr($class),
                esc_url($url),
                esc_html($module->title())
            );
        }

        echo '</h2>';
    }
}
