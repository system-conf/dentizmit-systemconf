<?php

declare(strict_types=1);

namespace Systemconf\Updater;

use Systemconf\ModuleInterface;

/**
 * GitHub sürümlerinden güncelleme: WordPress'in standart güncelleme
 * mekanizmasına bağlanır; yeni sürüm varsa Eklentiler ekranında "güncelle" çıkar.
 */
final class Module implements ModuleInterface
{
    private const REPO = 'system-conf/dentizmit-systemconf';
    private const ASSET = 'systemconf.zip';
    private const CACHE_KEY = 'systemconf_github_release';
    private const CACHE_TTL = HOUR_IN_SECONDS;

    public function slug(): string
    {
        return 'updater';
    }

    public function title(): string
    {
        return 'Güncelleme';
    }

    public function register(): void
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'injectUpdate']);
        add_filter('plugins_api', [$this, 'pluginInfo'], 10, 3);
        add_action('upgrader_process_complete', [$this, 'clearCache'], 10, 0);
    }

    /**
     * @param mixed $transient
     * @return mixed
     */
    public function injectUpdate($transient)
    {
        if (!is_object($transient)) {
            return $transient;
        }

        $release = $this->latestRelease();
        if ($release === null || !isset($release['version'], $release['package'])) {
            return $transient;
        }

        $basename = plugin_basename(SYSTEMCONF_FILE);

        if (version_compare((string) $release['version'], SYSTEMCONF_VERSION, '>')) {
            $transient->response[$basename] = (object) [
                'slug'        => 'systemconf',
                'plugin'      => $basename,
                'new_version' => $release['version'],
                'url'         => $release['html_url'],
                'package'     => $release['package'],
                'tested'      => get_bloginfo('version'),
                'requires_php' => '7.4',
            ];
        } else {
            unset($transient->response[$basename]);
            $transient->no_update[$basename] = (object) [
                'slug'        => 'systemconf',
                'plugin'      => $basename,
                'new_version' => SYSTEMCONF_VERSION,
                'url'         => $release['html_url'],
                'package'     => '',
            ];
        }

        return $transient;
    }

    /**
     * "Ayrıntıları görüntüle" penceresi.
     *
     * @param mixed  $result
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function pluginInfo($result, $action, $args)
    {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== 'systemconf') {
            return $result;
        }

        $release = $this->latestRelease();
        if ($release === null) {
            return $result;
        }

        return (object) [
            'name'          => 'Systemconf',
            'slug'          => 'systemconf',
            'version'       => $release['version'],
            'author'        => 'Mango Medya',
            'homepage'      => $release['html_url'],
            'download_link' => $release['package'],
            'requires_php'  => '7.4',
            'sections'      => ['description' => 'Dent İzmit sitesine özel sistem bileşenleri.', 'changelog' => $release['notes']],
        ];
    }

    public function clearCache(): void
    {
        delete_transient(self::CACHE_KEY);
    }

    /** @return array{version: string, package: string, html_url: string, notes: string}|null */
    private function latestRelease(): ?array
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            // Boş dizi "son denemede sürüm bulunamadı" demektir; tekrar sormadan geç.
            return isset($cached['version'], $cached['package']) ? $cached : null;
        }

        $response = wp_remote_get('https://api.github.com/repos/' . self::REPO . '/releases/latest', [
            'timeout' => 10,
            'headers' => ['Accept' => 'application/vnd.github+json', 'User-Agent' => 'systemconf-updater'],
        ]);

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            error_log('[systemconf] GitHub sürüm bilgisi alınamadı.');
            set_transient(self::CACHE_KEY, [], 15 * MINUTE_IN_SECONDS);
            return null;
        }

        $data = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($data) || empty($data['tag_name'])) {
            set_transient(self::CACHE_KEY, [], 15 * MINUTE_IN_SECONDS);
            return null;
        }

        $package = '';
        foreach ($data['assets'] ?? [] as $asset) {
            if (is_array($asset) && ($asset['name'] ?? '') === self::ASSET) {
                $package = (string) ($asset['browser_download_url'] ?? '');
            }
        }

        if ($package === '') {
            set_transient(self::CACHE_KEY, [], 15 * MINUTE_IN_SECONDS);
            return null;
        }

        $release = [
            'version'  => ltrim((string) $data['tag_name'], 'v'),
            'package'  => $package,
            'html_url' => (string) ($data['html_url'] ?? ''),
            'notes'    => wp_kses_post((string) ($data['body'] ?? '')),
        ];

        set_transient(self::CACHE_KEY, $release, self::CACHE_TTL);

        return $release;
    }

    public function renderSettings(): void
    {
        $release = $this->latestRelease();
        echo '<p>' . esc_html__('Kurulu sürüm:', 'systemconf') . ' <strong>' . esc_html(SYSTEMCONF_VERSION) . '</strong></p>';

        if ($release === null) {
            echo '<p>' . esc_html__('GitHub sürüm bilgisi alınamadı.', 'systemconf') . '</p>';
            return;
        }

        printf(
            '<p>%s <strong>%s</strong> — <a href="%s" target="_blank" rel="noopener">GitHub</a></p>',
            esc_html__('GitHub\'daki son sürüm:', 'systemconf'),
            esc_html($release['version']),
            esc_url($release['html_url'])
        );
        printf(
            '<p><a class="button" href="%s">%s</a></p>',
            esc_url(admin_url('plugins.php')),
            esc_html__('Eklentiler ekranına git', 'systemconf')
        );
    }
}
