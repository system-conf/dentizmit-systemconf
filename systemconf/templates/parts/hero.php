<?php
/**
 * Koyu arka planlı başlık şeridi (yazı, arşiv ve 404 sayfalarının üstü).
 *
 * @var string $title
 * @var string $subtitle   (isteğe bağlı) tarih ya da açıklama
 * @var array<int, array{label: string, url: string}> $crumbs
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$heroImage = content_url('uploads/2023/02/implant.jpg');
?>
<section class="sct-hero" style="background-image:url('<?php echo esc_url($heroImage); ?>')">
    <div class="sct-hero__shade"></div>
    <div class="sct-container sct-hero__inner">
        <h1 class="sct-hero__title"><?php echo esc_html($title); ?></h1>
        <?php if (!empty($crumbs)) : ?>
            <nav class="sct-crumbs" aria-label="<?php esc_attr_e('Sayfa yolu', 'systemconf'); ?>">
                <?php foreach ($crumbs as $i => $crumb) : ?>
                    <?php if ($i > 0) : ?><span class="sct-crumbs__sep" aria-hidden="true">›</span><?php endif; ?>
                    <?php if ($crumb['url'] !== '') : ?>
                        <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['label']); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html($crumb['label']); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        <?php if (!empty($subtitle)) : ?>
            <p class="sct-hero__meta"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
    </div>
</section>
