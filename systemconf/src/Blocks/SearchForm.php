<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Site içi arama. İki görünüm:
 *  - varsayılan: satır içi kutu + kırmızı düğme
 *  - ikon: üst menüdeki büyüteç; tıklanınca tam ekran arama katmanı açılır
 * Kısa kod: [systemconf_arama stil="ikon"]
 */
final class SearchForm
{
    public function render(string $style = ''): string
    {
        return $style === 'ikon' ? $this->iconOverlay() : $this->inline();
    }

    private function inline(): string
    {
        return sprintf(
            '<form class="scb-search" role="search" method="get" action="%s">'
            . '<label class="screen-reader-text" for="scb-search-input">%s</label>'
            . '<input id="scb-search-input" class="scb-search__input" type="search" name="s" placeholder="%s" value="%s">'
            . '<button class="scb-search__button" type="submit" aria-label="%s">%s</button></form>',
            esc_url(home_url('/')),
            esc_html__('Ara', 'systemconf'),
            esc_attr__('Arama...', 'systemconf'),
            esc_attr((string) get_search_query()),
            esc_attr__('Ara', 'systemconf'),
            $this->icon(18)
        );
    }

    private function iconOverlay(): string
    {
        return sprintf(
            '<div class="scb-searchtoggle" data-scb-search>'
            . '<button type="button" class="scb-searchtoggle__button" data-scb-search-open aria-label="%1$s" aria-expanded="false">%2$s</button>'
            . '<div class="scb-overlay" data-scb-search-overlay hidden>'
            . '<button type="button" class="scb-overlay__close" data-scb-search-close aria-label="%3$s">&times;</button>'
            . '<form class="scb-overlay__form" role="search" method="get" action="%4$s">'
            . '<input class="scb-overlay__input" type="search" name="s" placeholder="%5$s" autocomplete="off">'
            . '<button class="scb-overlay__submit" type="submit" aria-label="%1$s">%6$s</button>'
            . '</form></div></div>',
            esc_attr__('Ara', 'systemconf'),
            $this->icon(20),
            esc_attr__('Kapat', 'systemconf'),
            esc_url(home_url('/')),
            esc_attr__('Ne aramıştınız?', 'systemconf'),
            $this->icon(26)
        );
    }

    private function icon(int $size): string
    {
        return sprintf(
            '<svg viewBox="0 0 24 24" width="%1$d" height="%1$d" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>',
            $size
        );
    }
}
