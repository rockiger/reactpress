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
 * Plugin URI:        https://rockiger.com
 * Description:       Easily create, build and deploy React apps into your existing WordPress sites.
 * Version:           1.3.2
 * Author:            Marco Laspe
 * Author URI:        https://rockiger.com
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

use ReactPress\Includes\Activator;
use ReactPress\Includes\Deactivator;


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('REPR_VERSION', '1.3.2');

define('REPR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REPR_PLUGIN_PATH', plugin_dir_path(__FILE__));

define('REPR_APPS_PATH', WP_CONTENT_DIR . '/reactpress/apps');
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
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-reactpress.php';

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

	$plugin = new Reactpress();
	$plugin->run();
}
run_reactpress();
