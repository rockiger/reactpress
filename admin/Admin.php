<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Reactpress
 * @subpackage Reactpress/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Reactpress
 * @subpackage Reactpress/admin
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace ReactPress\Admin;

use ReactPress\Admin\Controller;
use ReactPress\Includes\Activator;

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
				'apps' => Utils::get_apps(),
				'appspath' => REPR_APPS_PATH,
			));

			// React app
			$plugin_app_dir_url = plugin_dir_url(__FILE__) . 'js/reactpress-admin/';
			$react_app_build = $plugin_app_dir_url . 'build/';
			$manifest_url = $react_app_build . 'asset-manifest.json';

			// Request manifest file.
			$request = file_get_contents($manifest_url);

			// If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
			if (!$request)
				return false;

			// Convert json to php array.
			$files_data = json_decode($request);
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
				wp_enqueue_script('react-plugin-' . $index, $react_app_build . $js_file, array('jquery'), 1, true);
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
			'ReactPress',
			'ReactPress',
			'manage_options',
			'reactpress',
			fn () =>  require_once('partials/reactpress-admin-display.php'),
			REPR_MENU_ICON
		);
	}

	/**
	 * Add own post state (label) for pages used by apps.
	 * (C) https://www.ibenic.com/post-states-labels/
	 * 
	 * @param array   $states Array of all registered states.
	 * @param WP_Post $post   Post object that we can use.
	 */
	function add_post_state($states, $post) {
		if ('page' === get_post_type($post)) {
			$repr_apps = Utils::get_apps();
			$pageIds = array_map(fn ($el) => $el['pageIds'], $repr_apps);
			$valid_pages = array_merge(...$pageIds);
			if (in_array($post->ID, $valid_pages)) {
				$states['reactpress'] = __('ReactPress', 'text-domain');
			}
		}
		return $states;
	}

	/**
	 * Add page template.
	 * (C) https://www.pradipdebnath.com/2019/08/17/how-to-add-page-template-from-plugin-in-wordpress/
	 * 
	 * @param  array  $templates  The list of page templates
	 * @return array  $templates  The modified list of page templates* 
	 * @since 1.0.0
	 */
	public function repr_add_page_template($templates) {
		// Use relative paths for the templates and then resolve them at runtime.
		$templates['templates/empty-react-page-template.php'] = __('ReactPress Canvas', 'text-domain');
		$templates['templates/react-page-template.php'] = __('ReactPress Full Width', 'text-domain');

		return $templates;
	}

	/**
	 * https://www.sitepoint.com/wordpress-plugin-updates-right-way/
	 */
	public function check_plugin_version() {
		if (REPR_VERSION !== get_option('repr_version')) {
			Activator::activate();
		}
	}

	public function repr_handle_admin_ajax_request() {
		/**
		 * Handles all request from the admin frontend.
		 * All request must be of type POST, because the WordPress action
		 * 'wp_ajax_repr_admin_ajax_request' requires it.
		 * 
		 * @since 1.0.0
		 */
		$appname = strtolower(sanitize_file_name($_POST['appname'] ?? ''));
		$app_options_list = Utils::get_apps();
		$pageId = sanitize_title_for_query($_POST['pageId'] ?? '');
		$page_title = sanitize_title_for_query($_POST['page_title'] ?? '');
		$permalink = sanitize_title_for_query($_POST['permalink'] ?? '');
		$param = sanitize_file_name($_REQUEST['param'] ?? "");

		try {
			if (!empty($param)) {
				if ($param === "add_page" && $appname && $pageId && $page_title) {
					return Controller::add_page($appname, $pageId, $page_title);
				} elseif ($param === "delete_page" && $appname && $pageId) {
					Controller::delete_page($app_options_list, $appname, $pageId, $permalink);
				} elseif ($param === "toggle_react_routing" && $appname) {
					Controller::toggle_react_routing($appname);
				} elseif ($param === "update_index_html" && $appname && $pageId) {
					/* 					$this->write_index_html($appname, $this->get_index_html_content($permalink));
					echo wp_json_encode([
						'status' => 1,
						'message' => 'Index.html updated.',
					]); */
				} elseif ($param === 'delete_react_app' && $appname) {

					$options = get_option('repr_apps');
					$is_option_deleted = Utils::write_apps_option(array_filter(
						$options,
						fn ($el) => $el['appname'] !== $appname
					));
					$is_appdir_removed = repr_delete_directory(Utils::app_path($appname));
					if ($is_appdir_removed) {
						echo wp_json_encode([
							'status' => 1,
							'message' => 'App deleted.',
						]);
					} else {
						echo wp_json_encode([
							'status' => 1,
							'message' => "Couldn't remove files. Please remove directory by hand.",
						]);
					}
				} elseif ($param === "get_react_apps") {
					$apps = Utils::get_apps();
					echo wp_json_encode(['status' => 1, 'apps' => $apps]);
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
	 * Reads the React app uri out of the out.log file of the
	 * app. Which is a splash screen of create-react-app.
	 *
	 * @param string $apppath the directory path of the app
	 * @param integer $max_trys the number of trys to read the uri.
	 * Important when out.log file is still being created.
	 * @return array contains the protocol, the ip and the 
	 * port of the uri.
	 * @since 1.0.0
	 */
	function get_app_uri(string $apppath, int $max_trys = 1) {
		$regex = '/http:\/\/\d+\.\d+\.\d+\.\d+:\d*/';
		$matches = [];
		$trys = 0;
		while (empty($matches) && $trys < $max_trys) {
			try {
				$file_content = file_get_contents("{$apppath}/out.log");
			} catch (\Exception $e) {
				return [];
			}
			preg_match($regex, $file_content, $matches);
			$trys += 1;
			if ($trys < $max_trys) {
				sleep(1);
			}
		}
		if (empty($matches)) {
			return [];
		} else {
			return explode(':', $matches[0]);
		}
	}

	/**
	 * Change the slug of a page to the new one.
	 *
	 * @param string $oldSlug
	 * @param string $newSlug
	 * @return void
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
					'post_content' => $get_data->post_content . '\n' . REPR_REACT_ROOT_TAG,
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
}

/**
 * Deletes directory recursively
 * (C) Paulund https://paulund.co.uk/php-delete-directory-and-files-in-directory
 * 
 * @param string $dirname
 * @return bool true if directory deleted
 * @since 1.0.0
 */
function repr_delete_directory(string $dirname): bool {
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
				repr_delete_directory($dirname . '/' . $file);
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


/**
 * The html block, the react apps hook to.
 */
define("REPR_REACT_ROOT_TAG", "<!-- wp:html -->\n<!-- Please don't change. Block is needed for React app. -->\n<noscript>You need to enable JavaScript to run this app.</noscript>\n<div id=\"root\"></div>\n<!-- /wp:html -->");

define("REPR_MENU_ICON",     "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgd2lkdGg9IjI0IgogICBoZWlnaHQ9IjI0IgogICB2aWV3Qm94PSIwIDAgMjQgMjQiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2Zzg5NyIKICAgc29kaXBvZGk6ZG9jbmFtZT0iYnhsLXJlYWN0LnN2ZyIKICAgaW5rc2NhcGU6dmVyc2lvbj0iMS4wLjEgKDA3NjdmODMwMmEsIDIwMjAtMTAtMTcpIj4KICA8bWV0YWRhdGEKICAgICBpZD0ibWV0YWRhdGE5MDMiPgogICAgPHJkZjpSREY+CiAgICAgIDxjYzpXb3JrCiAgICAgICAgIHJkZjphYm91dD0iIj4KICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL3N2Zyt4bWw8L2RjOmZvcm1hdD4KICAgICAgICA8ZGM6dHlwZQogICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+CiAgICAgIDwvY2M6V29yaz4KICAgIDwvcmRmOlJERj4KICA8L21ldGFkYXRhPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM5MDEiIC8+CiAgPHNvZGlwb2RpOm5hbWVkdmlldwogICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIKICAgICBib3JkZXJjb2xvcj0iIzY2NjY2NiIKICAgICBib3JkZXJvcGFjaXR5PSIxIgogICAgIG9iamVjdHRvbGVyYW5jZT0iMTAiCiAgICAgZ3JpZHRvbGVyYW5jZT0iMTAiCiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwIgogICAgIGlua3NjYXBlOnBhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxOTIwIgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjEwMTUiCiAgICAgaWQ9Im5hbWVkdmlldzg5OSIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgaW5rc2NhcGU6em9vbT0iMzYuMTI1IgogICAgIGlua3NjYXBlOmN4PSIxMiIKICAgICBpbmtzY2FwZTpjeT0iMTIiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjE5MjAiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9IjI4IgogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiCiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0ic3ZnODk3IiAvPgogIDxjaXJjbGUKICAgICBjeD0iMTIiCiAgICAgY3k9IjExLjI0NSIKICAgICByPSIxLjc4NSIKICAgICBpZD0iY2lyY2xlODg3IgogICAgIHN0eWxlPSJmaWxsOiNhMGE1YWE7ZmlsbC1vcGFjaXR5OjEiIC8+CiAgPHBhdGgKICAgICBkPSJNNy4wMDIsMTQuNzk0bC0wLjM5NS0wLjEwMWMtMi45MzQtMC43NDEtNC42MTctMi4wMDEtNC42MTctMy40NTJjMC0xLjQ1MiwxLjY4NC0yLjcxMSw0LjYxNy0zLjQ1MmwwLjM5NS0wLjFMNy4xMTMsOC4wOCBjMC4yOTcsMS4wMjMsMC42NzYsMi4wMjIsMS4xMzYsMi45ODNsMC4wODUsMC4xNzhsLTAuMDg1LDAuMTc4Yy0wLjQ2LDAuOTYzLTAuODQxLDEuOTYxLTEuMTM2LDIuOTg1TDcuMDAyLDE0Ljc5NEw3LjAwMiwxNC43OTR6IE02LjQyNSw4LjY5OWMtMi4yMjksMC42MjgtMy41OTgsMS41ODYtMy41OTgsMi41NDJjMCwwLjk1NCwxLjM2OCwxLjkxMywzLjU5OCwyLjU0YzAuMjczLTAuODY4LDAuNjAzLTEuNzE3LDAuOTg1LTIuNTQgQzcuMDI1LDEwLjQxNiw2LjY5Niw5LjU2Nyw2LjQyNSw4LjY5OXogTTE2Ljk5NywxNC43OTRsLTAuMTEtMC4zOTJjLTAuMjk4LTEuMDI0LTAuNjc3LTIuMDIyLTEuMTM3LTIuOTg0bC0wLjA4NS0wLjE3NyBsMC4wODUtMC4xNzljMC40Ni0wLjk2MSwwLjgzOS0xLjk2LDEuMTM3LTIuOTg0bDAuMTEtMC4zOWwwLjM5NSwwLjFjMi45MzUsMC43NDEsNC42MTcsMiw0LjYxNywzLjQ1MyBjMCwxLjQ1Mi0xLjY4MywyLjcxMS00LjYxNywzLjQ1MkwxNi45OTcsMTQuNzk0eiBNMTYuNTg3LDExLjI0MWMwLjQsMC44NjYsMC43MzMsMS43MTgsMC45ODcsMi41NCBjMi4yMy0wLjYyNywzLjU5OS0xLjU4NiwzLjU5OS0yLjU0YzAtMC45NTYtMS4zNjgtMS45MTMtMy41OTktMi41NDJDMTcuMzAxLDkuNTY3LDE2Ljk3MiwxMC40MTYsMTYuNTg3LDExLjI0MUwxNi41ODcsMTEuMjQxeiIKICAgICBpZD0icGF0aDg4OSIKICAgICBzdHlsZT0iZmlsbDojYTBhNWFhO2ZpbGwtb3BhY2l0eToxIiAvPgogIDxwYXRoCiAgICAgZD0iTTYuNDE5LDguNjk1bC0wLjExLTAuMzlDNS40ODMsNS4zOTcsNS43MzMsMy4zMTQsNi45OTYsMi41ODhjMS4yMzUtMC43MTUsMy4yMjIsMC4xMyw1LjMwMywyLjI2NWwwLjI4NCwwLjI5MiBsLTAuMjg0LDAuMjkxYy0wLjczOSwwLjc2OS0xLjQxNSwxLjU5Ni0yLjAyLDIuNDc0bC0wLjExMywwLjE2Mkw5Ljk3LDguMDg4QzguOTA3LDguMTcxLDcuODUxLDguMzQyLDYuODEzLDguNTk3TDYuNDE5LDguNjk1IEw2LjQxOSw4LjY5NXogTTguMDAxLDMuMTY2Yy0wLjIyNCwwLTAuNDIyLDAuMDQ5LTAuNTg5LDAuMTQ1QzYuNTg0LDMuNzg4LDYuNDM4LDUuNDQ5LDcuMDA4LDcuNjkxIGMwLjg5MS0wLjE5NywxLjc5LTAuMzM4LDIuNjk2LTAuNDE3YzAuNTI1LTAuNzQ1LDEuMDk3LTEuNDUzLDEuNzEzLTIuMTIzQzEwLjExNCwzLjg4NCw4Ljg4NCwzLjE2Niw4LjAwMSwzLjE2Nkw4LjAwMSwzLjE2NnogTTE1Ljk5OCwyMC4xNUwxNS45OTgsMjAuMTVjLTEuMTg4LDAtMi43MTQtMC44OTYtNC4yOTgtMi41MjJsLTAuMjgzLTAuMjkxbDAuMjgzLTAuMjljMC43MzktMC43NywxLjQxNi0xLjU5OSwyLjAyMS0yLjQ3NyBsMC4xMTItMC4xNmwwLjE5NC0wLjAxOWMxLjA2NS0wLjA4MiwyLjEyMi0wLjI1MiwzLjE1OC0wLjUwN2wwLjM5NS0wLjFsMC4xMTEsMC4zOTFjMC44MjIsMi45MDYsMC41NzMsNC45OTItMC42ODgsNS43MTggQzE2LjY5OCwyMC4wNjYsMTYuMzUyLDIwLjE1NSwxNS45OTgsMjAuMTVMMTUuOTk4LDIwLjE1eiBNMTIuNTgzLDE3LjMzYzEuMzAyLDEuMjY3LDIuNTMzLDEuOTg2LDMuNDE1LDEuOTg2bDAsMCBjMC4yMjUsMCwwLjQyMy0wLjA1LDAuNTg5LTAuMTQ1YzAuODI5LTAuNDc4LDAuOTc2LTIuMTQyLDAuNDA0LTQuMzg0Yy0wLjg5LDAuMTk4LTEuNzksMC4zNC0yLjY5OCwwLjQxOSBDMTMuNzcxLDE1Ljk1MSwxMy4xOTksMTYuNjYxLDEyLjU4MywxNy4zM3oiCiAgICAgaWQ9InBhdGg4OTEiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MSIgLz4KICA8cGF0aAogICAgIGQ9Ik0xNy41OCw4LjY5NWwtMC4zOTUtMC4wOTljLTEuMDM2LTAuMjU2LTIuMDkzLTAuNDI2LTMuMTU4LTAuNTA5bC0wLjE5NC0wLjAxN2wtMC4xMTItMC4xNjIgYy0wLjYwNC0wLjg3OC0xLjI4MS0xLjcwNS0yLjAyMS0yLjQ3NGwtMC4yODMtMC4yOTFMMTEuNyw0Ljg1M2MyLjA4LTIuMTM0LDQuMDY2LTIuOTc5LDUuMzAzLTIuMjY1IGMxLjI2MiwwLjcyNywxLjUxMywyLjgxLDAuNjg4LDUuNzE3TDE3LjU4LDguNjk1TDE3LjU4LDguNjk1eiBNMTQuMjkzLDcuMjc0YzAuOTU0LDAuMDg1LDEuODU4LDAuMjI4LDIuNjk4LDAuNDE3IGMwLjU3MS0yLjI0MiwwLjQyNS0zLjkwMy0wLjQwNC00LjM4MWMtMC44MjQtMC40NzctMi4zNzUsMC4yNTMtNC4wMDQsMS44NDFDMTMuMTk5LDUuODIxLDEzLjc3MSw2LjUyOSwxNC4yOTMsNy4yNzR6IE04LjAwMSwyMC4xNSBjLTAuMzUzLDAuMDA1LTAuNjk5LTAuMDg0LTEuMDA1LTAuMjU3Yy0xLjI2My0wLjcyNi0xLjUxMy0yLjgxMS0wLjY4OC01LjcxOGwwLjEwOC0wLjM5MWwwLjM5NSwwLjEgYzAuOTY0LDAuMjQzLDIuMDI2LDAuNDE0LDMuMTU4LDAuNTA3bDAuMTk0LDAuMDE5bDAuMTEzLDAuMTZjMC42MDQsMC44NzgsMS4yOCwxLjcwNywyLjAyLDIuNDc3bDAuMjg0LDAuMjlsLTAuMjg0LDAuMjkxIEMxMC43MTMsMTkuMjU1LDkuMTg3LDIwLjE1LDguMDAxLDIwLjE1TDguMDAxLDIwLjE1eiBNNy4wMDgsMTQuNzg4Yy0wLjU3LDIuMjQyLTAuNDI0LDMuOTA2LDAuNDA0LDQuMzg0IGMwLjgyNSwwLjQ3LDIuMzcxLTAuMjU1LDQuMDA1LTEuODQyYy0wLjYxNi0wLjY3LTEuMTg4LTEuMzc5LTEuNzEzLTIuMTIzQzguNzk4LDE1LjEyOCw3Ljg5OCwxNC45ODYsNy4wMDgsMTQuNzg4TDcuMDA4LDE0Ljc4OHoiCiAgICAgaWQ9InBhdGg4OTMiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MSIgLz4KICA8cGF0aAogICAgIGQ9Ik0xMiwxNS4zMTNjLTAuNjg3LDAtMS4zOTItMC4wMjktMi4xLTAuMDg4bC0wLjE5Ni0wLjAxN2wtMC4xMTMtMC4xNjJjLTAuMzk4LTAuNTcyLTAuNzc0LTEuMTYzLTEuMTI2LTEuNzY5IGMtMC4zNDktMC42MDctMC42NzItMS4yMjYtMC45NzEtMS44NTlMNy40MSwxMS4yNDFsMC4wODQtMC4xNzljMC4yOTktMC42MzIsMC42MjItMS4yNTIsMC45NzEtMS44NTggYzAuMzQ3LTAuNTk2LDAuNzI2LTEuMTkyLDEuMTI2LTEuNzdsMC4xMTMtMC4xNkw5LjksNy4yNTZjMS4zOTctMC4xMTcsMi44MDEtMC4xMTcsNC4xOTgsMGwwLjE5NCwwLjAxOWwwLjExMywwLjE2IGMwLjc5OSwxLjE0OSwxLjUwMywyLjM2MiwyLjEsMy42MjhsMC4wODMsMC4xNzlsLTAuMDgzLDAuMTc3Yy0wLjU5NywxLjI2OC0xLjI5OSwyLjQ4MS0yLjEsMy42MjhsLTAuMTEzLDAuMTYybC0wLjE5NCwwLjAxNyBDMTMuMzkyLDE1LjI4MywxMi42ODYsMTUuMzEzLDEyLDE1LjMxM0wxMiwxNS4zMTN6IE0xMC4xNjYsMTQuNDA5YzEuMjM1LDAuMDkzLDIuNDMzLDAuMDkzLDMuNjY3LDAgYzAuNjktMS4wMSwxLjMwMS0yLjA2OCwxLjgzMi0zLjE2OGMtMC41MjktMS4xMDItMS4xNDItMi4xNjEtMS44MzItMy4xNjhjLTEuMjIxLTAuMDk0LTIuNDQ2LTAuMDk0LTMuNjY3LDAgYy0wLjY4OSwxLjAwNy0xLjMwNSwyLjA2NS0xLjgzMiwzLjE2OEM4Ljg2NSwxMi4zNDEsOS40NzksMTMuMzk5LDEwLjE2NiwxNC40MDlMMTAuMTY2LDE0LjQwOXoiCiAgICAgaWQ9InBhdGg4OTUiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MTtzdHJva2U6bm9uZTtzdHJva2Utb3BhY2l0eToxIiAvPgo8L3N2Zz4K");

define('REPR_REACT_ICON_SVG', '<svg class="react" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><circle cx="12" cy="11.245" r="1.785" fill="#626262"/><path d="M7.002 14.794l-.395-.101c-2.934-.741-4.617-2.001-4.617-3.452c0-1.452 1.684-2.711 4.617-3.452l.395-.1l.111.391a19.507 19.507 0 0 0 1.136 2.983l.085.178l-.085.178c-.46.963-.841 1.961-1.136 2.985l-.111.39zm-.577-6.095c-2.229.628-3.598 1.586-3.598 2.542c0 .954 1.368 1.913 3.598 2.54c.273-.868.603-1.717.985-2.54a20.356 20.356 0 0 1-.985-2.542zm10.572 6.095l-.11-.392a19.628 19.628 0 0 0-1.137-2.984l-.085-.177l.085-.179c.46-.961.839-1.96 1.137-2.984l.11-.39l.395.1c2.935.741 4.617 2 4.617 3.453c0 1.452-1.683 2.711-4.617 3.452l-.395.101zm-.41-3.553c.4.866.733 1.718.987 2.54c2.23-.627 3.599-1.586 3.599-2.54c0-.956-1.368-1.913-3.599-2.542a20.683 20.683 0 0 1-.987 2.542z" fill="#626262"/><path d="M6.419 8.695l-.11-.39c-.826-2.908-.576-4.991.687-5.717c1.235-.715 3.222.13 5.303 2.265l.284.292l-.284.291a19.718 19.718 0 0 0-2.02 2.474l-.113.162l-.196.016a19.646 19.646 0 0 0-3.157.509l-.394.098zm1.582-5.529c-.224 0-.422.049-.589.145c-.828.477-.974 2.138-.404 4.38c.891-.197 1.79-.338 2.696-.417a21.058 21.058 0 0 1 1.713-2.123c-1.303-1.267-2.533-1.985-3.416-1.985zm7.997 16.984c-1.188 0-2.714-.896-4.298-2.522l-.283-.291l.283-.29a19.827 19.827 0 0 0 2.021-2.477l.112-.16l.194-.019a19.473 19.473 0 0 0 3.158-.507l.395-.1l.111.391c.822 2.906.573 4.992-.688 5.718a1.978 1.978 0 0 1-1.005.257zm-3.415-2.82c1.302 1.267 2.533 1.986 3.415 1.986c.225 0 .423-.05.589-.145c.829-.478.976-2.142.404-4.384c-.89.198-1.79.34-2.698.419a20.526 20.526 0 0 1-1.71 2.124z" fill="#626262"/><path d="M17.58 8.695l-.395-.099a19.477 19.477 0 0 0-3.158-.509l-.194-.017l-.112-.162A19.551 19.551 0 0 0 11.7 5.434l-.283-.291l.283-.29c2.08-2.134 4.066-2.979 5.303-2.265c1.262.727 1.513 2.81.688 5.717l-.111.39zm-3.287-1.421c.954.085 1.858.228 2.698.417c.571-2.242.425-3.903-.404-4.381c-.824-.477-2.375.253-4.004 1.841c.616.67 1.188 1.378 1.71 2.123zM8.001 20.15a1.983 1.983 0 0 1-1.005-.257c-1.263-.726-1.513-2.811-.688-5.718l.108-.391l.395.1c.964.243 2.026.414 3.158.507l.194.019l.113.16c.604.878 1.28 1.707 2.02 2.477l.284.29l-.284.291c-1.583 1.627-3.109 2.522-4.295 2.522zm-.993-5.362c-.57 2.242-.424 3.906.404 4.384c.825.47 2.371-.255 4.005-1.842a21.17 21.17 0 0 1-1.713-2.123a20.692 20.692 0 0 1-2.696-.419z" fill="#626262"/><path d="M12 15.313c-.687 0-1.392-.029-2.1-.088l-.196-.017l-.113-.162a25.697 25.697 0 0 1-1.126-1.769a26.028 26.028 0 0 1-.971-1.859l-.084-.177l.084-.179c.299-.632.622-1.252.971-1.858c.347-.596.726-1.192 1.126-1.77l.113-.16l.196-.018a25.148 25.148 0 0 1 4.198 0l.194.019l.113.16a25.136 25.136 0 0 1 2.1 3.628l.083.179l-.083.177a24.742 24.742 0 0 1-2.1 3.628l-.113.162l-.194.017c-.706.057-1.412.087-2.098.087zm-1.834-.904c1.235.093 2.433.093 3.667 0a24.469 24.469 0 0 0 1.832-3.168a23.916 23.916 0 0 0-1.832-3.168a23.877 23.877 0 0 0-3.667 0a23.743 23.743 0 0 0-1.832 3.168a24.82 24.82 0 0 0 1.832 3.168z" fill="#626262"/></svg>');

define('REPR_LOGO', '<svg class="logo"
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
