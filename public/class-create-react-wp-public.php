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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/create-react-wp-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/create-react-wp-public.js', array('jquery'), $this->version, false);
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
	public function crwp_change_page_template($template) {
		if (is_page()) {
			$meta = get_post_meta(get_the_ID());

			if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
				$template = $meta['_wp_page_template'][0];
			}
		}

		return $template;
	}
}
