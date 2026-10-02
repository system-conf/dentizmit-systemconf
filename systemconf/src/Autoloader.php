<?php

declare(strict_types=1);

namespace Systemconf;

/**
 * Systemconf ad alanındaki sınıfları src/ klasöründen yükler.
 * Örnek: Systemconf\Whatsapp\Button -> src/Whatsapp/Button.php
 */
final class Autoloader
{
    private const PREFIX = 'Systemconf\\';

    public static function register(string $baseDir): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir): void {
            if (strpos($class, self::PREFIX) !== 0) {
                return;
            }

            $relative = substr($class, strlen(self::PREFIX));
            $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_readable($file)) {
                require_once $file;
            }
        });
    }
}
