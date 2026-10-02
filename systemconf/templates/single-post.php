<?php
/**
 * Tek blog yazısı şablonu.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $title = get_the_title();
    $subtitle = (string) get_the_date('d/m/Y');
    $crumbs = [
        ['label' => 'Anasayfa', 'url' => home_url('/')],
        ['label' => 'Blog', 'url' => home_url('/blog/')],
        ['label' => $title, 'url' => ''],
    ];
    include SYSTEMCONF_DIR . 'templates/parts/hero.php';
    ?>
    <div class="sct-container sct-layout">
        <main class="sct-main">
            <article <?php post_class('sct-post'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="sct-post__thumb"><?php the_post_thumbnail('large', ['loading' => 'eager']); ?></div>
                <?php endif; ?>
                <div class="sct-post__content">
                    <?php the_content(); ?>
                </div>
            </article>
        </main>
        <?php include SYSTEMCONF_DIR . 'templates/parts/sidebar.php'; ?>
    </div>
    <?php
endwhile;

get_footer();
