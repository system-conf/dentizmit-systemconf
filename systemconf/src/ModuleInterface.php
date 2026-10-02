<?php

declare(strict_types=1);

namespace Systemconf;

/**
 * Her modül kendi kancalarını register() içinde bağlar ve
 * yönetim panelinde bir sekme olarak görünür.
 */
interface ModuleInterface
{
    /** Modülün makine adı (sekme anahtarı). */
    public function slug(): string;

    /** Yönetim panelinde görünen ad. */
    public function title(): string;

    /** WordPress kancalarını bağlar. */
    public function register(): void;

    /** Modülün ayar sayfasını çizer. */
    public function renderSettings(): void;
}
