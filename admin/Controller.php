<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Reactpress
 * @subpackage Reactpress/admin
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace ReactPress\Admin;

use LengthException;
use ReactPress\Admin\Utils;

class Controller {

  public static function delete_url_slug(array $app_options_list, string $appname, string $pageslug) {
    try {
      Utils::delete_pageslug($app_options_list, $appname, $pageslug);
      echo wp_json_encode(['status' => 1]);
    } catch (\Exception $e) {
      repr_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }

  public static function toggle_react_routing(string $appname) {
    try {
      $app_options = is_array(get_option('repr_apps')) ?  get_option('repr_apps') : [];

      $new_options = array_map(function ($el) use ($appname) {
        if ($el['appname'] === $appname) {
          repr_log($el);
          //# create new allowsRouting state
          $allowsRouting = $el['allowsRouting'] ?? false;
          $el['allowsRouting'] = !$allowsRouting;

          //# change rewrite rules in wordpress
          if ($el['allowsRouting']) {
            // routing is only allowed for one pageslug
            if (count($el['pageslugs']) > 1) {
              throw new LengthException('Client-side routing is only allowed for apps with one page slug.');
            }
            foreach ($el['pageslugs'] as $pageslug) {
              add_rewrite_rule('^' . $pageslug . '/(.*)?', 'index.php?pagename=' . $pageslug, 'top');
            }
            flush_rewrite_rules();
          } else {
            foreach ($el['pageslugs'] as $pageslug) {
              Utils::remove_rewrite_rule('^' . $pageslug . '/(.*)?');
            }
            flush_rewrite_rules();
          }
          return $el;
        }
        return $el;
      }, $app_options);

      update_option('repr_apps', $new_options);
      echo wp_json_encode(['status' => 1]);
    } catch (\Exception $e) {
      repr_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }
}
