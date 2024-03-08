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
		global $post, $wp_scripts, $wp_styles;
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
						fulc_debug($e->getMessage());
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

				// deque styles and scripts
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
			wp_localize_script('rp-react-app-asset-0-0', 'reactPress', array(
				'api' => [
					'nonce' => wp_create_nonce('wp_rest'),
					'rest_url' => esc_url_raw(rest_url()),
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

	function cptui_register_my_cpts() {

		/**
		 * Post Type: Wiki Pages.
		 */

		$labels = [
			"name" => esc_html__("Wiki Pages", "fulcrum"),
			"singular_name" => esc_html__("Wiki Page", "fulcrum"),
			"menu_name" => esc_html__("My Wiki Pages", "fulcrum"),
			"all_items" => esc_html__("All Wiki Pages", "fulcrum"),
			"add_new" => esc_html__("Add new", "fulcrum"),
			"add_new_item" => esc_html__("Add new Wiki Page", "fulcrum"),
			"edit_item" => esc_html__("Edit Wiki Page", "fulcrum"),
			"new_item" => esc_html__("New Wiki Page", "fulcrum"),
			"view_item" => esc_html__("View Wiki Page", "fulcrum"),
			"view_items" => esc_html__("View Wiki Pages", "fulcrum"),
			"search_items" => esc_html__("Search Wiki Pages", "fulcrum"),
			"not_found" => esc_html__("No Wiki Pages found", "fulcrum"),
			"not_found_in_trash" => esc_html__("No Wiki Pages found in trash", "fulcrum"),
			"parent" => esc_html__("Parent Wiki Page:", "fulcrum"),
			"featured_image" => esc_html__("Featured image for this Wiki Page", "fulcrum"),
			"set_featured_image" => esc_html__("Set featured image for this Wiki Page", "fulcrum"),
			"remove_featured_image" => esc_html__("Remove featured image for this Wiki Page", "fulcrum"),
			"use_featured_image" => esc_html__("Use as featured image for this Wiki Page", "fulcrum"),
			"archives" => esc_html__("Wiki Page archives", "fulcrum"),
			"insert_into_item" => esc_html__("Insert into Wiki Page", "fulcrum"),
			"uploaded_to_this_item" => esc_html__("Upload to this Wiki Page", "fulcrum"),
			"filter_items_list" => esc_html__("Filter Wiki Pages list", "fulcrum"),
			"items_list_navigation" => esc_html__("Wiki Pages list navigation", "fulcrum"),
			"items_list" => esc_html__("Wiki Pages list", "fulcrum"),
			"attributes" => esc_html__("Wiki Pages attributes", "fulcrum"),
			"name_admin_bar" => esc_html__("Wiki Page", "fulcrum"),
			"item_published" => esc_html__("Wiki Page published", "fulcrum"),
			"item_published_privately" => esc_html__("Wiki Page published privately.", "fulcrum"),
			"item_reverted_to_draft" => esc_html__("Wiki Page reverted to draft.", "fulcrum"),
			"item_trashed" => esc_html__("Wiki Page trashed.", "fulcrum"),
			"item_scheduled" => esc_html__("Wiki Page scheduled", "fulcrum"),
			"item_updated" => esc_html__("Wiki Page updated.", "fulcrum"),
			"parent_item_colon" => esc_html__("Parent Wiki Page:", "fulcrum"),
		];

		$args = [
			"label" => esc_html__("Wiki Pages", "fulcrum"),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "wikipages",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "wikipage", // "wikipage"
			"map_meta_cap" => true,
			"hierarchical" => true,
			"can_export" => true,
			"rewrite" => ["slug" => "wikipages", "with_front" => true],
			"query_var" => true,
			"menu_icon" => "dashicons-admin-site",
			"supports" => ["title", "editor", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "author", "page-attributes", "post-formats"],
			"taxonomies" => ["wikispace"],
			"show_in_graphql" => false,
		];

		register_post_type("wikipage", $args);
	}

	function cptui_register_my_taxes() {

		/**
		 * Taxonomy: Wiki Spaces.
		 */

		$labels = [
			"name" => esc_html__("Wiki Spaces", "fulcrum"),
			"singular_name" => esc_html__("Wiki Space", "fulcrum"),
			"menu_name" => esc_html__("Wiki Spaces", "fulcrum"),
			"all_items" => esc_html__("All Wiki Spaces", "fulcrum"),
			"edit_item" => esc_html__("Edit Wiki Space", "fulcrum"),
			"view_item" => esc_html__("View Wiki Space", "fulcrum"),
			"update_item" => esc_html__("Update Wiki Space name", "fulcrum"),
			"add_new_item" => esc_html__("Add new Wiki Space", "fulcrum"),
			"new_item_name" => esc_html__("New Wiki Space name", "fulcrum"),
			"parent_item" => esc_html__("Parent Wiki Space", "fulcrum"),
			"parent_item_colon" => esc_html__("Parent Wiki Space:", "fulcrum"),
			"search_items" => esc_html__("Search Wiki Spaces", "fulcrum"),
			"popular_items" => esc_html__("Popular Wiki Spaces", "fulcrum"),
			"separate_items_with_commas" => esc_html__("Separate Wiki Spaces with commas", "fulcrum"),
			"add_or_remove_items" => esc_html__("Add or remove Wiki Spaces", "fulcrum"),
			"choose_from_most_used" => esc_html__("Choose from the most used Wiki Spaces", "fulcrum"),
			"not_found" => esc_html__("No Wiki Spaces found", "fulcrum"),
			"no_terms" => esc_html__("No Wiki Spaces", "fulcrum"),
			"items_list_navigation" => esc_html__("Wiki Spaces list navigation", "fulcrum"),
			"items_list" => esc_html__("Wiki Spaces list", "fulcrum"),
			"back_to_items" => esc_html__("Back to Wiki Spaces", "fulcrum"),
			"name_field_description" => esc_html__("The name is how it appears on your site.", "fulcrum"),
			"parent_field_description" => esc_html__("Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.", "fulcrum"),
			"slug_field_description" => esc_html__("The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "fulcrum"),
			"desc_field_description" => esc_html__("The description is not prominent by default; however, some themes may show it.", "fulcrum"),
		];


		$args = [
			"label" => esc_html__("Wiki Spaces", "fulcrum"),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => true,
			"hierarchical" => false,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"query_var" => true,
			"rewrite" => ['slug' => 'wikispaces', 'with_front' => true,],
			"show_admin_column" => false,
			"show_in_rest" => true,
			"show_tagcloud" => true,
			"rest_base" => "wikispaces",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"rest_namespace" => "wp/v2",
			"show_in_quick_edit" => true,
			"sort" => true,
			"show_in_graphql" => false,
			// "default_term" => ['name' => 'Default'], collides with add term
			'capabilities' => array(
				'manage_terms' => 'manage_wikispace',
				'edit_terms' => 'edit_wikispace',
				'delete_terms' => 'delete_wikispace',
				'assign_terms' => 'assign_wikispace',
			),
		];
		register_taxonomy("wikispace", ["wikipage"], $args);
	}

	function add_custom_capabilities() {
		// Administrator
		$admin = get_role('administrator');
		$admin->add_cap('read_wikipage');
		$admin->add_cap('edit_wikipage');
		$admin->add_cap('delete_wikipage');
		$admin->add_cap('edit_wikipages');
		$admin->add_cap('edit_others_wikipages');
		$admin->add_cap('delete_wikipages');
		$admin->add_cap('publish_wikipages');
		$admin->add_cap('read_private_wikipages');
		$admin->add_cap('read');
		$admin->add_cap('delete_private_wikipages');
		$admin->add_cap('delete_published_wikipages');
		$admin->add_cap('delete_others_wikipages');
		$admin->add_cap('edit_private_wikipages');
		$admin->add_cap('edit_published_wikipages');
		$admin->add_cap('edit_wikipages');

		$admin->add_cap('manage_wikispace');
		$admin->add_cap('edit_wikispace');
		$admin->add_cap('delete_wikispace');
		$admin->add_cap('assign_wikispace');

		// Editor
		$editor = get_role('editor');
		$editor->add_cap('read_wikipage');
		$editor->add_cap('edit_wikipage');
		$editor->add_cap('delete_wikipage');
		$editor->add_cap('edit_wikipages');
		$editor->add_cap('edit_others_wikipages');
		$editor->add_cap('delete_wikipages');
		$editor->add_cap('publish_wikipages');
		$editor->add_cap('read_private_wikipages');
		$editor->add_cap('read');
		$editor->add_cap('delete_private_wikipages');
		$editor->add_cap('delete_published_wikipages');
		$editor->add_cap('delete_others_wikipages');
		$editor->add_cap('edit_private_wikipages');
		$editor->add_cap('edit_published_wikipages');
		$editor->add_cap('edit_wikipages');

		$editor->add_cap('manage_wikispace');
		$editor->add_cap('edit_wikispace');
		$editor->add_cap('delete_wikispace');
		$editor->add_cap('assign_wikispace');
	}

	/**
	 * Registers meta fields and REST fields for the 'wikipage' post type.
	 *
	 * @return void
	 */
	public function alter_wikipage_endpoint_response() {

		register_meta(
			'wikipage',
			'width',
			array(
				'type' => 'string',
				'single' => true,
				'show_in_rest' => true,
			)
		);

		// register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
		register_rest_field(
			'wikipage',
			'isOverview',
			array(
				'get_callback'    => function ($wikipage) {
					return get_post_meta($wikipage['id'], 'isOverview', true) ? true : false;
				},
				'schema'          => null,
			)
		);
		register_rest_field(
			'wikipage',
			'width',
			array(
				'get_callback'    => function ($wikipage) {
					$width = get_post_meta($wikipage['id'], 'width', true);
					return  $width ? $width : 'standard';
				},
				'schema'          => null,
			)
		);

		register_rest_field(
			'wikipage',
			'raw_title',
			array(
				'get_callback'    => function ($wikipage) {
					return $wikipage;
				},
				'schema'          => null,
			)
		);
		register_rest_field(
			'wikipage',
			'wikispace',
			array(
				'get_callback'    => function ($wikipage) {
					return ['id' => $wikipage['wikispaces'][0] ?? 0, 'name' => get_term($wikipage['wikispaces'][0] ?? 0)->name ?? ''];
				},
				'schema'          => null,
			)
		);


		register_rest_field(
			'wikipage',
			'author',
			array(
				'get_callback'    => function ($wikipage) {
					$author_name = get_the_author_meta('display_name', $wikipage['author']);
					return ['id' => $wikipage['author'], 'name' => $author_name];
				},
				'schema'          => null,
			)
		);
	}


	/**
	 * Update post meta for CUSTOM_POST_TYPE.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/rest_insert_this-post_type/
	 * @see https://developer.wordpress.org/reference/functions/update_post_meta/
	 */
	public function wikipage_meta_update(\WP_Post $post, \WP_REST_Request $request, bool $creating): void {
		$metas = $request->get_param('meta');
		if (is_array($metas)) {
			foreach ($metas as $meta_name => $meta_value) {
				update_post_meta($post->ID, $meta_name, $meta_value);
			}
		}
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
}
