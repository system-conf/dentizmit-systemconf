<?php

declare(strict_types=1);

namespace Systemconf\Tools;

/**
 * Elementor öğe ağacında id ile eşleşen bileşeni yerinde değiştirir.
 */
final class WidgetReplacer
{
    /**
     * @param array<int, array<string, mixed>> $elements
     * @param array<string, mixed>             $settings
     * @return array<int, array<string, mixed>>
     */
    public function replace(array $elements, string $widgetId, string $widgetType, array $settings, int &$count): array
    {
        foreach ($elements as $index => $element) {
            if (!is_array($element)) {
                continue;
            }

            if (($element['id'] ?? '') === $widgetId && ($element['elType'] ?? '') === 'widget') {
                $elements[$index] = [
                    'id'         => $widgetId,
                    'elType'     => 'widget',
                    'widgetType' => $widgetType,
                    'settings'   => $settings,
                    'elements'   => [],
                ];
                $count++;
                continue;
            }

            if (!empty($element['elements']) && is_array($element['elements'])) {
                $elements[$index]['elements'] = $this->replace($element['elements'], $widgetId, $widgetType, $settings, $count);
            }
        }

        return $elements;
    }
}
