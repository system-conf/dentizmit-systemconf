<?php

declare(strict_types=1);

namespace Systemconf\Tools;

/**
 * Elementor Pro'nun dinamik etiketlerini (yazı başlığı, site adresi vb.)
 * kalıcı değerlere çevirir. Pro olmadan bu etiketler yer tutucu metne düşer.
 */
final class DynamicResolver
{
    private int $postId;
    private int $resolved = 0;

    /** @var array<int, string> çözülemeyen etiket adları */
    private array $unknown = [];

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    /**
     * @param array<int, array<string, mixed>> $elements
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $elements): array
    {
        foreach ($elements as $i => $element) {
            if (!is_array($element)) {
                continue;
            }

            if (isset($element['settings']) && is_array($element['settings'])) {
                $elements[$i]['settings'] = $this->resolveSettings($element['settings']);
            }

            if (!empty($element['elements']) && is_array($element['elements'])) {
                $elements[$i]['elements'] = $this->resolve($element['elements']);
            }
        }

        return $elements;
    }

    public function resolvedCount(): int
    {
        return $this->resolved;
    }

    /** @return array<int, string> */
    public function unknownTags(): array
    {
        return array_values(array_unique($this->unknown));
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function resolveSettings(array $settings): array
    {
        if (isset($settings['__dynamic__']) && is_array($settings['__dynamic__'])) {
            foreach ($settings['__dynamic__'] as $key => $tag) {
                $value = $this->valueFor((string) $tag, $settings[$key] ?? null);

                if ($value === null) {
                    continue;
                }

                $settings[$key] = $value;
                unset($settings['__dynamic__'][$key]);
                $this->resolved++;
            }

            if ($settings['__dynamic__'] === []) {
                unset($settings['__dynamic__']);
            }
        }

        // Tekrarlayıcı (repeater) öğeleri: simge listesi satırları gibi.
        foreach ($settings as $key => $value) {
            if (!is_array($value) || $key === '__dynamic__') {
                continue;
            }
            foreach ($value as $i => $item) {
                if (is_array($item) && isset($item['_id'])) {
                    $settings[$key][$i] = $this->resolveSettings($item);
                }
            }
        }

        return $settings;
    }

    /**
     * @param mixed $current
     * @return mixed|null null ise etiket tanınmadı, dokunulmaz
     */
    private function valueFor(string $tag, $current)
    {
        if (!preg_match('/name="([^"]+)"/', $tag, $m)) {
            return null;
        }
        $name = $m[1];

        switch ($name) {
            case 'post-title':
            case 'page-title':
                return get_the_title($this->postId);
            case 'post-url':
                return $this->asLink($current, (string) get_permalink($this->postId));
            case 'site-url':
                return $this->asLink($current, home_url('/'));
            case 'site-title':
                return get_bloginfo('name');
            case 'site-tagline':
                return get_bloginfo('description');
            case 'post-date':
                return (string) get_the_date('', $this->postId);
            case 'post-excerpt':
                return (string) get_the_excerpt($this->postId);
            case 'post-featured-image':
                $id = (int) get_post_thumbnail_id($this->postId);
                $url = $id > 0 ? (string) wp_get_attachment_url($id) : '';
                return ['id' => $id, 'url' => $url];
            default:
                $this->unknown[] = $name;
                return null;
        }
    }

    /**
     * Bağlantı ayarları {url, is_external, nofollow, ...} dizisi olarak tutulur.
     *
     * @param mixed $current
     * @return array<string, mixed>
     */
    private function asLink($current, string $url): array
    {
        $base = is_array($current) ? $current : [];
        $base['url'] = $url;

        return $base;
    }
}
