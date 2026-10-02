<?php

declare(strict_types=1);

namespace Systemconf\Whatsapp;

/**
 * Ön yüzdeki düğme ve sohbet kutusunun HTML çıktısı.
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
        $position = $this->config['position'] === 'right' ? 'right' : 'left';
        ?>
        <div class="scwa scwa--<?php echo esc_attr($position); ?> scwa--hidden" id="scwa" data-systemconf-whatsapp>
            <div class="scwa__box" id="scwa-box" role="dialog" aria-modal="false" aria-labelledby="scwa-title" hidden>
                <div class="scwa__header">
                    <span class="scwa__header-icon" aria-hidden="true"><?php echo $this->icon(); ?></span>
                    <div class="scwa__header-text">
                        <strong id="scwa-title"><?php echo esc_html((string) $this->config['header_title']); ?></strong>
                        <small><?php esc_html_e('Genellikle birkaç dakika içinde yanıt verir', 'systemconf'); ?></small>
                    </div>
                    <button type="button" class="scwa__close" data-scwa-close aria-label="<?php esc_attr_e('Kapat', 'systemconf'); ?>">&times;</button>
                </div>
                <div class="scwa__body">
                    <div class="scwa__bubble"><?php echo nl2br(esc_html((string) $this->config['welcome_text'])); ?></div>
                </div>
                <div class="scwa__footer">
                    <button type="button" class="scwa__cta" data-scwa-open>
                        <?php echo esc_html((string) $this->config['cta_label']); ?>
                    </button>
                </div>
            </div>

            <div class="scwa__launcher">
                <button type="button" class="scwa__button" data-scwa-toggle aria-expanded="false" aria-controls="scwa-box" aria-label="<?php echo esc_attr((string) $this->config['tooltip']); ?>">
                    <?php echo $this->icon(); ?>
                </button>
                <span class="scwa__tooltip" aria-hidden="true"><?php echo esc_html((string) $this->config['tooltip']); ?></span>
            </div>
        </div>
        <?php
    }

    private function icon(): string
    {
        return '<svg viewBox="0 0 32 32" width="32" height="32" fill="currentColor" aria-hidden="true">'
            . '<path d="M16 3C9.4 3 4 8.3 4 14.9c0 2.3.7 4.6 1.9 6.5L4 29l7.8-2c1.9 1 4.1 1.6 6.2 1.6 6.6 0 12-5.3 12-11.9S22.6 3 16 3zm0 21.7c-1.9 0-3.8-.5-5.4-1.5l-.4-.2-4.6 1.2 1.2-4.4-.3-.4c-1.1-1.7-1.7-3.6-1.7-5.6C4.8 9.4 9.8 4.8 16 4.8s11.2 4.6 11.2 10.1-5 9.8-11.2 9.8zm6.1-7.4c-.3-.2-2-1-2.3-1.1-.3-.1-.5-.2-.8.2-.2.3-.9 1.1-1.1 1.3-.2.2-.4.3-.7.1-.3-.2-1.4-.5-2.7-1.6-1-.9-1.7-2-1.9-2.3-.2-.3 0-.5.1-.7l.5-.6c.2-.2.2-.3.3-.6.1-.2.1-.4 0-.6-.1-.2-.8-1.8-1-2.5-.3-.6-.5-.5-.8-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.2 2.4 3.6 5.8 5 .8.3 1.4.5 1.9.7.8.3 1.5.2 2.1.1.6-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.6-.4z"/>'
            . '</svg>';
    }
}
