<?php
/**
 * Plugin Name: Systemconf
 * Plugin URI:  https://dentizmit.com
 * Description: Dent İzmit sitesine özel sistem bileşenleri (WhatsApp destek düğmesi ve diğer modüller).
 * Version:     1.5.0
 * Author:      Mango Medya
 * License:     GPL-2.0-or-later
 * Text Domain: systemconf
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SYSTEMCONF_VERSION', '1.5.0');
define('SYSTEMCONF_FILE', __FILE__);
define('SYSTEMCONF_DIR', plugin_dir_path(__FILE__));
define('SYSTEMCONF_URL', plugin_dir_url(__FILE__));

require_once SYSTEMCONF_DIR . 'src/Autoloader.php';

Systemconf\Autoloader::register(SYSTEMCONF_DIR . 'src');

add_action('plugins_loaded', static function (): void {
    Systemconf\Plugin::instance()->boot();
});
