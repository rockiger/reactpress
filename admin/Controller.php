<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fulcrum
 * @subpackage Fulcrum/admin
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace Fulcrum\Admin;

use LengthException;
use Fulcrum\Admin\Utils;

class Controller {

  // # Controller functions 

  public static function add_page(string $appname, int $pageId, string $page_title) {
    $app_options = Utils::get_app_options($appname);
    $apptype = Utils::get_app_type($appname);
    //# Check if the app allows adding of more URL slugs
    if ($app_options && $app_options['allowsRouting'] && count($app_options['pageIds'])) {
      echo wp_json_encode([
        'status' => 0,
        'message' => 'Apps with client-side routing can only be shown on one single page.'
      ]);
      return;
    }

    // add slug to existing app_options
    $inserted_page = Controller::insert_page($pageId, $page_title);
    fulc_log(['$inserted_page' => $inserted_page]);
    if (!$inserted_page['ID']) {
      echo wp_json_encode([
        'status' => 0,
        'message' => $inserted_page['message']
      ]);
      return;
    }
    $permalink = get_permalink($inserted_page['ID']);
    $permalink = $permalink ? $permalink : '';
    $app_options ? Utils::add_pageId_to_app_options($appname, $inserted_page['ID']) : Utils::add_app_options($appname, $inserted_page['ID']);

    //# Check if the default space and the default page should be created
    $terms = get_terms('fulcrum-spaces');
    $posts = get_posts([
      'post_type' => 'fulcrum-page',
      'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash']
    ]);

    fulc_log(['before fulcrum check' => 'before fulcrum check', 'terms' => count($terms), 'posts' => count($posts), 'cond' => !count($terms) && count($posts)]);
    if (!count($terms) && !count($posts)) {
      fulc_log('inside fulcrum check');
      $default_term = wp_create_term('Default', 'fulcrum-spaces');
      fulc_log(['$default_term' => $default_term]);
      wp_insert_post([
        'post_title' => 'Overview',
        'post_type' => 'fulcrum-page',
        'post_status' => 'publish',
        'post_content' => DEFAULT_OVERVIEW_CONTENT,
        'tax_input'    => [
          'fulcrum-spaces' => [$default_term['term_id']],
        ],
        'meta_input'   => [
          'isOverview' => true,
          'width' => 'standard'
        ],
      ]);
    }

    echo wp_json_encode([
      'status' => 1,
      'message' => 'Page added.',
      'pageId' => $inserted_page['ID'],
      'page_title' => $inserted_page['page_title'],
      'permalink' => $permalink
    ]);
  }

  public static function delete_page(string $appname, int $pageId, string $permalink) {
    try {
      $app_options_list = Utils::get_apps();
      Utils::delete_page($app_options_list, $appname, $pageId);
      Utils::unset_public_url_for_dev_server($appname, $permalink);
      echo wp_json_encode(['status' => 1]);
    } catch (\Exception $e) {
      fulc_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }

  public static function delete_react_app(string $appname) {
    $options = get_option('fulc_apps');
    Utils::write_apps_option(array_filter(
      $options,
      fn ($el) => $el['appname'] !== $appname
    ));
    $is_appdir_removed = fulc_delete_directory(Utils::app_path($appname));
    if ($is_appdir_removed) {
      echo wp_json_encode([
        'status' => 1,
        'message' => 'App deleted.',
      ]);
    } else {
      echo wp_json_encode([
        'status' => 1,
        'message' => "Couldn't remove files. Please remove directory by hand.",
      ]);
    }
  }

  public static function get_react_apps() {
    $apps = Utils::get_apps();
    echo wp_json_encode(['status' => 1, 'apps' => $apps]);
  }

  public static function update_index_html(string $appname, string $permalink) {
    $apptype = Utils::get_app_type($appname);
    $html_content = Controller::get_index_html_content($permalink, $apptype, $appname);
    if (empty($html_content)) {
      echo wp_json_encode([
        'status' => 0,
        'message' => 'Couldn\'t download page content.'
      ]);
    } elseif (Controller::write_index_html($appname, $html_content, $apptype)) {
      echo wp_json_encode([
        'status' => 1,
        'message' => 'Index.html updated.',
      ]);
    } else {
      echo wp_json_encode([
        'status' => 0,
        'message' => 'Index.html could not be updated.',
      ]);
    }
  }

  // # Helper functions

  /**
   * Add the right build path to package.json
   *
   * @param string $appname
   * @return int 0 if no success
   * @since 1.2.0
   */
  public static function add_build_path($appname, $apptype = 'development_cra') {
    $apppath = Utils::app_path($appname);
    // We need the relative path, that we can deploy our
    // built app to another server later.
    $relative_apppath = Utils::app_path($appname, true);
    $relative_apppath = $relative_apppath ? $relative_apppath : "/wp-content/fulcrum/apps/{$appname}/";
    if ($apptype === 'development_vite') {
      $homepage = "{$relative_apppath}/dist/";
      $path_vite_config = is_file("{$apppath}/vite.config.js") ? "{$apppath}/vite.config.js" : "{$apppath}/vite.config.ts";
      $vite_config_contents = file_get_contents($path_vite_config);
      if (!$vite_config_contents) {
        return 0;
      } elseif (stripos($vite_config_contents, $homepage)) {
        return 1;
      } else {
        // add the base pathe to vite.config.*
        file_put_contents(
          $path_vite_config,
          str_replace(
            "export default defineConfig({\n  plugins: [react()],\n})",
            "export default defineConfig(({ command }) => {\n  if (command === 'build') {\n    return {\n      base: \"{$homepage}\",\n      plugins: [react()],\n    }\n  } else {\n    return {\n      plugins: [react()],\n    }\n  }\n})",
            $vite_config_contents
          )
        );
        return 2;
      }
    } else {
      $homepage = "{$relative_apppath}/build";
      $path_package_json = "{$apppath}/package.json";
      $package_json_contents = file_get_contents($path_package_json);
      if (!$package_json_contents) {
        return 0;
      } elseif (stripos($package_json_contents, $homepage)) {
        return 1;
      } else {
        // add the base path that images are correctly loaded
        file_put_contents(
          $path_package_json,
          str_replace("react-scripts build", IS_WINDOWS ? "set PUBLIC_URL={$homepage}&&react-scripts build" : "PUBLIC_URL={$homepage} react-scripts build", $package_json_contents)
        );
        return 2;
      }
    }
  }

  /**
   * Downloads the content of the page with the given permalink and removes the
   * the react assets of the build page, that we can use the content for our
   * development server.
   *
   * @param $permalink
   * @return string
   * @since 1.0.0
   */
  public static function get_index_html_content(string $permalink, $apptype = 'development_cra', $appname = '') {
    $file_contents = wp_remote_retrieve_body(
      wp_remote_get($permalink, ['timeout' => 1000])
    );
    $file_contents_arr = explode(PHP_EOL, $file_contents);
    // filter all build assets out of the file, that they don't conflict
    // with the dev assets.
    $filtered_arr = array_filter($file_contents_arr, fn ($el) => !strpos($el, "id='rp-react-app-asset-"));
    $filtered_contents =  implode(PHP_EOL, $filtered_arr);
    // re-add script tag for global fulcrum variable
    $readded_contents = str_replace('var fulcrum', "<script>\nvar fulcrum", $filtered_contents);

    if ($apptype === 'development_vite') {
      $apppath = Utils::app_path($appname);
      $file_ending = is_file("{$apppath}/src/main.jsx") ? 'jsx' : 'tsx';
      // add script tag after root div that link to src/main.tsx
      $readded_contents = str_replace(
        '<div id="root"></div>',
        "<div id=\"root\"></div><script type=\"module\" src=\"/src/main.{$file_ending}\"></script>",
        $readded_contents
      );
    }

    return $readded_contents;
  }

  /**
   * Creates or updates a page with the given name and title.
   *
   * @param int $pageId
   * @param string $page_title
   * @since 1.0.0
   */
  public static function insert_page(int $pageId, string $page_title) {
    if ($pageId === -1) {
      $result = wp_insert_post(
        array(
          'post_title' => $page_title,
          'post_status' => 'publish',
          'post_content' => FULC_REACT_ROOT_TAG,
          'post_type' => "page",
          // Assign page template using the relative path, it will be
          // resolved to the fully qualified name at run-time
          'page_template'  => 'templates/empty-react-page-template.php',
        )
      );
      return $result
        ? ['status' => 'true', 'message' => 'Page created.', 'ID' => $result, 'page_title' => $page_title]
        : ['status' => 'false', 'message' => "Couldn't create page.", "ID" => $result, 'page_title' => $page_title];
    } else {
      $page = get_post($pageId);
      if ($page) {
        // already we have data with this post name
        if (strpos($page->post_content, '<div id="root"></div>') !== false) {
          return ['status' => 'true', 'message' => 'Page with app already exists.', 'ID' => $page->ID, 'page_title' => $page_title];
        }
        $result = wp_update_post(
          [
            'ID' => $page->ID,
            'post_content' => $page->post_content . "\n\n" . REPR_REACT_ROOT_TAG
          ]
        );
        return $result
          ? ['status' => 'true', 'message' => 'Add app to page.', 'ID' => $result, 'page_title' => $page->post_title]
          : ['status' => 'false', 'message' => 'Couldn\'t add app to page.', 'ID' => 0, 'page_title' => ''];
      }
    }
  }

  public static function toggle_react_routing(string $appname) {
    try {
      $app_options = Utils::get_apps();

      $new_options = array_map(function ($el) use ($appname) {
        if ($el['appname'] === $appname) {
          //# create new allowsRouting state
          $allowsRouting = $el['allowsRouting'] ?? false;
          $el['allowsRouting'] = !$allowsRouting;

          //# change rewrite rules in wordpress
          if ($el['allowsRouting']) {
            // routing is only allowed for one single page
            if (count($el['pages']) > 1) {
              throw new LengthException('Client-side routing is only possible on one single page.');
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
      fulc_log($e);
      echo wp_json_encode(['status' => 0, 'message' => $e->getMessage()]);
    }
  }

  /**
   * Writes the given to the index.html file in the app directory of
   * the given appname and produces if the writing of the file succeded or not.
   *
   * @param string $appname
   * @param string $content
   * @since 1.0.0
   */
  public static function write_index_html(string $appname, string $content, $apptype = 'development_cra') {
    $index_html_path =
      $apptype === 'development_vite'
      ? sprintf("%s/%s/index.html", FULC_APPS_PATH, $appname)
      : sprintf("%s/%s/public/index.html", FULC_APPS_PATH, $appname);

    return file_put_contents($index_html_path, $content);
  }
}

define('DEFAULT_OVERVIEW_CONTENT', $str = '<h2>Welcome to your wiki</h2>
This is <strong>your </strong><a target="_blank" rel="noopener noreferrer nofollow" href="https://en.wikipedia.org/wiki/Wiki">wiki</a>, where you can do whatever you want.

You can use it to:
<ul>
 	<li>Develop ideas</li>
 	<li>Use it as a notebook</li>
 	<li>Collect links, files and data</li>
 	<li>Post code snippets you find useful</li>
</ul>
These are just <strong>suggestions</strong>. Do whatever <strong>you want</strong>. It is <strong>your</strong> wiki.
<h2>Some tips to get you started</h2>
<ul>
 	<li><strong>Edit this page.</strong> Click on the <strong>edit icon</strong> or press <strong>e</strong> on your keyboard, the editor will open. You can insert files from your Google Drive by using the Drive-button in the menu bar.</li>
 	<li><strong>Insert a new page.</strong> Click on the <strong>New Page</strong> button on the top left to create a <strong>new page below the current page</strong>. If you want to <strong>create a page somewhere else</strong> in your wiki, <strong>hover over an entry</strong> in the page tree and <strong>click</strong> the <strong>+</strong>-button.</li>
 	<li><strong>Search your wiki.</strong> Click into the <strong>search box</strong> or press <strong>/</strong> on your keyboard to search your wiki. The search box will also show your last visited wiki pages.</li>
 	<li>You can <strong>insert files</strong> from your Google Drive <strong>into your wiki</strong> pages by using the <strong>Drive-button</strong> in the menu bar.</li>
 	<li>If you <strong>need more wikis</strong>, add them <strong>from your Google Drive</strong> using the <strong>New-Button.</strong></li>
</ul>
<h2>Editor features and shortcuts</h2>
<table>
<tbody>
<tr>
<td colspan="1" rowspan="1">Feature</td>
<td colspan="1" rowspan="1">Shortcut</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Start editing</td>
<td colspan="1" rowspan="1">e</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Save and close editor</td>
<td colspan="1" rowspan="1">Ctrl+Enter / ⌘+Enter</td>
</tr>
<tr>
<td colspan="1" rowspan="1"></td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle heading style [1-6]</td>
<td colspan="1" rowspan="1">Ctrl+Alt+[1-6] / ⌘+Alt+[1-6]</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle numbered list</td>
<td colspan="1" rowspan="1">Ctrl+Shift+7 / ⌘+Shift+7</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle bulleted list</td>
<td colspan="1" rowspan="1">Ctrl+Shift+8 / ⌘+Shift+8</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle code block</td>
<td colspan="1" rowspan="1">Ctrl+Shift+0 / ⌘+Shift+0</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Choose syntax highlighting for code block</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1"></td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle quote</td>
<td colspan="1" rowspan="1">Ctrl+Shift+9 / ⌘+Shift+9</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle bold text</td>
<td colspan="1" rowspan="1">Ctrl+b / ⌘+b</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle italic text</td>
<td colspan="1" rowspan="1">Ctrl+i / ⌘+i</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle underlined text</td>
<td colspan="1" rowspan="1">Ctrl+u / ⌘+u</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle strikethrough text</td>
<td colspan="1" rowspan="1">Ctrl+~ / ⌘+~</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle monospaced text</td>
<td colspan="1" rowspan="1">Ctrl+` / ⌘+`</td>
</tr>
<tr>
<td colspan="1" rowspan="1"></td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Insert link</td>
<td colspan="1" rowspan="1">Ctrl+k / ⌘+k</td>
</tr>
<tr>
<td colspan="1" rowspan="1">Insert image from Google Drive</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Drag image from your computer</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Insert a link to an item in your Google Drive</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1"></td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Insert table</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Delete table</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Add column</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Remove column</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Add row</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Remove row</td>
<td colspan="1" rowspan="1"></td>
</tr>
<tr>
<td colspan="1" rowspan="1">Toggle table header</td>
<td colspan="1" rowspan="1"></td>
</tr>
</tbody>
</table>
<h2>Markdown shortcuts</h2>
If you know markdown you can use it as a shortcut to structure your content. You simply type the markdown marker and hit space. Fulcrum supports the following markers:
<h1># Header 1</h1>
<h2>## Header 2</h2>
<h3>### Header 3</h3>
<h4>#### Header 4</h4>
<h5>##### Header 5</h5>
<h6>###### Header 6</h6>
<ul>
 	<li>* bullet point</li>
</ul>
&gt; quote+-9*
+');
