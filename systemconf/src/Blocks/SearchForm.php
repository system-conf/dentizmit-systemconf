<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Site içi arama kutusu. Kısa kod: [systemconf_arama]
 */
final class SearchForm
{
    public function render(): string
    {
        return sprintf(
            '<form class="scb-search" role="search" method="get" action="%s">'
            . '<label class="screen-reader-text" for="scb-search-input">%s</label>'
            . '<input id="scb-search-input" class="scb-search__input" type="search" name="s" placeholder="%s" value="%s">'
            . '<button class="scb-search__button" type="submit" aria-label="%s">'
            . '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>'
            . '</button></form>',
            esc_url(home_url('/')),
            esc_html__('Ara', 'systemconf'),
            esc_attr__('Arama...', 'systemconf'),
            esc_attr((string) get_search_query()),
            esc_attr__('Ara', 'systemconf')
        );
    }
}
