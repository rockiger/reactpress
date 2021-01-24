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
<div class="wrap">
  <h1>Create React WP</h1>
  <div>
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
        <p class="submit">
          <input type="submit" name="submit" id="submit" class="button button-primary" value="Create React App" />
          <span id="crwp-spinner" class="spinner" style="float: none;margin-top: 5px; vertical-align: top;"></span>
        </p>
      </fieldset>
    </form>
  </div>
  <?php // TODO show current react apps 
  ?>
</div>
<pre>
  <?php
  //delete_option('crwp_apps');
  $crwp_apps = get_option('crwp_apps');
  print_r($crwp_apps);
  ?>