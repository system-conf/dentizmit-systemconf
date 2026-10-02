<?php
/**
 * Yazı arşivi (blog listesi, kategori, etiket, arama sonuçları).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (is_search()) {
    $title = sprintf('"%s" için sonuçlar', get_search_query());
} elseif (is_home()) {
    $title = 'Blog';
} else {
    $title = wp_strip_all_tags((string) get_the_archive_title());
}

$subtitle = '';
$crumbs = [
    ['label' => 'Anasayfa', 'url' => home_url('/')],
    ['label' => $title, 'url' => ''],
];
include SYSTEMCONF_DIR . 'templates/parts/hero.php';
?>
<div class="sct-container sct-layout">
    <main class="sct-main">
        <?php if (have_posts()) : ?>
            <div class="scb-posts scb-posts--cards">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="scb-card">
                        <a class="scb-card__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large', ['class' => 'scb-card__img', 'loading' => 'lazy']); ?></a>
                        <div class="scb-card__body">
                            <h4 class="scb-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <p class="scb-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30, '…')); ?></p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <div class="sct-pagination">
                <?php the_posts_pagination(['prev_text' => '« Önceki', 'next_text' => 'Sonraki »']); ?>
            </div>
        <?php else : ?>
            <p class="sct-empty"><?php esc_html_e('Aradığınız ölçütlere uygun yazı bulunamadı.', 'systemconf'); ?></p>
        <?php endif; ?>
    </main>
    <?php include SYSTEMCONF_DIR . 'templates/parts/sidebar.php'; ?>
</div>
<?php
get_footer();
