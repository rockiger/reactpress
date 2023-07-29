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
 * Plugin URI:        https://rockiger.com/en/reactpress
 * Description:       Capture knowledge. Find information faster. Share your ideas with others. Save projects, meeting notes and marketing plans right in your WordPress installation.
 * Version:           3.2.0
 * Author:            Rockiger
 * Author URI:        https://rockiger.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
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
define('FULC_VERSION', '0.1.0');
define('IS_WINDOWS', PHP_OS_FAMILY === 'Windows');

define('FULC_PLUGIN_URL', IS_WINDOWS ? str_replace('\\', '/', plugin_dir_url(__FILE__)) : plugin_dir_url(__FILE__));
define('FULC_PLUGIN_PATH', IS_WINDOWS ? str_replace('\\', '/', plugin_dir_path(__FILE__)) : plugin_dir_path(__FILE__));
/** @phpstan-ignore-next-line */
define('FULC_APPS_PATH', IS_WINDOWS ? str_replace('\\', '/', plugin_dir_path(__FILE__) . '/apps') : plugin_dir_path(__FILE__) . '/apps');
define('FULC_APPS_URL', plugin_dir_url(__FILE__) . '/apps');

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
