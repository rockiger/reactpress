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
$environment_message = '';
$types = ['deployment' => 'Production', 'development' => 'Development', 'empty' => 'Empty Folder - It seems no build folder was added.', 'orphan' => 'Orphan - It seems the app folder was deleted.']
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
        <?php if (empty($apps)) : ?>
          <p class="pb1">It seems you don't have any React apps created. Go to <code><?php echo REPR_APPS_PATH ?></code> in your command line and enter:</p>
          <p class="pb1"><code>npx create-react-app [appname]</code></p>
          <p class="pb1">Insert a page slug and start developing your app with <code>yarn start</code>.</p>
          <p class="pb1">To deploy your React app, install ReactPress on your live system, build the app with <code>yarn build</code> and upload only the build folder to <code>wp-content/reactpress/[appname]</code> on your live system.</p>
          <p class="pb1">Then reload the ReactPress page in the WordpPress admin and give it the exact same slug as on the dev system.</p>
          <p class="pb1">If you visit the slug now, you should see the app on your live system.</p>
        <?php endif; ?>
        <div id="existing-apps" class="flex flexwrap gap row">
          <?php foreach ($apps as $app) :
            $appname = esc_attr($app['appname']);
            $pageslug = esc_attr($app['pageslug']);
          ?>
            <?php include(plugin_dir_path(__FILE__) . 'reactpress-admin-app.php'); ?>
          <?php endforeach; ?>
        </div>
        <p class="pt1">You can find <b>all app sources</b> in your WordPress plugin folder under:<code><?php echo REPR_APPS_PATH ?>/[appname]</code>.</p>
        <p class="pt1"><b>For deployments</b> to work, make sure, that you <b>upload the build folder</b> of your React app into the app directory and that you have the <b>same folder structure</b> in your dev and live wordpress installation.</p>
      </div>
      <div class=" flex col half">
        <?php if ($environment_message) : ?>
          <div class="notice notice-info inline m0 mb1">
            <p>
              Currently you are in <b>Manual Mode</b>, (this means, you can create React apps only in your command line) because:</p>
            <ul class="disc pl2">
              <?php echo $environment_message; ?>
            </ul>
          </div>
        <?php endif; ?>
        <div id="existing-apps" class="flex flexwrap gap row">

          <?php // include_once(plugin_dir_path(__FILE__) . 'reactpress-admin-advanced_settings.php'); 
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="root"></div>
<div id="rp-snackbar" class="rp-snackbar">Test</div>
<pre>
  <?php
  //print_r(REPR_PLUGIN_PATH);
  //var_dump($this->get_apps());
  //print_r(get_option('repr_apps'));

  ?>