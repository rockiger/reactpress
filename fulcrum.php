<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rockiger.com
 * @since             1.0.0
 * @package           Fulcrum
 *
 * @wordpress-plugin
 * Plugin Name:       Fulcrum
 * Description:       Capture knowledge. Find information faster. Share your ideas with others. Save projects, meeting notes and marketing plans right in your WordPress installation.
 * Version:           1.0.0
 * Author:            Rockiger
 * Author URI:        https://rockiger.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fulcrum
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use Fulcrum\Includes\Activator;

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

use Fulcrum\Includes\Core;
use Fulcrum\Includes\Deactivator;


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('FULC_VERSION', '1.0.0');
define('FULC_IS_WINDOWS', PHP_OS_FAMILY === 'Windows');

define('FULC_PLUGIN_URL', FULC_IS_WINDOWS ? str_replace('\\', '/', plugin_dir_url(__FILE__)) : plugin_dir_url(__FILE__));
define('FULC_PLUGIN_PATH', FULC_IS_WINDOWS ? str_replace('\\', '/', plugin_dir_path(__FILE__)) : plugin_dir_path(__FILE__));
/** @phpstan-ignore-next-line */
define('FULC_APPS_PATH', FULC_IS_WINDOWS ? str_replace('\\', '/', plugin_dir_path(__FILE__) . 'apps') : plugin_dir_path(__FILE__) . 'apps');
define('FULC_APPS_URL', plugin_dir_url(__FILE__) . 'apps');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reactpress-activator.php
 */
function fulc_activate() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reactpress-deactivator.php
 */
function fulc_deactivate() {
	Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'fulc_activate');
register_deactivation_hook(__FILE__, 'fulc_deactivate');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function fulc_run() {

	$plugin = new Core();
	$plugin->run();
}
fulc_run();

/**
 * @param ...$data
 *
 * @return void
 */
function fulc_debug($data) {
	if (WP_DEBUG !== true) return;

	$json = json_encode($data);
	add_action('shutdown', function () use ($json) {
		echo "<script>console.log({$json})</script>";
	});
}


/**
 * Write error to a log file named debug.log in wp-content.
 * 
 * @param mixed $log The thing you want to log.
 * @since 1.0.0
 */
function fulc_log($log) {
	if (WP_DEBUG !== true) return;

	if (is_array($log) || is_object($log)) {
		error_log(print_r($log, true));
	} else {
		error_log($log);
	}
}
