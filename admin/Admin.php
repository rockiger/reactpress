<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Fulcrum
 * @subpackage Fulcrum/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fulcrum
 * @subpackage Fulcrum/admin
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace Fulcrum\Admin;

use Fulcrum\Admin\Controller;
use Fulcrum\Includes\Activator;

require_once dirname(__FILE__) . '/class-tgm-plugin-activation.php';

class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$valid_pages = ['reactpress'];
		$page = sanitize_title($_REQUEST['page'] ?? "");

		if (in_array($page, $valid_pages)) {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/reactpress-admin.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$valid_pages = ['reactpress'];
		$page = sanitize_title($_REQUEST['page'] ?? "");

		if (in_array($page, $valid_pages)) {

			// We need to load jquery and enable wp_localize_script.
			// Please don't ask me why!
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/reactpress-admin.js', array('jquery'), $this->version, false);

			wp_localize_script($this->plugin_name, "rp", array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'api' => [
					'nonce' => wp_create_nonce('wp_rest'),
					'rest_url' => esc_url_raw(rest_url()),

				],
				'apps' => Utils::get_apps(),
				'appspath' => FULC_APPS_PATH,
			));

			// React app
			$plugin_app_dir_url = plugin_dir_url(__FILE__) . 'js/reactpress-admin/';
			$react_app_build = $plugin_app_dir_url . 'build/';

			// Get the asset-manifest.json ($asset_manifest_json) content from React frontend, which is 
			// created after CRA build. This way we don't need to use
			// file_get_contents, which doesn't work on some hosts.
			require_once('js/reactpress-admin/build/asset-manifest.php');

			// Convert json to php array.
			/** @phpstan-ignore-next-line */
			$files_data = json_decode($asset_manifest_json);
			if ($files_data === null)
				return;

			if (!property_exists($files_data, 'entrypoints'))
				return false;

			// Get assets links.
			$assets_files = $files_data->entrypoints;

			$js_files = array_filter(
				$assets_files,
				fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'js'
			);
			$css_files = array_filter(
				$assets_files,
				fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'css'
			);

			// Load css files.
			foreach ($css_files as $index => $css_file) {
				wp_enqueue_style('react-plugin-' . $index, $react_app_build . $css_file);
			}

			// Load js files.
			foreach ($js_files as $index => $js_file) {
				wp_enqueue_script('react-plugin-' . $index, $react_app_build . $js_file, array('jquery'), '1', true);
			}
		}
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Fulcrum',
			'Fulcrum',
			'manage_options',
			'reactpress',
			fn () =>  require_once('partials/reactpress-admin-display.php'),
			FULC_MENU_ICON
		);
	}

	/**
	 * Add own post state (label) for pages used by apps.
	 * (C) https://www.ibenic.com/post-states-labels/
	 * 
	 * @param string[]   $states Array of all registered states.
	 * @param \WP_Post $post   Post object that we can use.
	 */
	function add_post_state($states, $post) {
		if ('page' === get_post_type($post)) {
			$fulc_apps = Utils::get_apps();
			$pageIds = array_map(fn ($el) => $el['pageIds'], $fulc_apps);
			$valid_pages = array_merge(...$pageIds);
			if (in_array($post->ID, $valid_pages)) {
				$states['reactpress'] = __('Fulcrum', 'text-domain');
			}
		}
		return $states;
	}

	/**
	 * Add page template. Consues a list of templates and adds two templates.
	 * (C) https://www.pradipdebnath.com/2019/08/17/how-to-add-page-template-from-plugin-in-wordpress/
	 * 
	 * @param string[]  $templates  The list of page templates 
	 * @since 1.0.0
	 */
	public function fulc_add_page_template($templates) {
		// Use relative paths for the templates and then resolve them at runtime.
		$templates['templates/empty-react-page-template.php'] = __('Fulcrum Canvas', 'text-domain');
		$templates['templates/react-page-template.php'] = __('Fulcrum Full Width', 'text-domain');

		return $templates;
	}

	/**
	 * https://www.sitepoint.com/wordpress-plugin-updates-right-way/
	 */
	public function check_plugin_version() {
		if (FULC_VERSION !== get_option('fulc_version')) {
			Activator::activate();
		}
	}

	public function fulc_handle_admin_ajax_request() {
		/**
		 * Handles all request from the admin frontend.
		 * All request must be of type POST, because the WordPress action
		 * 'wp_ajax_fulc_admin_ajax_request' requires it.
		 * 
		 * @since 1.0.0
		 */
		$appname = strtolower(sanitize_file_name($_POST['appname'] ?? ''));
		$pageId = intval($_POST['pageId'] ?? '');
		$page_title = $_POST['page_title'] ?? '';
		$permalink = $_POST['permalink'] ?? '';
		$param = sanitize_file_name($_REQUEST['param'] ?? "");

		try {
			if (!empty($param)) {
				if ($param === "add_page" && $appname && $pageId && $page_title) {
					Controller::add_page($appname, $pageId, $page_title);
				} elseif ($param === "delete_page" && $appname && $pageId) {
					Controller::delete_page($appname, $pageId, $permalink);
				} elseif ($param === "toggle_react_routing" && $appname) {
					Controller::toggle_react_routing($appname);
				} elseif ($param === "update_index_html" && $appname && $permalink) {
					Controller::update_index_html($appname, $permalink);
				} elseif ($param === 'delete_react_app' && $appname) {
					Controller::delete_react_app($appname);
				} elseif ($param === "get_react_apps") {
					Controller::get_react_apps();
				} else {
					echo wp_json_encode([
						'status' => 0,
						'message' => "Request unknown.",
					]);
				}
			}
		} catch (\Exception $e) {
			echo (wp_json_encode([
				'status' => 0,
				'message' => $e->getMessage(),
				'code' => $e->getCode(), // User-defined Exception code
				'filename' => $e->getFile(), // Source filename
				'line' => $e->getLine(), // Source line
				'trace' => $e->getTraceAsString(), // Formated string of trace
			]));
		} finally {
			wp_die();
		}
	}



	/**
	 * Checks if the given string is already used as a pageslug of any
	 * post in the current WP site.
	 * @param string $pageslug 
	 * @return bool 
	 * @since 1.2.0
	 */
	public function does_pageslug_exist(string $pageslug) {
		global $wpdb;
		return !empty($wpdb->get_row(
			$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "posts WHERE post_name = %s", $pageslug)
		));
	}

	/**
	 * Change the slug of a page to the new one.
	 *
	 * @param string $oldSlug
	 * @param string $newSlug
	 * @since 1.0.0
	 */
	public function update_react_page_slug(string $oldSlug, string $newSlug) {
		global $wpdb;
		// check if post_name (which is the slug and should be unique) exist
		$get_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT `ID`, `post_content` FROM " . $wpdb->prefix . "posts WHERE post_name = %s",
				$oldSlug
			)
		);

		if (empty($get_data)) {
			return ['status' => 'false', 'message' => 'Couldn\'t find page.'];
		} else {
			$result = wp_update_post(
				array(
					'ID' => $get_data->ID,
					'post_content' => $get_data->post_content . "\n\n" . REPR_REACT_ROOT_TAG,
					'post_name' => $newSlug,
				)
			);
			if ($result) {
				return ['status' => 'true', 'message' => 'Page updated.'];
			} else {
				return ['status' => 'false', 'message' => "Couldn't update page"];
			}
		}
	}

	public function fulc_register_required_plugins() {
		/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
		$plugins = array(

			// This is an example of how to include a plugin from a GitHub repository in your theme.
			// This presumes that the plugin code is based in the root of the GitHub repository
			// and not in a subdirectory ('/src') of the repository.

			array(
				'name'      => 'WPGraphQL',
				'slug'      => 'wp-graphql',
				'required'  => true,
			),
			array(
				'name'      => 'WPGraphQL Tax Query',
				'slug'      => 'wp-graphql-tax-query',
				'source'    => 'https://github.com/wp-graphql/wp-graphql-tax-query/archive/refs/tags/v0.2.0.zip',
				'required' => 'true',
			),
			array(
				'name'      => 'LH Private Content Login',
				'slug'      => 'lh-private-content-login',
				'required'  => false,
			),

		);

		/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
		$config = array(
			'id'           => 'fulcrum',                 // Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',                      // Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'parent_slug'  => 'plugins.php',            // Parent menu slug.
			'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => true,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.

			/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'fulcrum' ),
			'menu_title'                      => __( 'Install Plugins', 'fulcrum' ),
			/* translators: %s: plugin name. * /
			'installing'                      => __( 'Installing Plugin: %s', 'fulcrum' ),
			/* translators: %s: plugin name. * /
			'updating'                        => __( 'Updating Plugin: %s', 'fulcrum' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'fulcrum' ),
			'notice_can_install_required'     => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'fulcrum'
			),
			'notice_can_install_recommended'  => _n_noop(
				/* translators: 1: plugin name(s). * /
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'fulcrum'
			),
			'notice_ask_to_update'            => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'fulcrum'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				/* translators: 1: plugin name(s). * /
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'fulcrum'
			),
			'notice_can_activate_required'    => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'fulcrum'
			),
			'notice_can_activate_recommended' => _n_noop(
				/* translators: 1: plugin name(s). * /
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'fulcrum'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'fulcrum'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'fulcrum'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'fulcrum'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'fulcrum' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'fulcrum' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'fulcrum' ),
			/* translators: 1: plugin name. * /
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'fulcrum' ),
			/* translators: 1: plugin name. * /
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'fulcrum' ),
			/* translators: 1: dashboard link. * /
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'fulcrum' ),
			'dismiss'                         => __( 'Dismiss this notice', 'fulcrum' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'fulcrum' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'fulcrum' ),

			'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
		),
		*/
		);

		tgmpa($plugins, $config);
	}
}

/**
 * Deletes directory recursively
 * (C) Paulund https://paulund.co.uk/php-delete-directory-and-files-in-directory
 * 
 * @param string $dirname
 * @return bool true if directory deleted
 * @since 1.0.0
 */
function fulc_delete_directory(string $dirname): bool {
	$dir_handle = '';
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	if (!$dir_handle)
		return false;
	while ($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (!is_dir($dirname . "/" . $file))
				unlink($dirname . "/" . $file);
			else
				fulc_delete_directory($dirname . '/' . $file);
		}
	}
	closedir($dir_handle);
	return rmdir($dirname);
}

/**
 * Write error to a log file named debug.log in wp-content.
 * 
 * @param mixed $log The thing you want to log.
 * @since 1.0.0
 */
function fulc_log($log) {
	if (true === WP_DEBUG) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
	return $log;
}


/**
 * The html block, the react apps hook to.
 */
define("FULC_REACT_ROOT_TAG", "<!-- wp:html -->\n<!-- Please don't change. Block is needed for React app. -->\n<noscript>You need to enable JavaScript to run this app.</noscript>\n<div id=\"root\"></div>\n<!-- /wp:html -->");

define("FULC_MENU_ICON",     "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgdmVyc2lvbj0iMS4xIgogICB3aWR0aD0iMjQiCiAgIGhlaWdodD0iMjQiCiAgIHZpZXdCb3g9IjAgMCAyNCAyNCIKICAgaWQ9InN2ZzQiCiAgIHNvZGlwb2RpOmRvY25hbWU9ImZ1bGNydW0uc3ZnIgogICBpbmtzY2FwZTp2ZXJzaW9uPSIxLjIuMiAoYjBhODQ4NjU0MSwgMjAyMi0xMi0wMSkiCiAgIHhtbG5zOmlua3NjYXBlPSJodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy9uYW1lc3BhY2VzL2lua3NjYXBlIgogICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iPgogIDxtZXRhZGF0YQogICAgIGlkPSJtZXRhZGF0YTEwIj4KICAgIDxyZGY6UkRGPgogICAgICA8Y2M6V29yawogICAgICAgICByZGY6YWJvdXQ9IiI+CiAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+CiAgICAgICAgPGRjOnR5cGUKICAgICAgICAgICByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPgogICAgICAgIDxkYzp0aXRsZSAvPgogICAgICA8L2NjOldvcms+CiAgICA8L3JkZjpSREY+CiAgPC9tZXRhZGF0YT4KICA8ZGVmcwogICAgIGlkPSJkZWZzOCI+CiAgICA8aW5rc2NhcGU6cGF0aC1lZmZlY3QKICAgICAgIGlzX3Zpc2libGU9InRydWUiCiAgICAgICBpZD0icGF0aC1lZmZlY3Q5MDAyIgogICAgICAgZWZmZWN0PSJzcGlybyIgLz4KICA8L2RlZnM+CiAgPHNvZGlwb2RpOm5hbWVkdmlldwogICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIKICAgICBib3JkZXJjb2xvcj0iIzY2NjY2NiIKICAgICBib3JkZXJvcGFjaXR5PSIxIgogICAgIG9iamVjdHRvbGVyYW5jZT0iMTAiCiAgICAgZ3JpZHRvbGVyYW5jZT0iMTAiCiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwIgogICAgIGlua3NjYXBlOnBhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxOTIwIgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjEwNTEiCiAgICAgaWQ9Im5hbWVkdmlldzYiCiAgICAgc2hvd2dyaWQ9ImZhbHNlIgogICAgIGlua3NjYXBlOnpvb209IjQuOTE2NjY2NyIKICAgICBpbmtzY2FwZTpjeD0iLTQuMTY5NDkxNSIKICAgICBpbmtzY2FwZTpjeT0iLTEzLjQyMzcyOSIKICAgICBpbmtzY2FwZTp3aW5kb3cteD0iMCIKICAgICBpbmtzY2FwZTp3aW5kb3cteT0iMCIKICAgICBpbmtzY2FwZTp3aW5kb3ctbWF4aW1pemVkPSIxIgogICAgIGlua3NjYXBlOmN1cnJlbnQtbGF5ZXI9InN2ZzQiCiAgICAgaW5rc2NhcGU6c2hvd3BhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6cGFnZWNoZWNrZXJib2FyZD0iMCIKICAgICBpbmtzY2FwZTpkZXNrY29sb3I9IiNkMWQxZDEiIC8+CiAgPGcKICAgICBpZD0iZzg2OSIKICAgICB0cmFuc2Zvcm09Im1hdHJpeCgxLjMzMzMzMzMsMCwwLDEuMzMzMzMzLDExMy41MDk2OCwtMTE3LjcxMzAzKSIKICAgICBzdHlsZT0ic3Ryb2tlLXdpZHRoOjEiPgogICAgPHBhdGgKICAgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiCiAgICAgICBpZD0icGF0aDc0NzEiCiAgICAgICBkPSJtIC04NS4xMzIyNjEsOTYuNjUzNzk5IDE3LjYyNzkwNywtOC4zMzU3OTQgLTUuNDQxODU5LDguNjY3ODk4IHoiCiAgICAgICBzdHlsZT0iZmlsbDojZWE0MzM1O2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpldmVub2RkO3N0cm9rZTpub25lO3N0cm9rZS13aWR0aDoxcHg7c3Ryb2tlLWxpbmVjYXA6YnV0dDtzdHJva2UtbGluZWpvaW46bWl0ZXI7c3Ryb2tlLW9wYWNpdHk6MSIgLz4KICAgIDxwYXRoCiAgICAgICBpbmtzY2FwZTpjb25uZWN0b3ItY3VydmF0dXJlPSIwIgogICAgICAgaWQ9InBhdGg3NDczIgogICAgICAgZD0ibSAtNzUuOTQyMjIzLDEwMS42MzUzNSA4LjgwOTk2Myw0LjY0OTQ1IC0wLjM3MjA5MSwtMTguMDAwMDA0IHoiCiAgICAgICBzdHlsZT0iZmlsbDojNDI4NWY0O2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpldmVub2RkO3N0cm9rZTpub25lO3N0cm9rZS13aWR0aDoxcHg7c3Ryb2tlLWxpbmVjYXA6YnV0dDtzdHJva2UtbGluZWpvaW46bWl0ZXI7c3Ryb2tlLW9wYWNpdHk6MSIgLz4KICAgIDxwYXRoCiAgICAgICBpbmtzY2FwZTpjb25uZWN0b3ItY3VydmF0dXJlPSIwIgogICAgICAgaWQ9InBhdGg3NDc1IgogICAgICAgZD0ibSAtNjcuMTMyMjYsMTA2LjI4NDggLTEwLjg2MjExNywtNS43MjU5NCAtMC44MTIzMDIsNS43MjU5NCB6IgogICAgICAgc3R5bGU9ImZpbGw6IzM0YTg1MztmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6ZXZlbm9kZDtzdHJva2U6bm9uZTtzdHJva2Utd2lkdGg6MXB4O3N0cm9rZS1saW5lY2FwOmJ1dHQ7c3Ryb2tlLWxpbmVqb2luOm1pdGVyO3N0cm9rZS1vcGFjaXR5OjEiIC8+CiAgICA8cGF0aAogICAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIKICAgICAgIGlkPSJwYXRoNzQ3NyIKICAgICAgIGQ9Im0gLTg1LjEzMjI2MSw5Ni42NTM3OTkgNy44NzQwOSwwLjIwODk5IC0xLjU0ODUwOCw5LjQyMjAxMSB6IgogICAgICAgc3R5bGU9ImZpbGw6I2ZiYmMwNTtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6ZXZlbm9kZDtzdHJva2U6bm9uZTtzdHJva2Utd2lkdGg6MXB4O3N0cm9rZS1saW5lY2FwOmJ1dHQ7c3Ryb2tlLWxpbmVqb2luOm1pdGVyO3N0cm9rZS1vcGFjaXR5OjEiIC8+CiAgPC9nPgo8L3N2Zz4K");

define('FULC_REACT_ICON_SVG', '<svg class="react" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><circle cx="12" cy="11.245" r="1.785" fill="#626262"/><path d="M7.002 14.794l-.395-.101c-2.934-.741-4.617-2.001-4.617-3.452c0-1.452 1.684-2.711 4.617-3.452l.395-.1l.111.391a19.507 19.507 0 0 0 1.136 2.983l.085.178l-.085.178c-.46.963-.841 1.961-1.136 2.985l-.111.39zm-.577-6.095c-2.229.628-3.598 1.586-3.598 2.542c0 .954 1.368 1.913 3.598 2.54c.273-.868.603-1.717.985-2.54a20.356 20.356 0 0 1-.985-2.542zm10.572 6.095l-.11-.392a19.628 19.628 0 0 0-1.137-2.984l-.085-.177l.085-.179c.46-.961.839-1.96 1.137-2.984l.11-.39l.395.1c2.935.741 4.617 2 4.617 3.453c0 1.452-1.683 2.711-4.617 3.452l-.395.101zm-.41-3.553c.4.866.733 1.718.987 2.54c2.23-.627 3.599-1.586 3.599-2.54c0-.956-1.368-1.913-3.599-2.542a20.683 20.683 0 0 1-.987 2.542z" fill="#626262"/><path d="M6.419 8.695l-.11-.39c-.826-2.908-.576-4.991.687-5.717c1.235-.715 3.222.13 5.303 2.265l.284.292l-.284.291a19.718 19.718 0 0 0-2.02 2.474l-.113.162l-.196.016a19.646 19.646 0 0 0-3.157.509l-.394.098zm1.582-5.529c-.224 0-.422.049-.589.145c-.828.477-.974 2.138-.404 4.38c.891-.197 1.79-.338 2.696-.417a21.058 21.058 0 0 1 1.713-2.123c-1.303-1.267-2.533-1.985-3.416-1.985zm7.997 16.984c-1.188 0-2.714-.896-4.298-2.522l-.283-.291l.283-.29a19.827 19.827 0 0 0 2.021-2.477l.112-.16l.194-.019a19.473 19.473 0 0 0 3.158-.507l.395-.1l.111.391c.822 2.906.573 4.992-.688 5.718a1.978 1.978 0 0 1-1.005.257zm-3.415-2.82c1.302 1.267 2.533 1.986 3.415 1.986c.225 0 .423-.05.589-.145c.829-.478.976-2.142.404-4.384c-.89.198-1.79.34-2.698.419a20.526 20.526 0 0 1-1.71 2.124z" fill="#626262"/><path d="M17.58 8.695l-.395-.099a19.477 19.477 0 0 0-3.158-.509l-.194-.017l-.112-.162A19.551 19.551 0 0 0 11.7 5.434l-.283-.291l.283-.29c2.08-2.134 4.066-2.979 5.303-2.265c1.262.727 1.513 2.81.688 5.717l-.111.39zm-3.287-1.421c.954.085 1.858.228 2.698.417c.571-2.242.425-3.903-.404-4.381c-.824-.477-2.375.253-4.004 1.841c.616.67 1.188 1.378 1.71 2.123zM8.001 20.15a1.983 1.983 0 0 1-1.005-.257c-1.263-.726-1.513-2.811-.688-5.718l.108-.391l.395.1c.964.243 2.026.414 3.158.507l.194.019l.113.16c.604.878 1.28 1.707 2.02 2.477l.284.29l-.284.291c-1.583 1.627-3.109 2.522-4.295 2.522zm-.993-5.362c-.57 2.242-.424 3.906.404 4.384c.825.47 2.371-.255 4.005-1.842a21.17 21.17 0 0 1-1.713-2.123a20.692 20.692 0 0 1-2.696-.419z" fill="#626262"/><path d="M12 15.313c-.687 0-1.392-.029-2.1-.088l-.196-.017l-.113-.162a25.697 25.697 0 0 1-1.126-1.769a26.028 26.028 0 0 1-.971-1.859l-.084-.177l.084-.179c.299-.632.622-1.252.971-1.858c.347-.596.726-1.192 1.126-1.77l.113-.16l.196-.018a25.148 25.148 0 0 1 4.198 0l.194.019l.113.16a25.136 25.136 0 0 1 2.1 3.628l.083.179l-.083.177a24.742 24.742 0 0 1-2.1 3.628l-.113.162l-.194.017c-.706.057-1.412.087-2.098.087zm-1.834-.904c1.235.093 2.433.093 3.667 0a24.469 24.469 0 0 0 1.832-3.168a23.916 23.916 0 0 0-1.832-3.168a23.877 23.877 0 0 0-3.667 0a23.743 23.743 0 0 0-1.832 3.168a24.82 24.82 0 0 0 1.832 3.168z" fill="#626262"/></svg>');

define('FULC_LOGO', '<svg class="logo"
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   viewBox="0 0 105.53001 105.53001"
   version="1.1"
   id="svg8"
   sodipodi:docname="logo.svg"
   width="105.53001"
   height="105.53001"
   inkscape:version="1.0.1 (0767f8302a, 2020-10-17)">
  <metadata
     id="metadata12">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <sodipodi:namedview
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1"
     objecttolerance="10"
     gridtolerance="10"
     guidetolerance="10"
     inkscape:pageopacity="0"
     inkscape:pageshadow="2"
     inkscape:window-width="1920"
     inkscape:window-height="1043"
     id="namedview10"
     showgrid="false"
     inkscape:zoom="3.0964286"
     inkscape:cx="-68.237939"
     inkscape:cy="51.487908"
     inkscape:window-x="3840"
     inkscape:window-y="0"
     inkscape:window-maximized="1"
     inkscape:current-layer="svg8"
     fit-margin-top="5"
     fit-margin-left="5"
     fit-margin-right="5"
     fit-margin-bottom="5" />
  <defs
     id="defs4">
    <style
       id="style2">.cls-1{fill:#09d3ac}</style>
  </defs>
  <g
     id="g1466"
     transform="translate(-29.241311,-22.687829)">
    <ellipse
       style="fill:none;fill-opacity:1;stroke:#0071a1;stroke-width:8.69066;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
       id="path869"
       cx="33.131741"
       cy="106.39763"
       rx="15.910225"
       ry="43.420872"
       transform="rotate(-30.086938)" />
    <ellipse
       style="fill:none;fill-opacity:1;stroke:#0071a1;stroke-width:8.69066;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
       id="ellipse895"
       cx="-108.78278"
       cy="24.175873"
       rx="15.910225"
       ry="43.420872"
       transform="matrix(-0.86526573,-0.50131349,-0.50131349,0.86526573,0,0)" />
    <ellipse
       style="fill:none;fill-opacity:1;stroke:#0071a1;stroke-width:8.69066;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
       id="ellipse897"
       cx="-76.105133"
       cy="-81.401466"
       rx="15.910225"
       ry="43.420872"
       transform="matrix(-0.00798162,-0.99996815,-0.99996815,0.00798162,0,0)" />
  </g>
  <circle
     style="fill:#ffffff;fill-opacity:1;stroke:none;stroke-width:15.1175;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
     id="path1461"
     cx="52.765007"
     cy="52.765003"
     r="20.002855" />
  <path
     d="m 66.574248,45.217251 c 1.224994,2.24175 1.923254,4.812499 1.923254,7.547751 0,5.801247 -3.144764,10.874498 -7.822504,13.599247 l 4.80551,-13.893247 c 0.89774,-2.243501 1.19699,-4.040751 1.19699,-5.633252 -8e-4,-0.59325 -0.0385,-1.139251 -0.1033,-1.620499 m -11.63925,0.15225 c 0.94324,-0.04375 1.7955,-0.154 1.7955,-0.154 0.84874,-0.11025 0.74899,-1.356249 -0.098,-1.310748 0,0 -2.56025,0.195998 -4.2,0.195998 -1.55225,0 -4.15625,-0.21875 -4.15625,-0.21875 -0.85225,-0.042 -0.9625,1.24775 -0.10675,1.291501 0,0 0.78575,0.09099 1.6415,0.131249 l 2.44825,6.7165 -3.45625,10.323249 -5.7295,-17.017 c 0.94675,-0.0455 1.799,-0.145246 1.799,-0.145246 0.85225,-0.11025 0.75249,-1.356251 -0.0963,-1.307253 0,0 -2.54624,0.20125 -4.19124,0.20125 -0.29226,0 -0.63875,-0.01219 -1.00625,-0.02275 2.84899,-4.194749 7.69474,-7.017498 13.18625,-7.017498 4.09675,0 7.82425,1.564499 10.62423,4.13 -0.0665,-0.0035 -0.133,-0.01401 -0.20475,-0.01401 -1.54524,0 -2.64249,1.347501 -2.64249,2.793002 0,1.296746 0.74725,2.395749 1.54525,3.688998 0.60025,1.051749 1.2985,2.3975 1.2985,4.341751 0,1.335248 -0.51624,2.908499 -1.19875,5.073248 l -1.568,5.227247 -5.6875,-16.931249 z m -2.17,23.120996 c -1.54526,0 -3.03451,-0.222252 -4.445,-0.636996 l 4.71975,-13.716501 4.83524,13.250999 c 0.0367,0.07701 0.0735,0.14875 0.11374,0.217 -1.6345,0.572253 -3.39149,0.889002 -5.22375,0.889002 M 37.032498,52.765002 c 0,-2.282001 0.48825,-4.448501 1.36151,-6.4015 l 7.50224,20.560746 c -5.24474,-2.551498 -8.86375,-7.930998 -8.86375,-14.159246 m 15.7325,-17.499999 c -9.64775,0 -17.49999,7.852248 -17.49999,17.499999 0,9.647749 7.85224,17.500001 17.49999,17.500001 9.64776,0 17.500004,-7.852252 17.500004,-17.500001 0,-9.647751 -7.852244,-17.499999 -17.500004,-17.499999"
     id="path837"
     style="fill:#0071a1;fill-opacity:1;stroke-width:1.75" />
</svg>');
