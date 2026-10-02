<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

use WP_Query;

/**
 * Yazı listeleri. İki görünüm:
 *  - cards: ana sayfadaki iki sütunlu büyük kartlar
 *  - list : hizmet ve yazı sayfalarındaki "Önerilenler" küçük listesi (solda görsel)
 * Kısa kod: [systemconf_yazilar skin="cards|list" count="2" ids="1,2,3" exclude_current="1"]
 */
final class PostsList
{
    /** @param array<string, mixed> $atts */
    public function render(array $atts): string
    {
        $skin = $atts['skin'] === 'list' ? 'list' : 'cards';
        $query = new WP_Query($this->queryArgs($atts));

        if (!$query->have_posts()) {
            return '';
        }

        $html = sprintf('<div class="scb-posts scb-posts--%s">', esc_attr($skin));

        while ($query->have_posts()) {
            $query->the_post();
            $html .= $skin === 'list' ? $this->listItem() : $this->cardItem();
        }

        wp_reset_postdata();

        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $atts
     * @return array<string, mixed>
     */
    private function queryArgs(array $atts): array
    {
        $args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => max(1, min(24, (int) $atts['count'])),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ];

        $ids = array_filter(array_map('intval', explode(',', (string) $atts['ids'])));
        if ($ids !== []) {
            $args['post__in'] = array_values($ids);
            $args['orderby'] = 'post__in';
            $args['posts_per_page'] = count($ids);
        }

        if (!empty($atts['exclude_current']) && is_singular('post')) {
            $args['post__not_in'] = [get_the_ID()];
        }

        return $args;
    }

    private function cardItem(): string
    {
        $thumb = get_the_post_thumbnail(null, 'large', ['class' => 'scb-card__img', 'loading' => 'lazy']);

        return sprintf(
            '<article class="scb-card"><a class="scb-card__media" href="%1$s">%2$s</a>'
            . '<div class="scb-card__body"><h4 class="scb-card__title"><a href="%1$s">%3$s</a></h4><p class="scb-card__excerpt">%4$s</p></div></article>',
            esc_url((string) get_permalink()),
            $thumb,
            esc_html(get_the_title()),
            esc_html(wp_trim_words(get_the_excerpt(), 30, '…'))
        );
    }

    private function listItem(): string
    {
        $thumb = get_the_post_thumbnail(null, 'thumbnail', ['class' => 'scb-item__img', 'loading' => 'lazy']);

        return sprintf(
            '<article class="scb-item"><a class="scb-item__media" href="%1$s">%2$s</a>'
            . '<div class="scb-item__body"><h5 class="scb-item__title"><a href="%1$s">%3$s</a></h5>'
            . '<time class="scb-item__date" datetime="%4$s">%5$s</time></div></article>',
            esc_url((string) get_permalink()),
            $thumb,
            esc_html(get_the_title()),
            esc_attr((string) get_the_date('c')),
            esc_html((string) get_the_date('j F Y'))
        );
    }
}
