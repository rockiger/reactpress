<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Reactpress
 * @subpackage Reactpress/includes
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace ReactPress\Includes;

use ReactPress\Admin\Utils;
use WP_Post;

class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//# check im reactpress directory exists if not create it
		wp_mkdir_p(REPR_APPS_PATH);
		$repr_version_option = get_option('repr_version');
		$repr_apps = is_array(get_option('repr_apps')) ?  get_option('repr_apps') : [];

		Utils::write_apps_option(
			Activator::activate_helper(
				$repr_apps,
				$repr_version_option,
				fn (string $page_slug) => Activator::get_page_by_slug($page_slug)
			)
		);
		//# update version
		update_option('repr_version', REPR_VERSION);
	}

	/**
	 * 
	 * @param string $repr_version_option 
	 * @param array<array-key, mixed> $repr_apps 
	 * @param callable $get_page_by_slug
	 * @return array<array-key, mixed>
	 */
	public static function activate_helper($repr_apps, $repr_version_option, $get_page_by_slug) {
		if ($repr_version_option && $repr_version_option < '3.0.0') {
			//# check if repr_apps have the right format, otherwise update

			//# transform pageslug to pageslugs
			$repr_apps_with_pageslugs = array_map(function ($el) {
				if (array_key_exists('pageslug', $el)) {
					$new_el = $el;
					$pageslug = $el['pageslug'];
					$new_el['pageslugs'] = [$pageslug];
					unset($new_el['pageslug']);
					return $new_el;
				} else {
					return $el;
				}
			}, $repr_apps);

			//# swap out pageslugs for pageIds, because they are immutable
			$repr_apps_with_page_ids = array_map(function ($el) use ($get_page_by_slug) {
				if (array_key_exists('pageslugs', $el)) {
					$new_el = $el;
					$new_el['pageIds'] = array_map(function ($el) use ($get_page_by_slug) {
						$result = $get_page_by_slug($el);
						return $result ? $result->ID : null;
					}, $el['pageslugs']);
					unset($new_el['pageslugs']);
					return $new_el;
				} else {
					return $el;
				}
			}, $repr_apps_with_pageslugs);


			//# add flag for app routing
			$repr_apps_with_app_routing = array_map(function ($el) {
				if (!array_key_exists('allowsRouting', $el)) {
					$new_el = $el;
					$new_el['allowsRouting'] = true;
					return $new_el;
				} else {
					return $el;
				}
			}, $repr_apps_with_page_ids);

			return $repr_apps_with_app_routing;
		}
		return $repr_apps;
	}

	/**
	 * 
	 * @param string $page_slug 
	 * @param string|object[] $post_type
	 * @return WP_Post|null 
	 */
	public static function get_page_by_slug($page_slug, $post_type = 'page') {
		global $wpdb;
		$page = null;

		if (is_array($post_type)) {
			$post_type = esc_sql($post_type);
			// This constriuct is needed to secure against SQL injections
			// SQL "IN" keyword
			$post_type_placeholders = implode(', ', array_fill(0, count($post_type), '%s'));
			$prepare_values = array_merge([$page_slug], $post_type);
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_name = %s
				AND post_type IN ($post_type_placeholders)
			", $prepare_values);
			$page = $wpdb->get_var($sql);
		} else {
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_name = %s
				AND post_type = %s
			", $page_slug, $post_type);
			$page = $wpdb->get_var($sql);
		}

		$page = $wpdb->get_var($sql);

		if ($page)
			return get_post($page);

		return null;
	}
}
