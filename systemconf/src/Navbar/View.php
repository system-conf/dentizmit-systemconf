<?php

declare(strict_types=1);

namespace Systemconf\Navbar;

/**
 * Üst menünün HTML çıktısı.
 *
 * Masaüstünde: sol tarafta iki satırı kaplayan logo, üst satırda sosyal
 * simgeler, koyu ana satırda menü + arama + randevu düğmesi.
 * Telefonda: logo + arama + hamburger; menü sağdan açılan panelde.
 */
final class View
{
    /** @var array<string, mixed> */
    private array $config;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function render(): void
    {
        $classes = 'scnb' . ($this->config['sticky'] ? ' scnb--sticky' : '');
        ?>
        <header class="<?php echo esc_attr($classes); ?>" id="scnb">
            <div class="scnb__inner">
                <?php $this->renderLogo(); ?>
                <div class="scnb__top"><?php $this->renderSocial(); ?></div>
                <div class="scnb__bar">
                    <nav class="scnb__nav" id="scnbnav" aria-label="<?php esc_attr_e('Ana menü', 'systemconf'); ?>">
                        <div class="scnb__panelhead">
                            <span class="scnb__paneltitle"><?php esc_html_e('Menü', 'systemconf'); ?></span>
                            <button type="button" class="scnb__close" aria-label="<?php esc_attr_e('Menüyü kapat', 'systemconf'); ?>">&times;</button>
                        </div>
                        <?php echo $this->menuHtml(); // wp_nav_menu çıktısı kendi içinde kaçışlıdır. ?>
                        <div class="scnb__panelextra">
                            <?php $this->renderCta('scnb__cta scnb__cta--panel'); ?>
                            <?php $this->renderSocial(); ?>
                        </div>
                    </nav>
                    <?php $this->renderSearch(); ?>
                    <?php $this->renderCta('scnb__cta scnb__cta--bar'); ?>
                    <button type="button" class="scnb__toggle" aria-controls="scnbnav" aria-expanded="false" aria-label="<?php esc_attr_e('Menüyü aç', 'systemconf'); ?>">
                        <span class="scnb__burger" aria-hidden="true"><span></span><span></span><span></span></span>
                    </button>
                </div>
            </div>
            <div class="scnb__backdrop" hidden></div>
        </header>
        <?php
    }

    private function renderLogo(): void
    {
        $url = (string) $this->config['logo_url'];
        $alt = (string) $this->config['logo_alt'];
        ?>
        <a class="scnb__logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <?php if ($url !== '') : ?>
                <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" width="300" height="101" decoding="async">
            <?php else : ?>
                <span class="scnb__logotext"><?php echo esc_html(get_bloginfo('name')); ?></span>
            <?php endif; ?>
        </a>
        <?php
    }

    private function renderCta(string $classes): void
    {
        $url = (string) $this->config['cta_url'];
        $long = (string) $this->config['cta_label'];

        if ($url === '' || $long === '') {
            return;
        }

        $short = (string) $this->config['cta_short'];
        if ($short === '') {
            $short = $long;
        }
        ?>
        <a class="<?php echo esc_attr($classes); ?>" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($long); ?>">
            <span class="scnb__ctalong" aria-hidden="true"><?php echo esc_html($long); ?></span>
            <span class="scnb__ctashort" aria-hidden="true"><?php echo esc_html($short); ?></span>
        </a>
        <?php
    }

    private function renderSearch(): void
    {
        if (!$this->config['show_search'] || !shortcode_exists('systemconf_arama')) {
            return;
        }

        echo '<div class="scnb__search">' . do_shortcode('[systemconf_arama stil="ikon"]') . '</div>';
    }

    private function renderSocial(): void
    {
        $links = [
            'facebook'  => ['Facebook', (string) $this->config['facebook_url']],
            'instagram' => ['Instagram', (string) $this->config['instagram_url']],
            'twitter'   => ['Twitter', (string) $this->config['twitter_url']],
        ];

        $items = '';
        foreach ($links as $key => $link) {
            if ($link[1] === '') {
                continue;
            }

            $items .= sprintf(
                '<a class="scnb__social" href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a>',
                esc_url($link[1]),
                esc_attr($link[0]),
                $this->icon($key)
            );
        }

        if ($items !== '') {
            echo '<div class="scnb__socials">' . $items . '</div>';
        }
    }

    private function menuHtml(): string
    {
        $args = [
            'container'   => false,
            'menu_class'  => 'scnb__menu',
            'depth'       => 2,
            'fallback_cb' => '__return_empty_string',
            'echo'        => false,
        ];

        $menuId = (int) $this->config['menu_id'];

        if ($menuId > 0 && wp_get_nav_menu_object($menuId)) {
            $args['menu'] = $menuId;
        } elseif (has_nav_menu(Config::LOCATION)) {
            $args['theme_location'] = Config::LOCATION;
        } else {
            return '<!-- systemconf: üst menü için menü bulunamadı (Systemconf > Üst Menü (yeni)) -->';
        }

        $html = wp_nav_menu($args);

        return is_string($html) ? $html : '';
    }

    private function icon(string $key): string
    {
        $open = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">';

        if ($key === 'instagram') {
            return $open
                . '<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/>'
                . '<circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/>'
                . '<circle cx="17.3" cy="6.7" r="1.3" fill="currentColor"/></svg>';
        }

        if ($key === 'twitter') {
            return $open . '<path fill="currentColor" d="M22.16 5.66a8.38 8.38 0 0 1-2.4.66A4.2 4.2 0 0 0 21.6 4c-.82.49-1.72.83-2.66 1.02a4.18 4.18 0 0 0-7.13 3.81 11.87 11.87 0 0 1-8.62-4.37 4.17 4.17 0 0 0-.57 2.1c0 1.45.74 2.73 1.86 3.48a4.17 4.17 0 0 1-1.89-.52v.05a4.19 4.19 0 0 0 3.35 4.1 4.21 4.21 0 0 1-1.89.07 4.19 4.19 0 0 0 3.91 2.91 8.39 8.39 0 0 1-6.19 1.73 11.83 11.83 0 0 0 6.41 1.88c7.69 0 11.9-6.37 11.9-11.9l-.01-.54a8.5 8.5 0 0 0 2.09-2.16z"/></svg>';
        }

        return $open . '<path fill="currentColor" d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14A28.1 28.1 0 0 0 14.64 2C11.93 2 10 3.66 10 6.7v2.8H7v4h3V22h4z"/></svg>';
    }
}
