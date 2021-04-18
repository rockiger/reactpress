<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://rockiger.com
 * @since      1.0.0
 *
 * @package    Reactpress
 * @subpackage Reactpress/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php
$apps = $this->get_apps();
$environment_message = $this->environment_message();
?>
<div class="rp-content">
  <div class='head'>
    <div class='head--inner align-center flex m0auto maxWidth80 p2 pb1 pt1'>
      <?php echo REPR_LOGO ?>
      <h1 style="color: #82878C;">ReactPress</h1>
    </div>
  </div>

  <div class="maxWidth80 m0auto p2">
    <h2 class="mb075">React Apps</h2>
    <div class="flex gap row">
      <div class="col flex grow1 half">
        <div id="existing-apps" class="flex flexwrap gap row">
          <?php foreach ($apps as $app) :
            $appname = esc_attr($app['appname']);
            $pageslug = esc_attr($app['pageslug']);
          ?>
            <?php include(plugin_dir_path(__FILE__) . 'reactpress-admin-app.php'); ?>
          <?php endforeach; ?>
        </div>
        <p class="pt1">You can find <b>all app sources</b> in your WordPress plugin folder under:<code><?php echo REPR_PLUGIN_PATH ?>apps/[appname]</code>.</p>
        <p class="pt1"><b>For deployments</b> to work, make sure, that you <b>upload the build folder</b> of your React app into the app directory and that you have the <b>same folder structure</b> in your dev and live wordpress installation.</p>
      </div>
      <div class=" flex col half">
        <?php if ($environment_message) : ?>
          <div class="notice notice-info inline m0 mb1">
            <p>
              Currently you are in <b>Manual Mode</b>, (this means, you can create React apps only in your terminal) because:</p>
            <ul class="disc pl2">
              <?php echo $environment_message; ?>
            </ul>
          </div>
        <?php endif; ?>
        <div id="existing-apps" class="flex flexwrap gap row">
          <div class="card fullwidth p2">
            <h2>Create new React app</h2>
            <form id="rp-create-form" method="post" action="javascript:void(0)">
              <fieldset id="rp-create-fieldset">
                <input hidden name="action" value="CREATE_NEW_APP" />
                <table class="form-table" role="presentation">
                  <tbody>
                    <tr>
                      <th scope="row">App Name</th>
                      <td>
                        <input id="rp-appname" name="app_name" placeholder="e.g. my-email-app" required type="text" />
                        <p class="description">The name of your React app. Must be one word, lowercase and unique.</p>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Page Slug</th>
                      <td>
                        <input id="rp-pageslug" name="page_slug" placeholder="e.g. inbox" required type="text" />
                        <p class="description">The slug of page where your app should be displayed. Must be unique.</p>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Type</th>
                      <td>
                        <div class="mb025">
                          <label>
                            <input type="radio" name="type" value="development" required <?php echo $environment_message ? 'disabled' : '' ?> />
                            <span>Develop a new app (<?php echo $environment_message ? '<b>Doesn\' work in Deployment Mode</b>' : 'Usually on a local machine'; ?>).</span>
                          </label>
                        </div>
                        <div class="mb025">
                          <label>
                            <input type="radio" name="type" value="deployment" required <?php echo $environment_message ? 'checked' : ''; ?> />
                            <span>Deploy an already build app (Usually on a server).</span>
                          </label>
                        </div>
                        <p class="description">If you want to deploy an app, you must choose the same name and slug as on your development version.</p>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Template</th>
                      <td>
                        <select name="template" id="rp-template-select">
                          <option selected="selected" value="">Default</option>
                          <option value="typescript">TypeScript</option>
                          <option value="redux">Redux</option>
                          <option value="cra-template-rb">React Boilerplate</option>
                        </select>
                        <p class="description">The create-react-app template you want to choose.</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div>
                  <input type="submit" name="submit" id="submit" class="button button-primary" value="Create React App" />
                  <span id="rp-spinner" class="crpw-button-spinner spinner"></span>
                </div>
              </fieldset>
            </form>
          </div>
          <?php // include_once(plugin_dir_path(__FILE__) . 'reactpress-admin-advanced_settings.php'); 
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="rp-snackbar" class="rp-snackbar">Test</div>
<pre>
  <?php
  //print_r(REPR_PLUGIN_PATH);
  //var_dump($this->get_apps());
  //print_r(get_option('repr_apps'));

  ?>