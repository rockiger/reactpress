<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Create_React_Wp
 * @subpackage Create_React_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Create_React_Wp
 * @subpackage Create_React_Wp/admin
 * @author     Marco Laspe <marco@rockiger.com>
 */
class Create_React_Wp_Admin {

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

		/**
		 * This function is provided for demonstration purposes only.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/create-react-wp-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$valid_pages = ['create-react-wp'];
		$page = $_REQUEST['page'] ?? "";

		if (in_array($page, $valid_pages)) {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/create-react-wp-admin.js', array('jquery'), $this->version, false);

			wp_localize_script($this->plugin_name, "crwp", array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'apps' => get_option(('crwp_apps'))
			));
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
			'Create React WP',
			'Create React WP',
			'manage_options',
			'create-react-wp',
			fn () =>  require_once('partials/create-react-wp-admin-display.php'),
			MENU_ICON
		);
	}

	public function handle_crwp_admin_ajax_request() {
		$param = $_REQUEST['param'] ?? "";
		$crwp_apps = get_option('crwp_apps');

		if (!empty($param)) {
			if ($param === "create_react_app") {

				$appname = $_POST['appname'] ?? '';
				$pageslug = $_POST['pageslug'] ?? '';
				// TODO create fold function for functional programming
				// TODO function that checks, if all conditions for a new app are present
				$this->create_new_app($crwp_apps, $appname, $pageslug);
				$this->insert_react_page($appname, $pageslug);
				$this->write_index_html($appname, $this->get_index_html_content($pageslug));

				echo json_encode([
					'status' => 1,
					'message' => "React app created.",
				]);
			} else {
				echo (json_encode($_REQUEST));
			}
		}
		wp_die();
	}

	public function get_index_html_content(string $pageslug) {
		return file_get_contents(site_url() . '/' . $pageslug);
	}

	public function write_index_html(string $appname, $content) {
		$index_html_path = sprintf("%sapps/%s/public/index.html", CRWP_PLUGIN_PATH, $appname);
		return file_put_contents($index_html_path, $content);
	}

	function create_new_app($crwp_apps, string $appname, string $pageslug) {
		if (!is_array($crwp_apps) && $appname && $pageslug) {
			add_option('crwp_apps', [[
				'appname' => $appname,
				'pageslug' => $pageslug,
			]]);
		} elseif ($appname && $pageslug) {
			update_option('crwp_apps', $this->array_add($crwp_apps, [
				'appname' => $appname,
				'pageslug' => $pageslug
			]));
		}

		if ($appname && $pageslug) {
			return $this->create_react_app($appname, $pageslug);
		}
	}

	function create_react_app(string $appname, string $pageslug) {
		$output = '';
		$retval = '';
		chdir(CRWP_PLUGIN_PATH);
		exec(escapeshellcmd("npx create-react-app apps/{$appname}"), $output, $retval);
		return end($output) === 'Happy hacking!' ? true : false;
	}

	function array_add(array $array, $entry) {
		return array_merge($array, [$entry]);
	}

	public function insert_react_page(string $appname, string $pageslug) {
		global $wpdb;
		// check if post_name (which is the slug and should be unique) exist
		$get_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . $wpdb->prefix . "posts WHERE post_name = %s",
				$pageslug
			)
		);

		if (!empty($get_data)) {
			// alreade we have data with this post name
			return ['status' => 'false', 'message' => 'Page already exists.'];
		} else {
			// TODO Use real wp errors
			$result = wp_insert_post(
				array(
					'post_title' => $appname,
					'post_name' => $pageslug,
					'post_status' => 'publish',
					'post_author' => '1',
					'post_content' => CRWP_REACT_ROOT_TAG,
					'post_type' => "page"
				)
			);
			if ($result) {
				return ['status' => 'true', 'message' => 'Page created.'];
			} else {
				return ['status' => 'false', 'message' => "Couldn't create page"];
			}
		}
	}
}

define("CRWP_REACT_ROOT_TAG", "<!-- wp:html -->\n<!-- Please don't change. Block is needed for React app. -->\n<noscript>You need to enable JavaScript to run this app.</noscript>\n<div id=\"root\"></div>\n<!-- /wp:html -->");

define("MENU_ICON",     "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgd2lkdGg9IjI0IgogICBoZWlnaHQ9IjI0IgogICB2aWV3Qm94PSIwIDAgMjQgMjQiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2Zzg5NyIKICAgc29kaXBvZGk6ZG9jbmFtZT0iYnhsLXJlYWN0LnN2ZyIKICAgaW5rc2NhcGU6dmVyc2lvbj0iMS4wLjEgKDA3NjdmODMwMmEsIDIwMjAtMTAtMTcpIj4KICA8bWV0YWRhdGEKICAgICBpZD0ibWV0YWRhdGE5MDMiPgogICAgPHJkZjpSREY+CiAgICAgIDxjYzpXb3JrCiAgICAgICAgIHJkZjphYm91dD0iIj4KICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL3N2Zyt4bWw8L2RjOmZvcm1hdD4KICAgICAgICA8ZGM6dHlwZQogICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+CiAgICAgIDwvY2M6V29yaz4KICAgIDwvcmRmOlJERj4KICA8L21ldGFkYXRhPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM5MDEiIC8+CiAgPHNvZGlwb2RpOm5hbWVkdmlldwogICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIKICAgICBib3JkZXJjb2xvcj0iIzY2NjY2NiIKICAgICBib3JkZXJvcGFjaXR5PSIxIgogICAgIG9iamVjdHRvbGVyYW5jZT0iMTAiCiAgICAgZ3JpZHRvbGVyYW5jZT0iMTAiCiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwIgogICAgIGlua3NjYXBlOnBhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxOTIwIgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjEwMTUiCiAgICAgaWQ9Im5hbWVkdmlldzg5OSIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgaW5rc2NhcGU6em9vbT0iMzYuMTI1IgogICAgIGlua3NjYXBlOmN4PSIxMiIKICAgICBpbmtzY2FwZTpjeT0iMTIiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjE5MjAiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9IjI4IgogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiCiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0ic3ZnODk3IiAvPgogIDxjaXJjbGUKICAgICBjeD0iMTIiCiAgICAgY3k9IjExLjI0NSIKICAgICByPSIxLjc4NSIKICAgICBpZD0iY2lyY2xlODg3IgogICAgIHN0eWxlPSJmaWxsOiNhMGE1YWE7ZmlsbC1vcGFjaXR5OjEiIC8+CiAgPHBhdGgKICAgICBkPSJNNy4wMDIsMTQuNzk0bC0wLjM5NS0wLjEwMWMtMi45MzQtMC43NDEtNC42MTctMi4wMDEtNC42MTctMy40NTJjMC0xLjQ1MiwxLjY4NC0yLjcxMSw0LjYxNy0zLjQ1MmwwLjM5NS0wLjFMNy4xMTMsOC4wOCBjMC4yOTcsMS4wMjMsMC42NzYsMi4wMjIsMS4xMzYsMi45ODNsMC4wODUsMC4xNzhsLTAuMDg1LDAuMTc4Yy0wLjQ2LDAuOTYzLTAuODQxLDEuOTYxLTEuMTM2LDIuOTg1TDcuMDAyLDE0Ljc5NEw3LjAwMiwxNC43OTR6IE02LjQyNSw4LjY5OWMtMi4yMjksMC42MjgtMy41OTgsMS41ODYtMy41OTgsMi41NDJjMCwwLjk1NCwxLjM2OCwxLjkxMywzLjU5OCwyLjU0YzAuMjczLTAuODY4LDAuNjAzLTEuNzE3LDAuOTg1LTIuNTQgQzcuMDI1LDEwLjQxNiw2LjY5Niw5LjU2Nyw2LjQyNSw4LjY5OXogTTE2Ljk5NywxNC43OTRsLTAuMTEtMC4zOTJjLTAuMjk4LTEuMDI0LTAuNjc3LTIuMDIyLTEuMTM3LTIuOTg0bC0wLjA4NS0wLjE3NyBsMC4wODUtMC4xNzljMC40Ni0wLjk2MSwwLjgzOS0xLjk2LDEuMTM3LTIuOTg0bDAuMTEtMC4zOWwwLjM5NSwwLjFjMi45MzUsMC43NDEsNC42MTcsMiw0LjYxNywzLjQ1MyBjMCwxLjQ1Mi0xLjY4MywyLjcxMS00LjYxNywzLjQ1MkwxNi45OTcsMTQuNzk0eiBNMTYuNTg3LDExLjI0MWMwLjQsMC44NjYsMC43MzMsMS43MTgsMC45ODcsMi41NCBjMi4yMy0wLjYyNywzLjU5OS0xLjU4NiwzLjU5OS0yLjU0YzAtMC45NTYtMS4zNjgtMS45MTMtMy41OTktMi41NDJDMTcuMzAxLDkuNTY3LDE2Ljk3MiwxMC40MTYsMTYuNTg3LDExLjI0MUwxNi41ODcsMTEuMjQxeiIKICAgICBpZD0icGF0aDg4OSIKICAgICBzdHlsZT0iZmlsbDojYTBhNWFhO2ZpbGwtb3BhY2l0eToxIiAvPgogIDxwYXRoCiAgICAgZD0iTTYuNDE5LDguNjk1bC0wLjExLTAuMzlDNS40ODMsNS4zOTcsNS43MzMsMy4zMTQsNi45OTYsMi41ODhjMS4yMzUtMC43MTUsMy4yMjIsMC4xMyw1LjMwMywyLjI2NWwwLjI4NCwwLjI5MiBsLTAuMjg0LDAuMjkxYy0wLjczOSwwLjc2OS0xLjQxNSwxLjU5Ni0yLjAyLDIuNDc0bC0wLjExMywwLjE2Mkw5Ljk3LDguMDg4QzguOTA3LDguMTcxLDcuODUxLDguMzQyLDYuODEzLDguNTk3TDYuNDE5LDguNjk1IEw2LjQxOSw4LjY5NXogTTguMDAxLDMuMTY2Yy0wLjIyNCwwLTAuNDIyLDAuMDQ5LTAuNTg5LDAuMTQ1QzYuNTg0LDMuNzg4LDYuNDM4LDUuNDQ5LDcuMDA4LDcuNjkxIGMwLjg5MS0wLjE5NywxLjc5LTAuMzM4LDIuNjk2LTAuNDE3YzAuNTI1LTAuNzQ1LDEuMDk3LTEuNDUzLDEuNzEzLTIuMTIzQzEwLjExNCwzLjg4NCw4Ljg4NCwzLjE2Niw4LjAwMSwzLjE2Nkw4LjAwMSwzLjE2NnogTTE1Ljk5OCwyMC4xNUwxNS45OTgsMjAuMTVjLTEuMTg4LDAtMi43MTQtMC44OTYtNC4yOTgtMi41MjJsLTAuMjgzLTAuMjkxbDAuMjgzLTAuMjljMC43MzktMC43NywxLjQxNi0xLjU5OSwyLjAyMS0yLjQ3NyBsMC4xMTItMC4xNmwwLjE5NC0wLjAxOWMxLjA2NS0wLjA4MiwyLjEyMi0wLjI1MiwzLjE1OC0wLjUwN2wwLjM5NS0wLjFsMC4xMTEsMC4zOTFjMC44MjIsMi45MDYsMC41NzMsNC45OTItMC42ODgsNS43MTggQzE2LjY5OCwyMC4wNjYsMTYuMzUyLDIwLjE1NSwxNS45OTgsMjAuMTVMMTUuOTk4LDIwLjE1eiBNMTIuNTgzLDE3LjMzYzEuMzAyLDEuMjY3LDIuNTMzLDEuOTg2LDMuNDE1LDEuOTg2bDAsMCBjMC4yMjUsMCwwLjQyMy0wLjA1LDAuNTg5LTAuMTQ1YzAuODI5LTAuNDc4LDAuOTc2LTIuMTQyLDAuNDA0LTQuMzg0Yy0wLjg5LDAuMTk4LTEuNzksMC4zNC0yLjY5OCwwLjQxOSBDMTMuNzcxLDE1Ljk1MSwxMy4xOTksMTYuNjYxLDEyLjU4MywxNy4zM3oiCiAgICAgaWQ9InBhdGg4OTEiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MSIgLz4KICA8cGF0aAogICAgIGQ9Ik0xNy41OCw4LjY5NWwtMC4zOTUtMC4wOTljLTEuMDM2LTAuMjU2LTIuMDkzLTAuNDI2LTMuMTU4LTAuNTA5bC0wLjE5NC0wLjAxN2wtMC4xMTItMC4xNjIgYy0wLjYwNC0wLjg3OC0xLjI4MS0xLjcwNS0yLjAyMS0yLjQ3NGwtMC4yODMtMC4yOTFMMTEuNyw0Ljg1M2MyLjA4LTIuMTM0LDQuMDY2LTIuOTc5LDUuMzAzLTIuMjY1IGMxLjI2MiwwLjcyNywxLjUxMywyLjgxLDAuNjg4LDUuNzE3TDE3LjU4LDguNjk1TDE3LjU4LDguNjk1eiBNMTQuMjkzLDcuMjc0YzAuOTU0LDAuMDg1LDEuODU4LDAuMjI4LDIuNjk4LDAuNDE3IGMwLjU3MS0yLjI0MiwwLjQyNS0zLjkwMy0wLjQwNC00LjM4MWMtMC44MjQtMC40NzctMi4zNzUsMC4yNTMtNC4wMDQsMS44NDFDMTMuMTk5LDUuODIxLDEzLjc3MSw2LjUyOSwxNC4yOTMsNy4yNzR6IE04LjAwMSwyMC4xNSBjLTAuMzUzLDAuMDA1LTAuNjk5LTAuMDg0LTEuMDA1LTAuMjU3Yy0xLjI2My0wLjcyNi0xLjUxMy0yLjgxMS0wLjY4OC01LjcxOGwwLjEwOC0wLjM5MWwwLjM5NSwwLjEgYzAuOTY0LDAuMjQzLDIuMDI2LDAuNDE0LDMuMTU4LDAuNTA3bDAuMTk0LDAuMDE5bDAuMTEzLDAuMTZjMC42MDQsMC44NzgsMS4yOCwxLjcwNywyLjAyLDIuNDc3bDAuMjg0LDAuMjlsLTAuMjg0LDAuMjkxIEMxMC43MTMsMTkuMjU1LDkuMTg3LDIwLjE1LDguMDAxLDIwLjE1TDguMDAxLDIwLjE1eiBNNy4wMDgsMTQuNzg4Yy0wLjU3LDIuMjQyLTAuNDI0LDMuOTA2LDAuNDA0LDQuMzg0IGMwLjgyNSwwLjQ3LDIuMzcxLTAuMjU1LDQuMDA1LTEuODQyYy0wLjYxNi0wLjY3LTEuMTg4LTEuMzc5LTEuNzEzLTIuMTIzQzguNzk4LDE1LjEyOCw3Ljg5OCwxNC45ODYsNy4wMDgsMTQuNzg4TDcuMDA4LDE0Ljc4OHoiCiAgICAgaWQ9InBhdGg4OTMiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MSIgLz4KICA8cGF0aAogICAgIGQ9Ik0xMiwxNS4zMTNjLTAuNjg3LDAtMS4zOTItMC4wMjktMi4xLTAuMDg4bC0wLjE5Ni0wLjAxN2wtMC4xMTMtMC4xNjJjLTAuMzk4LTAuNTcyLTAuNzc0LTEuMTYzLTEuMTI2LTEuNzY5IGMtMC4zNDktMC42MDctMC42NzItMS4yMjYtMC45NzEtMS44NTlMNy40MSwxMS4yNDFsMC4wODQtMC4xNzljMC4yOTktMC42MzIsMC42MjItMS4yNTIsMC45NzEtMS44NTggYzAuMzQ3LTAuNTk2LDAuNzI2LTEuMTkyLDEuMTI2LTEuNzdsMC4xMTMtMC4xNkw5LjksNy4yNTZjMS4zOTctMC4xMTcsMi44MDEtMC4xMTcsNC4xOTgsMGwwLjE5NCwwLjAxOWwwLjExMywwLjE2IGMwLjc5OSwxLjE0OSwxLjUwMywyLjM2MiwyLjEsMy42MjhsMC4wODMsMC4xNzlsLTAuMDgzLDAuMTc3Yy0wLjU5NywxLjI2OC0xLjI5OSwyLjQ4MS0yLjEsMy42MjhsLTAuMTEzLDAuMTYybC0wLjE5NCwwLjAxNyBDMTMuMzkyLDE1LjI4MywxMi42ODYsMTUuMzEzLDEyLDE1LjMxM0wxMiwxNS4zMTN6IE0xMC4xNjYsMTQuNDA5YzEuMjM1LDAuMDkzLDIuNDMzLDAuMDkzLDMuNjY3LDAgYzAuNjktMS4wMSwxLjMwMS0yLjA2OCwxLjgzMi0zLjE2OGMtMC41MjktMS4xMDItMS4xNDItMi4xNjEtMS44MzItMy4xNjhjLTEuMjIxLTAuMDk0LTIuNDQ2LTAuMDk0LTMuNjY3LDAgYy0wLjY4OSwxLjAwNy0xLjMwNSwyLjA2NS0xLjgzMiwzLjE2OEM4Ljg2NSwxMi4zNDEsOS40NzksMTMuMzk5LDEwLjE2NiwxNC40MDlMMTAuMTY2LDE0LjQwOXoiCiAgICAgaWQ9InBhdGg4OTUiCiAgICAgc3R5bGU9ImZpbGw6I2EwYTVhYTtmaWxsLW9wYWNpdHk6MTtzdHJva2U6bm9uZTtzdHJva2Utb3BhY2l0eToxIiAvPgo8L3N2Zz4K");
