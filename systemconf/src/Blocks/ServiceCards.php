<?php

declare(strict_types=1);

namespace Systemconf\Blocks;

/**
 * Hizmet kartları: önde görsel + başlık, üzerine gelince dönen kırmızı arka yüz.
 * Kısa kod: [systemconf_hizmetler]
 */
final class ServiceCards
{
    /**
     * $index verilirse (1'den başlar) yalnızca o kart basılır; Elementor sütunları
     * içinde tek tek yerleştirmek için. Verilmezse 6 kartlık ızgara basılır.
     */
    public function render(int $index = 0): string
    {
        $services = Services::all();

        if ($index > 0) {
            $service = $services[$index - 1] ?? null;

            return $service === null ? '' : '<div class="scb-services scb-services--single">' . $this->card($service) . '</div>';
        }

        $html = '<div class="scb-services">';

        foreach ($services as $service) {
            $html .= $this->card($service);
        }

        return $html . '</div>';
    }

    /** @param array<string, string> $s */
    private function card(array $s): string
    {
        $title = nl2br(esc_html($s['title']));

        return sprintf(
            '<a class="scb-flip" href="%1$s" aria-label="%2$s">'
            . '<div class="scb-flip__inner">'
            . '<div class="scb-flip__front" style="background-image:url(%3$s)"><div class="scb-flip__shade"></div><h3 class="scb-flip__title">%4$s</h3></div>'
            . '<div class="scb-flip__back"><h3 class="scb-flip__title">%4$s</h3><p class="scb-flip__text">%5$s</p><span class="scb-flip__button">Devamını Oku</span></div>'
            . '</div></a>',
            esc_url($s['url']),
            esc_attr(str_replace("\n", ' ', $s['title'])),
            esc_url($s['image']),
            $title,
            esc_html(wp_trim_words($s['description'], 28, '…'))
        );
    }
}
