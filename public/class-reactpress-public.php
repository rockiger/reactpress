<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Create_React_Wp
 * @subpackage Create_React_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Create_React_Wp
 * @subpackage Create_React_Wp/public
 * @author     Marco Laspe <marco@rockiger.com>
 */
class Create_React_Wp_Public {

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
		 * defined in Create_React_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Create_React_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-create-react-app-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-create-react-app-public.js', array('jquery'), $this->version, false);

		$this->wpcra_load_react_app();
	}


	/**
	 * Change the page template to the our template on the dropdown if selected.
	 * (C) PRADIP DEBNATH https://bit.ly/3iSRjpu
	 * 
	 * @param $template
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function wpcra_change_page_template($template) {
		if (is_page()) {
			$meta = get_post_meta(get_the_ID());

			if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
				$template = $meta['_wp_page_template'][0];
			}
		}

		return $template;
	}

	/**
	 * Load react app files im page should contain a react app.
	 * (C) Ben Broide https://bit.ly/3iQXsCi
	 * 
	 * @return bool|void
	 * @since 1.0.0
	 */
	function wpcra_load_react_app() {
		// Only load react app scripts on pages that contain our apps
		global $post;
		$wpcra_apps = get_option('wpcra_apps');
		$valid_pages = array_map(fn ($el) => $el['pageslug'], $wpcra_apps);

		if (is_page() && in_array($post->post_name, $valid_pages)) {

			// Setting path variables.
			$current_app = array_values(array_filter($wpcra_apps, fn ($el) => $el['pageslug'] === $post->post_name))[0];
			$appname = $current_app['appname'];
			$plugin_app_dir_url = escapeshellcmd(WPCRA_PLUGIN_PATH . "apps/{$appname}/");

			$relative_apppath = "/wp-content/plugins/wp-create-react-app/apps/{$appname}/"; // fallback, because get_home_path() seems to don't exists on nginx
			if (function_exists('get_home_path')) {
				$relative_apppath = '/' . explode(
					get_home_path(),
					$plugin_app_dir_url
				)[1];
			}

			$react_app_build = $plugin_app_dir_url . 'build/';
			$manifest_url = $react_app_build . 'asset-manifest.json';

			// Request manifest file.
			$request = @file_get_contents($manifest_url);

			// If the remote request fails, return.
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
				wp_enqueue_style('wpcra-react-app-asset-' . $index, $relative_apppath . 'build/' . $css_file);
			}

			// Load js files.
			foreach ($js_files as $index => $js_file) {
				wp_enqueue_script('wpcra-react-app-asset-' . $index, $relative_apppath . 'build/' . $js_file, array(), 1, true);
			}
		}
	}
}
