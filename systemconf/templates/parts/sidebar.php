<?php
/**
 * Arama kutusu + "Önerilenler" listesi.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>
<aside class="sct-sidebar">
    <div class="sct-sidebar__search"><?php echo do_shortcode('[systemconf_arama]'); ?></div>
    <h3 class="sct-sidebar__title"><?php esc_html_e('Önerilenler', 'systemconf'); ?></h3>
    <?php echo do_shortcode('[systemconf_yazilar skin="list" count="5" exclude_current="1"]'); ?>
</aside>
