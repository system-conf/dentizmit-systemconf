<?php

declare(strict_types=1);

namespace Systemconf\ScrollTop;

/**
 * Yukarı çık düğmesinin HTML çıktısı.
 */
final class View
{
    public function render(): void
    {
        ?>
        <button type="button" class="scst scst--hidden" id="scst" data-systemconf-scrolltop aria-label="<?php esc_attr_e('Sayfanın başına çık', 'systemconf'); ?>">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg>
        </button>
        <?php
    }
}
