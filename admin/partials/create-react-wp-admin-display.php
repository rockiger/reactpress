<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Create_React_Wp
 * @subpackage Create_React_Wp/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php $crwp_apps = get_option('crwp_apps'); ?>
<div class="crwp-content">
  <div class='head'>
    <div class='head--inner align-center flex m0auto maxWidth80 p2 pb1 pt1'>
      <?= LOGO ?>
      <h1 style="color: #82878C;">Create React WP</h1>
    </div>
  </div>

  <div class="maxWidth80 m0auto p2">
    <h2 class="mb075">React Apps</h2>
    <div class="flex gap row">
      <div class="col flex grow1 half">
        <div id="existing-apps" class="flex flexwrap gap row">
          <?php foreach ($crwp_apps as $app) :
            $is_running = $this->is_react_app_running($app['appname']);
            [$protocol, $ip, $port] = $is_running ? $this->get_app_uri($this->app_path($app['appname']), 1) : ['', '', ''];
          ?>
            <div id="<?= $app['appname'] ?>" class="card col flex half m0 p1_5">
              <h3 class="title flex m0 mb075 row"><?= REACT_ICON_SVG ?><?= $app['appname'] ?></h3>
              <div class="grow1 mb1">
                <p><b>URL Slug: </b><a href="<?= $app['pageslug'] ?>"><?= $app['pageslug'] ?></a></p>
                <p><b>Status:</b> <b id="status-<?= $app['appname'] ?>" class=" fg-<?= $is_running ? 'green' : 'red' ?>"><?= $is_running ? "Running at port: <a href=\"{$protocol}://{$ip}:{$port}\" rel=\"noopener\" target=\"_blank\">{$port}<i class=\"external-link\"></i></a>" : 'Stopped' ?></b>
              </div>
              <div class=" flex">
                <button class="button button-primary button-start-stop" data-appname="<?= $app['appname'] ?>" data-pageslug="<?= $app['pageslug'] ?>"><?= $is_running ? 'Stop' : 'Start' ?></button>
                <span id="crwp-start-spinner-<?= $app['appname'] ?>" class="crpw-button-spinner spinner"></span>
                <div class="grow1"></div>
                <span id="crwp-build-spinner-<?= $app['appname'] ?>" class="crpw-button-spinner spinner"></span>
                <button class="button button-build" data-appname="<?= $app['appname'] ?>" data-pageslug="<?= $app['pageslug'] ?>">Build</button>
                <button class="button-link button-delete ml025" data-appname="<?= $app['appname'] ?>" data-pageslug="<?= $app['pageslug'] ?>">Delete</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="pt1">You can find all app sources in your WordPress plugin folder under:<code>./create-react-wp/apps/[appname]</code></p>
      </div>
      <div class=" flex col half">
        <div class="card fullwidth p2">
          <h2>Create new React app.</h2>
          <form id="crwp-create-form" method="post" action="javascript:void(0)">
            <fieldset id="crwp-create-fieldset">
              <input hidden name="action" value="CREATE_NEW_APP" />
              <table class="form-table" role="presentation">
                <tbody>
                  <tr>
                    <th scope="row"><label for="appname">App Name</label></th>
                    <td>
                      <input id="crwp-appname" name="app_name" placeholder="e.g. my-email-app" required type="text" />
                      <p class="description" id="tagline-description">The name of your React app. Must be one word, lowercase and unique.</p>
                    </td>
                  </tr>
                  <tr>
                    <th scope="row"><label for="page_slug">Page Slug</label></th>
                    <td>
                      <input id="crwp-pageslug" name="page_slug" placeholder="e.g. inbox" required type="text" />
                      <p class="description" id="tagline-description">The slug of page where your app should be displayed. Must be unique.</p>
                    </td>
                  </tr>
                  <tr>
                    <th scope="row"><label for="template">Page Slug</label></th>
                    <td>
                      <select name="template" id="crwp-template-select">
                        <option selected="selected" value="">Default</option>
                        <option value="typescript">TypeScript</option>
                        <option value="redux">Redux</option>
                        <option value="cra-template-rb">React Boilerplate</option>
                      </select>
                      <p class="description" id="tagline-description">The create-react-app you want to choose.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Create React App" />
                <span id="crwp-spinner" class="crpw-button-spinner spinner"></span>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="crwp-snackbar" class="crwp-snackbar">Test</div>
<pre>
  <?php
  //delete_option('crwp_apps');
  // print_r($this->get_app_uri($this->app_path('app1'), 1));

  ?>