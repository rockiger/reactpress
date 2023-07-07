<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Fulcrum
 * @subpackage Fulcrum/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fulcrum
 * @subpackage Fulcrum/public
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace Fulcrum\User;

use Fulcrum\Admin\Utils;

use function Fulcrum\Admin\fulc_log;

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
		 * defined in Fulcrum_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fulcrum_Loader will then create the relationship
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

		$this->fulc_load_react_app();
	}

	/**
	 * Add the type="module" attribute to the script tag, for 
	 * Fulcrum apps, to remove some errors with Vite.
	 */
	function add_type_module_to_scripts($tag, $handle, $src) {
		if (str_starts_with($handle, 'rp-react-app-asset')) {
			$tag = '<script id="' . $handle . '" type="module" src="' . esc_url($src) . '"></script>';
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
	public function fulc_change_page_template($template) {
		if (is_page()) {
			$meta = get_post_meta(intval(get_the_ID()));

			// Check if the page template is a Fulcrum template
			if (
				!empty($meta['_wp_page_template'][0]) &&
				$meta['_wp_page_template'][0] != $template &&
				'default' !== $meta['_wp_page_template'][0] &&
				strpos($meta['_wp_page_template'][0], 'react-page-template.php')
			) {
				// At this point we know it's a Fulcrum template
				$template = $meta['_wp_page_template'][0];

				// determine the location of the templates folder reference
				$ndx = intval(strpos($template, 'templates/'));

				// If it's not at the beginning
				if (0 != $ndx) {
					// change the template to be relative to the plugin's folder (i.e., templates/react-page-template.php)
					$template = substr($template, $ndx);
				}

				// Prepend the real path at runtime
				$template = FULC_PLUGIN_PATH . $template;
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

	function fulc_load_react_app() {
		// Only load react app scripts on pages that contain our apps
		global $post;
		$fulc_apps = Utils::get_apps();
		$pageIds = $fulc_apps ? array_map(fn ($el) => $el['pageIds'], $fulc_apps) : [];

		$valid_pages = array_merge(...$pageIds);
		if (is_page() && in_array($post->ID, $valid_pages)) {
			$suitable_apps = array_values(array_filter($fulc_apps, fn ($el) => in_array($post->ID, $el['pageIds'])));
			foreach ($suitable_apps as $app_index => $current_app) {

				// Setting path variables.
				$appname = $current_app['appname'];
				$plugin_app_dir_url = escapeshellcmd(FULC_APPS_URL . "/{$appname}/");
				$apptype = Utils::get_app_type($appname);
				$css_files = [];
				$js_files = [];
				// setting up vite app
				if ($apptype === 'development_vite' || $apptype === 'deployment_vite') {
					$react_app_build = FULC_APPS_PATH . '/' . $appname . '/dist/assets';
					$assets_files = scandir($react_app_build);
					if (!$assets_files) {
						return false;
					}
					// We use array_values to reindex the array (because PHP)
					$js_files = array_map(fn ($file_name) => Utils::app_path($appname, true) . '/dist/assets/' . $file_name, array_values(array_filter(
						$assets_files,
						fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'js'
					)));
					$css_files = array_map(fn ($file_name) => Utils::app_path($appname, true) . '/dist/assets/' . $file_name, array_filter(
						$assets_files,
						fn ($file_string) => pathinfo($file_string, PATHINFO_EXTENSION) === 'css'
					));
				}

				// setting up cra app
				else {
					$react_app_build = $plugin_app_dir_url . 'build/';
					$manifest_path = escapeshellcmd(FULC_APPS_PATH . "/{$appname}/build/asset-manifest.json");

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
						fulc_log($e->getMessage());
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

				// Load css files.
				foreach ($css_files as $index => $css_file) {
					wp_enqueue_style('rp-react-app-asset-' . $app_index . '-' . $index, $css_file);
				}

				// Load js files.
				foreach ($js_files as $index => $js_file) {
					wp_enqueue_script('rp-react-app-asset-' . $app_index . '-' . $index, $js_file, array(), '1', true);
				}
			}
			// Variables for app use
			$current_user = wp_get_current_user();
			unset($current_user->user_pass); // Don't show encypted password for security reasons.
			wp_localize_script('rp-react-app-asset-0-0', 'fulcrum', array(
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

	/**
	 * Add new rewrite rules for every app to make react router usable.
	 * 
	 * @since 1.4.0
	 */
	public function add_fulc_apps_rewrite_rules() {
		$fulc_apps = Utils::get_apps();
		$fulc_apps_with_routing = array_filter($fulc_apps, fn ($el) => $el['allowsRouting']);
		$permalinkArrays = array_map(
			fn ($el) => array_map(
				fn ($pg) => $pg['permalink'],
				$el['pages']
			),
			$fulc_apps_with_routing
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

	public function cptui_register_my_cpts() {

		/**
		 * Post Type: Fulcrum Pages.
		 */

		$labels = [
			"name" => esc_html__("Fulcrum Pages", "custom-post-type-ui"),
			"singular_name" => esc_html__("Fulcrum Page", "custom-post-type-ui"),
			"menu_name" => esc_html__("Fulcrum Pages", "custom-post-type-ui"),
			"all_items" => esc_html__("All Fulcrum Pages", "custom-post-type-ui"),
			"add_new" => esc_html__("Add new", "custom-post-type-ui"),
			"add_new_item" => esc_html__("Add new Fulcrum Page", "custom-post-type-ui"),
			"edit_item" => esc_html__("Edit Fulcrum Page", "custom-post-type-ui"),
			"new_item" => esc_html__("New Fulcrum Page", "custom-post-type-ui"),
			"view_item" => esc_html__("View Fulcrum Page", "custom-post-type-ui"),
			"view_items" => esc_html__("View Fulcrum Pages", "custom-post-type-ui"),
			"search_items" => esc_html__("Search Fulcrum Pages", "custom-post-type-ui"),
			"not_found" => esc_html__("No Fulcrum Pages found", "custom-post-type-ui"),
			"not_found_in_trash" => esc_html__("No Fulcrum Pages found in trash", "custom-post-type-ui"),
			"parent" => esc_html__("Parent Fulcrum Page:", "custom-post-type-ui"),
			"featured_image" => esc_html__("Featured image for this Fulcrum Page", "custom-post-type-ui"),
			"set_featured_image" => esc_html__("Set featured image for this Fulcrum Page", "custom-post-type-ui"),
			"remove_featured_image" => esc_html__("Remove featured image for this Fulcrum Page", "custom-post-type-ui"),
			"use_featured_image" => esc_html__("Use as featured image for this Fulcrum Page", "custom-post-type-ui"),
			"archives" => esc_html__("Fulcrum Page archives", "custom-post-type-ui"),
			"insert_into_item" => esc_html__("Insert into Fulcrum Page", "custom-post-type-ui"),
			"uploaded_to_this_item" => esc_html__("Upload to this Fulcrum Page", "custom-post-type-ui"),
			"filter_items_list" => esc_html__("Filter Fulcrum Pages list", "custom-post-type-ui"),
			"items_list_navigation" => esc_html__("Fulcrum Pages list navigation", "custom-post-type-ui"),
			"items_list" => esc_html__("Fulcrum Pages list", "custom-post-type-ui"),
			"attributes" => esc_html__("Fulcrum Pages attributes", "custom-post-type-ui"),
			"name_admin_bar" => esc_html__("Fulcrum Page", "custom-post-type-ui"),
			"item_published" => esc_html__("Fulcrum Page published", "custom-post-type-ui"),
			"item_published_privately" => esc_html__("Fulcrum Page published privately.", "custom-post-type-ui"),
			"item_reverted_to_draft" => esc_html__("Fulcrum Page reverted to draft.", "custom-post-type-ui"),
			"item_scheduled" => esc_html__("Fulcrum Page scheduled", "custom-post-type-ui"),
			"item_updated" => esc_html__("Fulcrum Page updated.", "custom-post-type-ui"),
			"parent_item_colon" => esc_html__("Parent Fulcrum Page:", "custom-post-type-ui"),
		];

		$args = [
			"label" => esc_html__("Fulcrum Pages", "custom-post-type-ui"),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => true,
			"can_export" => false,
			"rewrite" => ["slug" => "fulcrum_page", "with_front" => true],
			"query_var" => true,
			"supports" => ["title", "editor", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "author", "page-attributes"],
			"taxonomies" => ["fulcrum_space"],
			"show_in_graphql" => true,
			"graphql_single_name" => "FulcrumPage",
			"graphql_plural_name" => "FulcrumPages",
		];

		register_post_type("fulcrum_page", $args);
	}

	public function cptui_register_my_taxes() {

		/**
		 * Taxonomy: Fulcrum Spaces.
		 */

		$labels = [
			"name" => esc_html__("Fulcrum Spaces", "custom-post-type-ui"),
			"singular_name" => esc_html__("Fulcrum Space", "custom-post-type-ui"),
			"menu_name" => esc_html__("Fulcrum Spaces", "custom-post-type-ui"),
			"all_items" => esc_html__("All Fulcrum Spaces", "custom-post-type-ui"),
			"edit_item" => esc_html__("Edit Fulcrum Space", "custom-post-type-ui"),
			"view_item" => esc_html__("View Fulcrum Space", "custom-post-type-ui"),
			"update_item" => esc_html__("Update Fulcrum Space name", "custom-post-type-ui"),
			"add_new_item" => esc_html__("Add new Fulcrum Space", "custom-post-type-ui"),
			"new_item_name" => esc_html__("New Fulcrum Space name", "custom-post-type-ui"),
			"parent_item" => esc_html__("Parent Fulcrum Space", "custom-post-type-ui"),
			"parent_item_colon" => esc_html__("Parent Fulcrum Space:", "custom-post-type-ui"),
			"search_items" => esc_html__("Search Fulcrum Spaces", "custom-post-type-ui"),
			"popular_items" => esc_html__("Popular Fulcrum Spaces", "custom-post-type-ui"),
			"separate_items_with_commas" => esc_html__("Separate Fulcrum Spaces with commas", "custom-post-type-ui"),
			"add_or_remove_items" => esc_html__("Add or remove Fulcrum Spaces", "custom-post-type-ui"),
			"choose_from_most_used" => esc_html__("Choose from the most used Fulcrum Spaces", "custom-post-type-ui"),
			"not_found" => esc_html__("No Fulcrum Spaces found", "custom-post-type-ui"),
			"no_terms" => esc_html__("No Fulcrum Spaces", "custom-post-type-ui"),
			"items_list_navigation" => esc_html__("Fulcrum Spaces list navigation", "custom-post-type-ui"),
			"items_list" => esc_html__("Fulcrum Spaces list", "custom-post-type-ui"),
			"back_to_items" => esc_html__("Back to Fulcrum Spaces", "custom-post-type-ui"),
			"name_field_description" => esc_html__("The name is how it appears on your site.", "custom-post-type-ui"),
			"parent_field_description" => esc_html__("Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.", "custom-post-type-ui"),
			"slug_field_description" => esc_html__("The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "custom-post-type-ui"),
			"desc_field_description" => esc_html__("The description is not prominent by default; however, some themes may show it.", "custom-post-type-ui"),
		];


		$args = [
			"label" => esc_html__("Fulcrum Spaces", "custom-post-type-ui"),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => true,
			"hierarchical" => false,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"query_var" => true,
			"rewrite" => ['slug' => 'fulcrum_space', 'with_front' => true,],
			"show_admin_column" => false,
			"show_in_rest" => true,
			"show_tagcloud" => false,
			"rest_base" => "fulcrum_space",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"rest_namespace" => "wp/v2",
			"show_in_quick_edit" => false,
			"sort" => false,
			"show_in_graphql" => true,
			"graphql_single_name" => "FulcrumSpace",
			"graphql_plural_name" => "FulcrumSpaces",
		];
		register_taxonomy("fulcrum_space", ["fulcrum_page"], $args);
	}

	public function register_graphql_fulcrum_page() {
		if (!function_exists('register_graphql_fields')) {
			return; //! Show the user
		}
		register_graphql_fields('FulcrumPage', [
			'isOverview' => [
				'type' => 'Boolean',
				'resolve' => function ($post) {
					// Get the meta field
					return !!get_post_meta(User::get_database_id_from_graphql_id($post->id), 'isOverview', true);
				}
			],
			'width' => [
				'type' => 'String',
				'description' => 'The width of the page',
				'resolve' => function ($post) {
					// Get the meta field
					$width = get_post_meta(User::get_database_id_from_graphql_id($post->id), 'width', true);
					return !!$width ? $width : 'standard';
				}
			],
		]);
	}

	public function register_graphql_update_fulcrum_page_meta() {
		if (!function_exists('register_graphql_mutation')) {
			return; //! Show the user
		}
		register_graphql_mutation('updateFulcrumPageMeta', [
			'inputFields' => [
				'id' => [
					'type' => ['non_null' => 'ID'],
					'description' => 'The post id'
				],
				'width' => [
					'type' => 'String',
					'description' => 'The width of the page (wide|standard)'

				],
				'isOverview' => [
					'type' => 'Boolean'
				],
			],
			'outputFields' => [
				'isOverview' => ['type' => 'Boolean'],
				'width' => [
					'type' => 'String',
				],
			],
			'mutateAndGetPayload' => function ($input) {
				$database_id = User::get_database_id_from_graphql_id($input['id']);
				if (isset($input['isOverview'])) {
					update_post_meta($database_id, 'isOverview', $input['isOverview']);
				}
				if (isset($input['width'])) {
					update_post_meta($database_id, 'width', $input['width']);
				}

				$isOverview = get_post_meta($database_id, 'isOverview', true);
				$width = get_post_meta($database_id, 'width', true);

				return  [
					'isOverview' => !!$isOverview,
					'width' => !!$width ? $width : 'standard'
				];
			}
		]);
	}

	/**
	 * Takes the "global ID" created by self::toGlobalId, and returns the type name and ID
	 * used to create it.
	 *
	 * @param $globalId
	 * @return array
	 */
	public static function fromGlobalRelayId($globalId) {
		$unbasedGlobalId = base64_decode($globalId);
		$delimiterPos = strpos($unbasedGlobalId, ':');
		return [
			'type' => substr($unbasedGlobalId, 0, $delimiterPos),
			'id' => substr($unbasedGlobalId, $delimiterPos + 1)
		];
	}

	/**
	 * Helper function to get the WordPress database ID from a GraphQL ID type input.
	 *
	 * Returns false if not a valid ID.
	 *
	 * @param int|string $id The ID from the input args. Can be either the database ID (as either a string or int) or the global Relay ID.
	 *
	 * @return int|false
	 */
	public static function get_database_id_from_graphql_id($id) {
		// If we already have the database ID, send it back as an integer.
		if (is_numeric($id)) {
			return absint($id);
		}

		$id_parts = User::fromGlobalRelayId($id);

		return !empty($id_parts['id']) && is_numeric($id_parts['id']) ? absint($id_parts['id']) : false;
	}
}
