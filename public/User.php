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

use function ReactPress\Admin\repr_log;

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
	 * Change the page template to the our template on the dropdown if selected.
	 * (C) PRADIP DEBNATH https://www.pradipdebnath.com/2019/08/17/how-to-add-page-template-from-plugin-in-wordpress/
	 * Fix for template incompatibility with Elementor and some other plugins.
	 * (C) Sally CJ https://stackoverflow.com/questions/67696139/error-in-wordpress-with-plugin-reactpress/68455647#answer-67751220
	 * @param $template
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function repr_change_page_template($template) {
		if (is_page()) {
			$meta = get_post_meta(get_the_ID());

			if (
				!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template && 'default' !== $meta['_wp_page_template'][0] &&	strpos($meta['_wp_page_template'][0], 'react-page-template.php')
			) {
				$template = $meta['_wp_page_template'][0];
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
		global $post;
		$repr_apps = get_option('repr_apps') ?? [];
		$pageslugs = $repr_apps ? array_map(fn ($el) => $el['pageslugs'], $repr_apps) : [];

		$valid_pages = array_merge(...$pageslugs);
		$document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
		if (is_page() && in_array($post->post_name, $valid_pages)) {

			// Setting path variables.
			$current_app = array_values(array_filter($repr_apps, fn ($el) => in_array($post->post_name, $el['pageslugs'])))[0];
			$appname = $current_app['appname'];
			$plugin_app_dir_url = escapeshellcmd(REPR_APPS_URL . "/{$appname}/");

			// Use fallback if $_SERVER['DOCUMENT_ROOT'] is not set
			$relative_apppath = escapeshellcmd("/wp-content/reactpress/apps/{$appname}/");
			if (strpos($plugin_app_dir_url, $document_root) === 0) {
				// Add check to ensure that the document root and plugin app dir live on the same disk
				$relative_apppath = explode($document_root, $plugin_app_dir_url)[1];
			}

			$react_app_build = $plugin_app_dir_url . 'build/';
			$manifest_url = $react_app_build . 'asset-manifest.json';

			// Request manifest file.
			set_error_handler(
				// Needed to surpress pontetial errors in file_get_contents and make try/catch
				// usable for php errors - which are much older than exceptions.
				function ($severity, $message, $file, $line) {
					throw new \ErrorException($message, $severity, $severity, $file, $line);
				}
			);
			$request = false;
			try {
				$request = file_get_contents($manifest_url);
			} catch (\Exception $e) {
				repr_log($e->getMessage());
			}
			// remove error handler again.
			restore_error_handler();

			// If the remote request fails, return.
			if (!$request)
				return false;

			// Convert json to php array.
			$files_data = json_decode(strval($request));
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
				wp_enqueue_style('rp-react-app-asset-' . $index, $plugin_app_dir_url . 'build/' . $css_file);
			}

			// Load js files.
			foreach ($js_files as $index => $js_file) {
				wp_enqueue_script('rp-react-app-asset-' . $index, $plugin_app_dir_url . 'build/' . $js_file, array(), 1, true);
			}

			// Variables for app use
			wp_localize_script('rp-react-app-asset-0', 'reactPress', array(
				'user' => wp_get_current_user(),
				'usermeta' => get_user_meta(
					get_current_user_id()
				)
			));
		}
	}

	/**
	 * Add new rewrite rules for every app to make react router usable.
	 * 
	 * @since 1.4.0
	 */
	public function add_repr_apps_rewrite_rules() {
		$repr_apps = is_array(get_option('repr_apps')) ?  get_option('repr_apps') : [];
		$repr_apps_with_routing = array_filter($repr_apps, fn ($el) => $el['allowsRouting']);
		$pageslugArrays = array_map(fn ($el) => $el['pageslugs'], $repr_apps_with_routing);
		$pageslugs = array_merge(...$pageslugArrays);

		foreach ($pageslugs as $pageslug) {
			add_rewrite_rule('^' . $pageslug . '/(.*)?', 'index.php?pagename=' . $pageslug, 'top');
		}
	}


	public function site_custom_endpoint($wp_rewrite) {

		// repr_log($wp_rewrite);
		return $wp_rewrite->rules;
	}
}
