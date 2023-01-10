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
   * Creates options for a new app. If the repr_apps options are not present, it will
   * create them.
   */

  //! refactor to not need the $app_options_list
  public static function add_app_options(string $appname, int $pageId) {
    $app_options_list = Utils::get_apps();
    if (!is_array($app_options_list) && $appname && $pageId) {
      add_option('repr_apps', [[
        'allowsRouting' => false,
        'appname' => $appname,
        'pageIds' => [$pageId],
      ]]);
    } elseif ($appname && $pageId) {
      Utils::write_apps_option(Utils::array_add($app_options_list, [
        'allowsRouting' => false,
        'appname' => $appname,
        'pageIds' => [$pageId],
      ]));
    }
  }

  public static function add_pageId_to_app_options(string $appname, int $pageId) {
    Utils::write_apps_option(array_map(function ($el) use ($appname, $pageId) {
      if ($el['appname'] === $appname) {
        $el['pageIds'] = array_unique(Utils::array_add($el['pageIds'], $pageId));
      }
      return $el;
    }, Utils::get_apps()));
  }

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
   * Helper function to add an element to an array
   * without mutationg the original array.
   *
   * @param array $array
   * @param [type] $entry
   * @return void
   * @since 1.0.0
   */
  public static function array_add(array $array, $entry) {
    return array_merge($array, [$entry]);
  }

  /**
   * Get the option for the given app name.
   * @param string $appname 
   * @return mixed
   */
  //! refactor to not need the $app_options_list
  public static function get_app_options(array $app_options_list, string $appname) {
    $app_options = null;
    foreach ($app_options_list as $key => $val) {
      if ($val['appname'] === $appname) {
        $app_options = $val;
      }
    }
    return $app_options;
  }

  /**
   * Add the PUBLIC_URL to start command of package.json of React app. 
   * This is neccessary that the dev server is working as exspected if 
   * client-side routing
   * is used.
   * @param string $appname
   * @param string $permalink
   * @return void 
   */
  public static function set_public_url_for_dev_server(string $appname, string $permalink) {
    $apppath = Utils::app_path($appname);
    // We need the relative path, that we can deploy our
    // build app to another server later.
    $relative_apppath = Utils::app_path($appname, true);
    $relative_apppath = $relative_apppath ? $relative_apppath : "/wp-content/reactpress/apps/{$appname}/";
    $relative_link = wp_make_link_relative($permalink);
    $path_package_json = "{$apppath}/package.json";
    $package_json_contents = file_get_contents($path_package_json);
    $search = "\"react-scripts start\"";
    $replace = IS_WINDOWS ? "\"set PUBLIC_URL={$relative_link}&&react-scripts build\"" : "\"PUBLIC_URL=/{$relative_link} react-scripts start\"";
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
   * @param string $permalink
   * @return void 
   */
  public static function unset_public_url_for_dev_server(string $appname, string $permalink) {
    $apppath = Utils::app_path($appname);
    // We need the relative path, that we can deploy our
    // build app to another server later.
    $relative_apppath = Utils::app_path($appname, true);
    $relative_apppath = $relative_apppath ? $relative_apppath : "/wp-content/reactpress/apps/{$appname}/";
    $relative_link = wp_make_link_relative($permalink);
    $path_package_json = "{$apppath}/package.json";
    $package_json_contents = file_get_contents($path_package_json);
    $replace = "\"react-scripts start\"";
    $search = IS_WINDOWS ? "\"set PUBLIC_URL={$relative_link}&&react-scripts build\"" : "\"PUBLIC_URL=/{$relative_link} react-scripts start\"";
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
   * @param int $pageId 
   * @return array
   * @since 2.0.0
   */
  public static function delete_page(array $app_options_list, string $appname, int $pageId) {
    $new_app_options_list = array_map(function ($app_options) use ($appname, $pageId) {
      if ($app_options['appname'] === $appname) {
        $new_app_options = $app_options;
        $new_app_options['pageIds'] = array_filter(
          $app_options['pageIds'],
          fn ($id) => $id !== $pageId
        );
        $new_app_options['pages'] = array_filter(
          $app_options['pages'],
          fn ($p) => $p['ID'] !== $pageId
        );
        return $new_app_options;
      }
      return $app_options;
    }, $app_options_list);
    Utils::write_apps_option($new_app_options_list);
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
   * Return all apps as an array, enriched with the meta data for pages
   * 
   * [['allowsRouting' => false,
   *   'appname' => $appname,
   *   'pageIds' => [100]
   *   'pages' => ['ID' => 100, 'title' => 'Title', 'permalink' => 'http://...']
   * ]]
   *
   * @return array
   * @since 1.2.0
   */
  public static function get_apps() {
    $app_options = Utils::get_app_options_list();

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
        'pageIds' => $app_option['pageIds'] ?? [],
        'type' => $type
      ];
    }, $appnames);

    //# Enrich the apps with page data
    $apps_enriched = array_map(function ($app) {
      $newApp = $app;
      $newApp['pages'] = array_map(function ($id) {
        $p = get_post($id);
        return [
          'ID' => $p->ID ?? 0,
          'permalink' => get_permalink($p),
          'title' => $p->post_title ?? '',
        ];
      }, $app['pageIds']);
      return $newApp;
    }, $apps);
    return $apps_enriched;
  }

  /**
   * Retrieves the repr_apps option from WordPress if nothing can retrieved,
   * produces an empty array. 
   * Usually you should prefer Utils::get_apps().
   */
  public static function get_app_options_list(): array {
    return is_array(get_option('repr_apps')) ?  get_option('repr_apps') : [];
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

  /**
   * Consumes an app list, filters unneccessary information (pages) and
   * saves it as options.
   * @param array $app_list 
   * @return void 
   */
  public static function write_apps_option($app_list) {
    $app_list_option = array_map(fn ($el) => [
      'allowsRouting' => $el['allowsRouting'],
      'appname' => $el['appname'],
      'pageIds' => $el['pageIds']
    ], $app_list);
    update_option('repr_apps', $app_list_option);
  }
}
