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
 * @package           Reactpress
 *
 * @wordpress-plugin
 * Plugin Name:       ReactPress
 * Plugin URI:        https://rockiger.com/en/reactpress
 * Description:       Easily create, build and deploy React apps into your existing WordPress sites.
 * Version:           3.2.1
 * Author:            Rockiger
 * Author URI:        https://rockiger.com/en/reactpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/rockiger/reactpress/
 * Text Domain:       reactpress
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use ReactPress\Includes\Activator;

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

use ReactPress\Includes\Core;
use ReactPress\Includes\Deactivator;


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('REPR_VERSION', '3.2.1');
define('REPR_IS_WINDOWS', PHP_OS_FAMILY === 'Windows');

define('REPR_PLUGIN_URL', REPR_IS_WINDOWS ? str_replace('\\', '/', plugin_dir_url(__FILE__)) : plugin_dir_url(__FILE__));
define('REPR_PLUGIN_PATH', REPR_IS_WINDOWS ? str_replace('\\', '/', plugin_dir_path(__FILE__)) : plugin_dir_path(__FILE__));
/** @phpstan-ignore-next-line */
define('REPR_APPS_PATH', REPR_IS_WINDOWS ? str_replace('\\', '/', WP_CONTENT_DIR . '/reactpress/apps') : WP_CONTENT_DIR . '/reactpress/apps');
define('REPR_APPS_URL', content_url() . '/reactpress/apps');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reactpress-activator.php
 */
function activate_reactpress() {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reactpress-deactivator.php
 */
function deactivate_reactpress() {
	Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_reactpress');
register_deactivation_hook(__FILE__, 'deactivate_reactpress');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_reactpress() {

	$plugin = new Core();
	$plugin->run();
}
run_reactpress();


/**
 * @param ...$data
 *
 * @return void
 */
function repr_debug($data) {
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
function repr_log($log) {
	if (true === WP_DEBUG) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
	return $log;
}
