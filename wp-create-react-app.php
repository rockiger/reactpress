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
 * @package           Create_React_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       WP Create React App
 * Plugin URI:        https://wp-create-react-app.dev
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Marco Laspe
 * Author URI:        https://rockiger.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-create-react-app
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CRWP_VERSION', '1.0.0');

define('CRWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRWP_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-create-react-app-activator.php
 */
function activate_create_react_wp() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-create-react-app-activator.php';
	Create_React_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-create-react-app-deactivator.php
 */
function deactivate_create_react_wp() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-create-react-app-deactivator.php';
	Create_React_Wp_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_create_react_wp');
register_deactivation_hook(__FILE__, 'deactivate_create_react_wp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wp-create-react-app.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_create_react_wp() {

	$plugin = new Create_React_Wp();
	$plugin->run();
}
run_create_react_wp();
