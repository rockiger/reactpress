<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Reactpress
 * @subpackage Reactpress/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Reactpress
 * @subpackage Reactpress/public
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace ReactPress\User;

use ReactPress\Admin\Utils;

use function \repr_log;

class User {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reactpress_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reactpress_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/reactpress-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/reactpress-public.js', array('jquery'), $this->version, false);

		$this->repr_load_react_app();
	}

	/**
	 * Add the type="module" attribute to the script tag, for
	 * ReactPress apps, to remove some errors with Vite.
	 */
	function add_type_module_to_scripts($tag, $handle, $src) {
        if (str_starts_with($handle, 'rp-react-app-asset')) {
          // Write the first JS as script and the rest (dependents) as modulepreload links
		  if (str_ends_with($handle, '-0')) {
            $tag = '<script id="' . $handle . '" type="module" crossorigin src="' . esc_url($src) . '"></script>';
		  } else {
            $tag = '<link id="' . $handle . '" rel="modulepreload" crossorigin href="' . esc_url($src) . '">';
		  }
		}

		return $tag;
	}

	/**
	 * Change the page template to the our template on the dropdown if selected.
	 * (C) PRADIP DEBNATH https://www.pradipdebnath.com/2019/08/17/how-to-add-page-template-from-plugin-in-wordpress/
	 * Fix for template incompatibility with Elementor and some other plugins.
	 * (C) Sally CJ https://stackoverflow.com/questions/67696139/error-in-wordpress-with-plugin-reactpress/68455647#answer-67751220
	 * @param mixed $template
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function repr_change_page_template($template) {
		if (is_page()) {
			$meta = get_post_meta(intval(get_the_ID()));

			// Check if the page template is a Reactpress template
			if (
				!empty($meta['_wp_page_template'][0]) &&
				$meta['_wp_page_template'][0] != $template &&
				'default' !== $meta['_wp_page_template'][0] &&
				strpos($meta['_wp_page_template'][0], 'react-page-template.php')
			) {
				// At this point we know it's a Reactpress template
				$template = $meta['_wp_page_template'][0];

				// determine the location of the templates folder reference
				$ndx = intval(strpos($template, 'templates/'));

				// If it's not at the beginning
				if (0 != $ndx) {
					// change the template to be relative to the plugin's folder (i.e., templates/react-page-template.php)
					$template = substr($template, $ndx);
				}

				// Prepend the real path at runtime
				$template = REPR_PLUGIN_PATH . $template;
			}
		}

		return $template;
	}

	/**
	 * Load react app files im page should contain a react app.
	 * (C) Ben Broide https://medium.com/swlh/wordpress-create-react-app-integration-30b41657b79e
	 *
	 * @return bool|void
	 * @since 1.0.0
	 */

	function repr_load_react_app() {
		// Only load react app scripts on pages that contain our apps
		global $post, $wp_scripts, $wp_styles;
		$repr_apps = Utils::get_apps();
		$pageIds = $repr_apps ? array_map(fn ($el) => $el['pageIds'], $repr_apps) : [];

		$valid_pages = array_merge(...$pageIds);
		if (is_page() && in_array($post->ID, $valid_pages)) {
			$suitable_apps = array_values(array_filter($repr_apps, fn ($el) => in_array($post->ID, $el['pageIds'])));
			foreach ($suitable_apps as $app_index => $current_app) {

				// Setting path variables.
				$appname = $current_app['appname'];
				$plugin_app_dir_url = escapeshellcmd(REPR_APPS_URL . "/{$appname}/");
				$apptype = Utils::get_app_type($appname);
				$css_files = [];
				$js_files = [];
				// setting up vite app
				if ($apptype === 'development_vite' || $apptype === 'deployment_vite') {
				    [$js_files, $css_files] = $this->setup_vite_application_files($appname);
				}
				// setting up cra app
				else {
				    [$js_files, $css_files] = $this->setup_cra_application_files($appname);
				}

				if (empty($js_files)) {
				    return false;
				}

				// deque styles and scripts
				if (basename(get_page_template_slug($post)) === 'empty-react-page-template.php') {
					foreach ($wp_styles->queue as $handle) {
						if (!(str_starts_with($handle, 'rp-react-app-asset-'))) {
							wp_dequeue_style($handle);
						}
					};
					foreach ($wp_scripts->queue as $handle) {
						if (!(str_starts_with($handle, 'rp-react-app-asset-'))) {
							wp_dequeue_script($handle);
						}
					}
				}

				// Load css files.
				foreach ($css_files as $index => $css_file) {
					wp_enqueue_style('rp-react-app-asset-' . $app_index . '-' . $index, $css_file);
				}

				// Load js files.
				foreach ($js_files as $index => $js_file) {
					wp_enqueue_script('rp-react-app-asset-' . $app_index . '-' . $index, $js_file, array(), '1', false);
				}
			}
			// Variables for app use
			$current_user = wp_get_current_user();
			unset($current_user->user_pass); // Don't show encypted password for security reasons.
			wp_localize_script('rp-react-app-asset-0-0', 'reactPress', array(
				'api' => [
					'nonce' => wp_create_nonce('wp_rest'),
					'rest_url' => esc_url_raw(rest_url()),
					'graphql_url' => esc_url_raw(site_url(get_option('graphql_general_settings', ["graphql_endpoint" => 'graphql'])['graphql_endpoint'])),
				],
				'post' => $post,
				'user' => $current_user,
				'usermeta' => get_user_meta(
					get_current_user_id()
				),
			));
		}
	}

	private function setup_vite_application_files(string $appname): array
	{
		$js_files = [];
		$css_files = [];

		$react_app_build = REPR_APPS_PATH . '/' . $appname . '/dist/assets';
		$assets_files = scandir($react_app_build);

		if ($assets_files) {
			$appAssetsUrl = Utils::app_url($appname) . '/dist/assets/';

			// Filter down to the js files
			$js_files = array_filter(
				$assets_files,
				fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'js'
				);

			// Sort files so index*.js is first, followed by the other js files in sorted order
			usort($js_files, function (string $a, string $b): int {
				$result = 0;

				if (0 === stripos($a, 'index')) {
					$result = -1;
				} elseif (0 === stripos($b, 'index')) {
					$result = 1;
				} elseif (0 === strpos($a, '@') && 0 === strpos($b, '@')) {
					$result = strcmp($a, $b);
				} elseif (0 === strpos($a, '@')) {
					return 1;
				} elseif (0 === strpos($b, '@')) {
					return -1;
				} else {
					$result = strcmp($a, $b);
				}

				return $result;
			});

			// We use array_values to reindex the array (because PHP)
			$js_files = array_map(fn ($file_name) => $appAssetsUrl . $file_name, array_values($js_files));

			$css_files = array_map(fn ($file_name) => $appAssetsUrl . $file_name, array_filter(
				$assets_files,
				fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'css'
				));
		}

		return [$js_files, $css_files];
	}

	private function setup_cra_application_files(string $appname): array
	{
		$js_files = [];
		$css_files = [];

		$react_app_build = escapeshellcmd(REPR_APPS_URL . "/{$appname}/") . 'build/';
		$manifest_path = escapeshellcmd(REPR_APPS_PATH . "/{$appname}/build/asset-manifest.json");

		// Request manifest file.
		set_error_handler(
			// Needed to surpress pontential errors in file_get_contents and make try/catch
			// usable for php errors - which are much older than exceptions.
			function ($severity, $message, $file, $line) {
				throw new \ErrorException($message, $severity, $severity, $file, $line);
			}
			);
		$request = false;
		try {
			$request = file_get_contents($manifest_path);
		} catch (\Exception $e) {
			repr_log($e->getMessage());
		}
		// remove error handler again.
		restore_error_handler();

		// If the remote request fails, return.
		if ($request) {
			// Convert json to php array.
			$files_data = json_decode(strval($request));
			if ($files_data) {
				if (property_exists($files_data, 'entrypoints')) {
					// Get assets links.
					$assets_files = $files_data->entrypoints;

					// We use array_values to reindex the array (because PHP)
					$js_files = array_map(fn ($file_name) => $react_app_build . $file_name, array_values(array_filter(
						$assets_files,
						fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'js'
						)));
					$css_files = array_map(fn ($file_name) => $react_app_build . $file_name, array_filter(
						$assets_files,
						fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'css'
						));
				}
			}
		}

		return [$js_files, $css_files];
	}

    /**
	 * Add new rewrite rules for every app to make react router usable.
	 *
	 * @since 1.4.0
	 */
	public function add_repr_apps_rewrite_rules() {
		$repr_apps = Utils::get_apps();
		$repr_apps_with_routing = array_filter($repr_apps, fn ($el) => $el['allowsRouting']);
		$permalinkArrays = array_map(
			fn ($el) => array_map(
				fn ($pg) => $pg['permalink'],
				$el['pages']
			),
			$repr_apps_with_routing
		);
		$permalinks = array_merge(...$permalinkArrays);

		foreach ($permalinks as $permalink) {
			add_rewrite_rule(
				'^' .
					wp_make_link_relative($permalink) .
					'/(.*)?',
				'index.php?pagename=' .
					wp_make_link_relative($permalink),
				'top'
			);
		}
	}


	/** @phpstan-ignore-next-line */
	public function site_custom_endpoint($wp_rewrite) {
		return $wp_rewrite->rules;
	}
}
