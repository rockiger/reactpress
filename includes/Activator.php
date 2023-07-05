<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Fulcrum
 * @subpackage Fulcrum/includes
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace Fulcrum\Includes;

use Fulcrum\Admin\Utils;
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
		wp_mkdir_p(FULC_APPS_PATH);
		$fulc_version_option = get_option('fulc_version');
		$fulc_apps = is_array(get_option('fulc_apps')) ?  get_option('fulc_apps') : [];

		Utils::write_apps_option(
			Activator::activate_helper(
				$fulc_apps,
				$fulc_version_option,
				fn (string $page_slug) => Activator::get_page_by_slug($page_slug)
			)
		);
		//# update version
		update_option('fulc_version', FULC_VERSION);
	}

	/**
	 * 
	 * @param string $fulc_version_option 
	 * @param array<array-key, mixed> $fulc_apps 
	 * @param callable $get_page_by_slug
	 * @return array<array-key, mixed>
	 */
	public static function activate_helper($fulc_apps, $fulc_version_option, $get_page_by_slug) {
		if ($fulc_version_option && $fulc_version_option < '3.0.0') {
			//# check if fulc_apps have the right format, otherwise update

			//# transform pageslug to pageslugs
			$fulc_apps_with_pageslugs = array_map(function ($el) {
				if (array_key_exists('pageslug', $el)) {
					$new_el = $el;
					$pageslug = $el['pageslug'];
					$new_el['pageslugs'] = [$pageslug];
					unset($new_el['pageslug']);
					return $new_el;
				} else {
					return $el;
				}
			}, $fulc_apps);

			//# swap out pageslugs for pageIds, because they are immutable
			$fulc_apps_with_page_ids = array_map(function ($el) use ($get_page_by_slug) {
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
			}, $fulc_apps_with_pageslugs);


			//# add flag for app routing
			$fulc_apps_with_app_routing = array_map(function ($el) {
				if (!array_key_exists('allowsRouting', $el)) {
					$new_el = $el;
					$new_el['allowsRouting'] = true;
					return $new_el;
				} else {
					return $el;
				}
			}, $fulc_apps_with_page_ids);

			return $fulc_apps_with_app_routing;
		}
		return $fulc_apps;
	}

	/**
	 * 
	 * @param string $page_slug 
	 * @param string|object[] $post_type
	 * @return WP_Post|null 
	 */
	public static function get_page_by_slug($page_slug, $post_type = 'page') {
		global $wpdb;

		if (is_array($post_type)) {
			$post_type = esc_sql($post_type);
			$post_type_in_string = "'" . implode("','", $post_type) . "'";
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_name = %s
				AND post_type IN ($post_type_in_string)
			", $page_slug);
		} else {
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_name = %s
				AND post_type = %s
			", $page_slug, $post_type);
		}

		$page = $wpdb->get_var($sql);

		if ($page)
			return get_post($page);

		return null;
	}
}
