<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Şube kartları: görsel, harita, ad, adres, telefon, saatler, düğmeler.
 * Kısa kod: [systemconf_subeler]
 */
final class BranchCards
{
    public function render(): string
    {
        $html = '<div class="scb-branches">';

        foreach (Branches::all() as $branch) {
            $html .= $this->card($branch);
        }

        return $html . '</div>';
    }

    /** @param array<string, string> $b */
    private function card(array $b): string
    {
        $phone = trim($b['phone']);
        $call  = $phone === ''
            ? ''
            : sprintf('<a class="scb-branch__btn scb-branch__btn--ghost" href="%s">Ara</a>', esc_url(Branches::telHref($phone)));
        $phoneRow = $phone === ''
            ? ''
            : sprintf('<p class="scb-branch__row"><a href="%s">%s</a></p>', esc_url(Branches::telHref($phone)), esc_html($phone));

        return sprintf(
            '<article class="scb-branch">'
            . '<img class="scb-branch__img" src="%1$s" alt="%2$s" loading="lazy">'
            . '<iframe class="scb-branch__map" src="%3$s" title="%2$s harita" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>'
            . '<div class="scb-branch__body">'
            . '<h3 class="scb-branch__name">%2$s</h3>'
            . '<p class="scb-branch__row">%4$s</p>'
            . '%5$s'
            . '<p class="scb-branch__row">%6$s</p>'
            . '<div class="scb-branch__actions"><a class="scb-branch__btn" href="%7$s" target="_blank" rel="noopener">Yol Tarifi Al</a>%8$s</div>'
            . '</div></article>',
            esc_url($b['image']),
            esc_attr($b['name']),
            esc_url(Branches::mapEmbedUrl($b['address'])),
            esc_html($b['address']),
            $phoneRow,
            esc_html($b['hours']),
            esc_url(Branches::directionsUrl($b['address'])),
            $call
        );
    }
}
