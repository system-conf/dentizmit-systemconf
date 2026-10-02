<?php
/**
 * Sayfa bulunamadı.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$title = 'Sayfa Bulunamadı';
$subtitle = '';
$crumbs = [
    ['label' => 'Anasayfa', 'url' => home_url('/')],
    ['label' => '404', 'url' => ''],
];
include SYSTEMCONF_DIR . 'templates/parts/hero.php';
?>
<div class="sct-container sct-404">
    <p class="sct-404__code">404</p>
    <p class="sct-404__text"><?php esc_html_e('Aradığınız sayfa taşınmış ya da kaldırılmış olabilir.', 'systemconf'); ?></p>
    <a class="sct-button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Ana Sayfaya Dön', 'systemconf'); ?></a>
    <div class="sct-404__search"><?php echo do_shortcode('[systemconf_arama]'); ?></div>
</div>
<?php
get_footer();
