<?php

/**
 * Utility functions for the admin. All created in as static methods.

 * @package Reactpress
 * @subpackage Reactpress/admin
 * @author Marco Laspe <marco@rockiger.com>
 */

namespace ReactPress\Admin;


class Utils {

  /**
   * Get all folders in the apps directory and return them as an array
   *
   * @return array
   * @since 1.2.0
   */
  public static function get_app_names() {
    // check im reactpress directory exists if not create it
    wp_mkdir_p(REPR_APPS_PATH);
    chdir(REPR_APPS_PATH);
    $appnames = scandir(REPR_APPS_PATH);
    return array_values(array_filter($appnames, fn ($el) => $el[0] !== '.' && is_dir($el)));
  }

  /**
   * Return all apps as an array
   *
   * @return array
   * @since 1.2.0
   */
  public static function get_apps() {
    $app_options = is_array(get_option('repr_apps')) ?  get_option('repr_apps') : [];


    // combine apps from directory and from settings to get a complete list
    // event when the user deletes an app from the directory
    $appnames_from_opts = array_map(fn ($el) => $el['appname'], $app_options);
    $appnames_from_dir = Utils::get_app_names();
    $appnames = array_unique(array_merge($appnames_from_opts, $appnames_from_dir));

    $apps = array_map(function ($el) use ($app_options) {
      $app_option = array_reduce(
        $app_options ? $app_options : [],
        fn ($carry, $item) =>
        $item['appname'] === $el ? $item : $carry,
        []
      );
      $type = '';
      if (is_file(REPR_APPS_PATH . '/' . $el . '/package.json')) {
        $type = 'development';
      } elseif (is_dir(REPR_APPS_PATH . '/' . $el . '/build')) {
        $type = 'deployment';
      } elseif (is_dir(REPR_APPS_PATH . '/' . $el)) {
        $type = 'empty';
      } else {
        $type = 'orphan';
      }
      return [
        'allowsRouting' => $app_option['allowsRouting'] ?? false,
        'appname' => $el,
        'pageslugs' => $app_option['pageslugs'] ?? [],
        'type' => $type
      ];
    }, $appnames);
    return $apps;
  }

  /**
   * Removes a rewrite rule from $wp_rewrite->extra_rules_top
   *
   * @param $regex the regex given to add_rewrite_rule
   */
  public static function remove_rewrite_rule(string $regex) {
    global $wp_rewrite;
    repr_log(array_keys($wp_rewrite->extra_rules_top));
    unset($wp_rewrite->extra_rules_top[$regex]);
    repr_log(array_keys($wp_rewrite->extra_rules_top));
  }
}
