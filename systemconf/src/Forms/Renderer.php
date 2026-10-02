<?php

declare(strict_types=1);

namespace Systemconf\Forms;

/**
 * Form tanımından HTML üretir. Elementor'daki görünüme (yuvarlak açık mavi
 * alanlar, koyu kırmızı düğme) assets/forms/form.css ile yaklaşılır.
 */
final class Renderer
{
    /** @param array<string, mixed> $definition */
    public function render(string $formId, array $definition): string
    {
        $showLabels = !empty($definition['show_labels']);
        $html = sprintf(
            '<form class="scf scf--%1$s" data-systemconf-form="%1$s" method="post" enctype="multipart/form-data" novalidate>',
            esc_attr($formId)
        );

        $html .= '<div class="scf__fields">';
        foreach ($definition['fields'] as $field) {
            $html .= $this->field($formId, $field, $showLabels);
        }
        $html .= '</div>';

        $html .= '<div class="scf__trap" aria-hidden="true"><label>Web sitesi<input type="text" name="sc_website" tabindex="-1" autocomplete="off"></label></div>';
        $html .= sprintf('<input type="hidden" name="sc_ts" value="%d">', time());
        $html .= '<input type="hidden" name="sc_token" value="">';

        $html .= sprintf(
            '<div class="scf__actions"><button type="submit" class="scf__submit">%s</button></div>',
            esc_html((string) ($definition['button'] ?? 'Gönder'))
        );
        $html .= '<div class="scf__message" role="status" aria-live="polite"></div>';
        $html .= '</form>';

        return $html;
    }

    /** @param array<string, mixed> $field */
    private function field(string $formId, array $field, bool $showLabels): string
    {
        $id = (string) $field['id'];
        $type = (string) $field['type'];
        $domId = 'scf-' . $formId . '-' . $id;
        $label = (string) $field['label'];
        $required = !empty($field['required']);
        $width = (int) ($field['width'] ?? 100) === 50 ? 'scf__field--half' : 'scf__field--full';
        $placeholder = (string) ($field['placeholder'] ?? ($showLabels ? '' : $label));
        $req = $required ? ' required aria-required="true"' : '';

        $open = sprintf('<div class="scf__field %s scf__field--%s" data-field="%s">', $width, esc_attr($type), esc_attr($id));
        $labelHtml = $showLabels && $type !== 'checkbox'
            ? sprintf('<label class="scf__label" for="%s">%s</label>', esc_attr($domId), esc_html($label))
            : '';
        $error = '<span class="scf__error" data-error></span></div>';

        switch ($type) {
            case 'textarea':
                $control = sprintf(
                    '<textarea id="%s" name="%s" rows="%d" placeholder="%s"%s></textarea>',
                    esc_attr($domId), esc_attr($id), (int) ($field['rows'] ?? 4), esc_attr($placeholder), $req
                );
                break;
            case 'select':
                $control = sprintf('<select id="%s" name="%s"%s>', esc_attr($domId), esc_attr($id), $req);
                $control .= sprintf('<option value="">%s</option>', esc_html($showLabels ? 'Seçiniz' : $label));
                foreach ($field['options'] ?? [] as $option) {
                    $control .= sprintf('<option value="%1$s">%1$s</option>', esc_html((string) $option));
                }
                $control .= '</select>';
                break;
            case 'checkbox':
                $control = sprintf(
                    '<label class="scf__check"><input type="checkbox" id="%s" name="%s" value="1"%s> <span>%s</span></label>',
                    esc_attr($domId), esc_attr($id), $req, esc_html($label)
                );
                break;
            case 'file':
                $control = sprintf(
                    '<input type="file" id="%s" name="%s" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"%s>',
                    esc_attr($domId), esc_attr($id), $req
                );
                break;
            default:
                $control = sprintf(
                    '<input type="%s" id="%s" name="%s" placeholder="%s" autocomplete="%s"%s>',
                    esc_attr($this->inputType($type)), esc_attr($domId), esc_attr($id),
                    esc_attr($placeholder), esc_attr($this->autocomplete($id, $type)), $req
                );
        }

        return $open . $labelHtml . $control . $error;
    }

    private function inputType(string $type): string
    {
        return in_array($type, ['text', 'tel', 'email', 'date', 'time'], true) ? $type : 'text';
    }

    private function autocomplete(string $id, string $type): string
    {
        if ($type === 'email') {
            return 'email';
        }
        if ($type === 'tel') {
            return 'tel';
        }
        if ($id === 'ad_soyad') {
            return 'name';
        }

        return 'off';
    }
}
