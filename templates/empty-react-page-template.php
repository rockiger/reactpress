<?php /* Template Name: EmptyReactPageTemplate */

if (!defined('ABSPATH')) exit; // Exit if accessed directly    

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <style>
    body {
      font-family: Inter, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", Segoe UI Symbol, "Noto Color Emoji" !important;
    }

    #wpadminbar {
      display: none !important;
    }
  </style>
  <meta charset="utf-8" />
  <link rel="icon" href="/favicon.ico" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#000000" />
  <meta name="description" content="Web site created using create-react-app" />

  <link rel="manifest" href="/<?php echo esc_html($post->post_name); ?>/manifest.json" />
  <?php wp_head() ?>
  ?>


</head>

<body>
  <noscript>You need to enable JavaScript to run this app.</noscript>

  <?php the_content(); ?>
  <!--
      This HTML file is a template.
      If you open it directly in the browser, you will see an empty page.

      You can add webfonts, meta tags, or analytics to this file.
      The build step will place the bundled scripts into the <body> tag.

      To begin the development, run `npm start` or `yarn start`.
      To create a production bundle, use `npm run build` or `yarn build`.
    -->
  <?php wp_footer(); ?>
</body>

</html>