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

  public static function delete_page(array $app_options_list, string $appname, int $pageId, string $permalink) {
    try {
      Utils::delete_page($app_options_list, $appname, $pageId);
      Utils::unset_public_url_for_dev_server($appname, $permalink);
      echo wp_json_encode(['status' => 1]);
    } catch (\Exception $e) {
      repr_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }

  public static function toggle_react_routing(string $appname) {
    try {
      $apps = Utils::get_apps();
      $app_options = is_array($apps) ?  $apps : [];

      $new_options = array_map(function ($el) use ($appname) {
        if ($el['appname'] === $appname) {
          //# create new allowsRouting state
          $allowsRouting = $el['allowsRouting'] ?? false;
          $el['allowsRouting'] = !$allowsRouting;

          //# change rewrite rules in wordpress
          if ($el['allowsRouting']) {
            // routing is only allowed for one single page
            if (count($el['pages']) > 1) {
              throw new LengthException('Client-side routing is only allowed for apps with one page slug.');
            }
            foreach ($el['pages'] as $page) {
              add_rewrite_rule('^' . wp_make_link_relative($page['permalink']) . '/(.*)?', 'index.php?pagename=' . wp_make_link_relative($page['permalink']), 'top');
              Utils::set_public_url_for_dev_server($appname, $page['permalink']);
            }
            flush_rewrite_rules();
          } else {
            foreach ($el['pages'] as $page) {
              Utils::remove_rewrite_rule('^' . wp_make_link_relative($page['permalink']) . '/(.*)?');
              Utils::unset_public_url_for_dev_server($appname, $page['permalink']);
            }
            flush_rewrite_rules();
          }
          return $el;
        }
        return $el;
      }, $app_options);

      Utils::write_apps_option($new_options);
      echo wp_json_encode(['status' => 1]);
    } catch (\Exception $e) {
      repr_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }
}
