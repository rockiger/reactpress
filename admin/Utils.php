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
   * Creates the path the app, beginning from root of filesystem
   * or htdocs.
   *
   * @param string $appname
   * @param boolean $relative_to_home_path
   * @return string
   * @since 1.0.0
   */
  public static function app_path(string $appname, $relative_to_home_path = false): string {
    $apppath = escapeshellcmd(REPR_APPS_PATH . "/{$appname}");
    $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($relative_to_home_path) {
      return explode($document_root, $apppath)[1];
    } else {
      return $apppath;
    }
  }

  /**
   * Add the PUBLIC_URL to start command of package.json of React app. 
   * This is neccessary that the dev server is working as exspected if 
   * client-side routing
   * is used.
   * @param string $appname
   * @param string $pageslug
   * @return void 
   */
  public static function set_public_url_for_dev_server(string $appname, string $pageslug) {
    $apppath = Utils::app_path($appname);
    // We need the relative path, that we can deploy our
    // build app to another server later.
    $relative_apppath = Utils::app_path($appname, true);
    $relative_apppath = $relative_apppath ? $relative_apppath : "/wp-content/reactpress/apps/{$appname}/";
    $path_package_json = "{$apppath}/package.json";
    $package_json_contents = file_get_contents($path_package_json);
    $search = "\"react-scripts start\"";
    $replace = "\"PUBLIC_URL=/{$pageslug} react-scripts start\"";
    if (!$package_json_contents) {
      return 0;
    } elseif (stripos($package_json_contents, $replace)) {
      return 1;
    } else {
      file_put_contents(
        $path_package_json,
        str_replace($search, $replace, $package_json_contents)
      );
      return 2;
    }
    return 0;
  }

  /**
   * Remove the PUBLIC_URL to start command of package.json of React app. * This is neccessary that the dev server is working as exspected if 
   * client-side routing
   * is used.
   * @param string $appname
   * @param string $pageslug
   * @return void 
   */
  public static function unset_public_url_for_dev_server(string $appname, string $pageslug) {
    $apppath = Utils::app_path($appname);
    // We need the relative path, that we can deploy our
    // build app to another server later.
    $relative_apppath = Utils::app_path($appname, true);
    $relative_apppath = $relative_apppath ? $relative_apppath : "/wp-content/reactpress/apps/{$appname}/";
    $path_package_json = "{$apppath}/package.json";
    $package_json_contents = file_get_contents($path_package_json);
    $replace = "\"react-scripts start\"";
    $search = "\"PUBLIC_URL=/{$pageslug} react-scripts start\"";
    if (!$package_json_contents) {
      return 0;
    } else {
      file_put_contents(
        $path_package_json,
        str_replace($search, $replace, $package_json_contents)
      );
      return 2;
    }
    return 0;
  }

  /**
   * Delete an app slug from an app. Returns the new options list
   * @param array $app_options_list
   * @param string $appname 
   * @param string $pageslug 
   * @return array
   * @since 2.0.0
   */
  public static function delete_pageslug(array $app_options_list, string $appname, string $pageslug) {
    $new_app_options_list = array_map(function ($app_option) use ($appname, $pageslug) {
      if ($app_option['appname'] === $appname) {
        $app_option['pageslugs'] = array_filter(
          $app_option['pageslugs'],
          fn ($ps) => $ps !== $pageslug
        );
      }
      return $app_option;
    }, $app_options_list);
    update_option('repr_apps', $new_app_options_list);
    return $new_app_options_list;
  }

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
   * @since 2.0.0
   */
  public static function remove_rewrite_rule(string $regex) {
    global $wp_rewrite;
    unset($wp_rewrite->extra_rules_top[$regex]);
  }
}
