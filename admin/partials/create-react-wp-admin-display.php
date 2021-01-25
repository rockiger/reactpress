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
    <div class='head--inner align-center flex maxWidth80 p2 pb1 pt1'>
      <?= LOGO ?>
      <h1 style="color: #82878C;">Create React WP</h1>
    </div>
  </div>

  <div class="maxWidth80 m0auto p2">
    <h2 class="mb075">React Apps</h2>
    <div class="flex gap row">
      <div class="col flex grow1 half">
        <div class="flex flexwrap gap row">
          <?php foreach ($crwp_apps as $app) : ?>
            <div class="card col flex half m0 p1_5">
              <h3 class="title flex m0 mb075 row"><?= REACT_ICON_SVG ?><?= $app['appname'] ?></h3>
              <p class="grow1 mb1"><b>URL Slug: </b><?= $app['pageslug'] ?></p>
              <div class="flex">
                <button class="button">Start</button>
                <div class="grow1"></div>
                <button class="button">Stop</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="flex col half">
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
                      <p class="description" id="tagline-description">The name of your React app. Must be unique.</p>
                    </td>
                  </tr>
                  <tr>
                    <th scope="row"><label for="page_slug">Page Slug</label></th>
                    <td>
                      <input id="crwp-pageslug" name=" page_slug" placeholder="e.g. inbox" required type="text" />
                      <p class="description" id="tagline-description">The slug of page where your app should be displayed. Must be unique.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Create React App" />
                <span id="crwp-spinner" class="spinner" style="float: none;margin-top: 5px; vertical-align: top;"></span>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<pre>
  <?php
  //delete_option('crwp_apps');
  print_r($crwp_apps);
  ?>