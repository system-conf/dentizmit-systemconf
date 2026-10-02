<?php

declare(strict_types=1);

namespace Systemconf\Popup;

/**
 * Ön yüzdeki popup'ın HTML çıktısı: arka perde, kutu ve kapatma düğmesi.
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
        $title = (string) $this->config['title'];
        $image = (string) $this->config['image_url'];
        ?>
        <div class="scpu scpu--hidden" id="scpu" data-systemconf-popup hidden>
            <div class="scpu__box" role="dialog" aria-modal="true" aria-labelledby="scpu-title" tabindex="-1">
                <button type="button" class="scpu__close" data-scpu-close aria-label="<?php esc_attr_e('Kapat', 'systemconf'); ?>">&times;</button>
                <span id="scpu-title" class="scpu__title"><?php echo esc_html($title); ?></span>
                <div class="scpu__content">
                    <?php if ($image !== '') : ?>
                        <img class="scpu__image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
                    <?php endif; ?>
                    <?php echo wp_kses_post(wpautop((string) $this->config['content'])); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
