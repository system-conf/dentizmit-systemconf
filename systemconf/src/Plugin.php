<?php

declare(strict_types=1);

namespace Systemconf;

use Systemconf\Admin\Menu;
use Systemconf\Blocks\Module as BlocksModule;
use Systemconf\Forms\Module as FormsModule;
use Systemconf\Header\Module as HeaderModule;
use Systemconf\Mail\Module as MailModule;
use Systemconf\Theme\Module as ThemeModule;
use Systemconf\Tools\Module as ToolsModule;
use Systemconf\Whatsapp\Module as WhatsappModule;

/**
 * Eklentinin giriş noktası. Modülleri sırayla ayağa kaldırır.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    /** @var array<int, ModuleInterface> */
    private array $modules = [];

    public static function instance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->modules = [
            new WhatsappModule(),
            new FormsModule(),
            new MailModule(),
            new BlocksModule(),
            new ThemeModule(),
            new HeaderModule(),
            new ToolsModule(),
        ];
    }

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
        }

        if (is_admin()) {
            (new Menu($this->modules))->register();
        }
    }
}
