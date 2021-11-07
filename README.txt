=== ReactPress - Create React App for Wordpress ===
Contributors: rockiger
Tags: react, embed, developer, javascript, js
Requires at least: 5.0
Tested up to: 5.8.1
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create, build and deploy React apps into your existing WordPress sites. 

== Description ==

Easily create, build and deploy React apps into your existing WordPress sites.

Get started in seconds and develop your React app with instant feedback and your WordPress theme in mind.

Combine the flexibility of WordPress with the UI capabilities of React and seamlessly integrate create-react-app into your WordPress project for your next SaaS.


ReactPress does 3 things:

* It integrates your local dev server into your WordPress theme, that you have instant feedback, how your React app looks in the context of your WordPress website.
* It builds your React app in a way that it is usable from your WordPress site.
* It makes it easy to upload your app to a live server after building.

=== System Requirements ===

To develop React apps your WordPress instance needs access to:

* the PHP function `shell_exec` and `exec`,

* and a POSIX compatible system ([Windows users can use WSL2](https://rockiger.com/en/windows-survival-guide-to-for-react-and-web-developers/ "Windows Survival Guide for React and Web Developers")).

Optionally, to create React apps directly from the WordPress admin it needs also:

* the nodejs package manager `npm` version 6 or higher

=== Development ===

Active development of this plugin is handled [on GitHub](https://github.com/rockiger/reactpress/).

=== Documentation ===

If you need more information than the following, you can have a look at the [Documentation page](https://rockiger.com/en/easily-embed-react-apps-into-wordpress-with-reactpress-plugin/).

To create and deploy your first app:

1. Install ReactPress on your local WordPress installation.

2. In your command line use npx create-react-app [your-app-name] in the apps directory of ReactPress, e.g. `[path-to-WordPress]/wp-content/reactpress/apps/my-app`

3. Reload the ReactPress admin page and add a URL Slug for your app.

4. In the command line start the React app with `npm start` or `yarn start`.

5. Develop your app, changes will automatically hot reloaded.

6. When you are finished, build the app from the command line. You can now see your app embedded in your WordPress instance. Open it at [your-domain]/[your-slug].

7. To deploy, Install ReactPress on live WordPress site.

8. Upload the build folder from your dev system under `.../wp-content/reactpress/apps/[your-app-name]` to the same directory onto your live server. No need for create-react-app.

9. Reload the ReactPress admin page and add a URL Slug for your app.

10. Open the React app under [your-domain]/[your-slug].

Repeat steps 6 to 10 when you have new releases you want to deploy.


== Installation ==


1. Like any other plugin install via *Plugins/Add New*. You can download the plugin via admin or upload it to the plugins directory.

2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

Release 1.3.0+ does change to way to use ReactPress and gives you much more control. It should solve a lot of issues with using ReactPress in local development environments like Local, DevKinsta, and similar.

Nonetheless, backward compatibility should be given.

== Frequently Asked Questions ==

= How do I make react-router work =

To make sure that react-router works you have to go to your WP permalink setting and click on save chang after every react app you install. This will rebuild the internal router of your WP installation.

== Screenshots ==


1. Create a new React app for development called *reactino*.

2. The new React app is created and running.

3. The local React dev server is running on port: 3000. Every change will hot reload immediately.

4. Create a new React app for deployment on the server.

5. The new React app is created, but no dev server is running.

6. The React app is deployed on the public server.

== Changelog ==

= 1.3.1 = 

* Swap file_get_contents for wp_remote_get.

* Create custom routing for react-router based on slug of the reactpress page.

* If the folder of an app is deleted, it is shown as type: Orphan

= 1.3.0 = 

* Move apps directory to wp-content/reactpress/apps to don't mess with the created app when updating the plugin.

* Remove possibility to create new react apps from the admin. From now on there is only the command line workflow.

= 1.2.1 =

* FIX: Template incompatibility with Elementor and some other plugins. Thank to the great answer of Sally CJ https://stackoverflow.com/questions/67696139/error-in-wordpress-with-plugin-reactpress/68455647#answer-67751220

* FIX: Problems if document root and plugin app directory are on the same machine/server/locationn

= 1.2.0 = 

* Revamp the process of adding using ReactPress. Don't start the react app anymore, only update the `index.html` from WordPress admin. Make it possible to add apps manually with npm or yarn.

* Add fallback if we can't find the plugin directory programmatically.

= 1.1.0 =

* Test with WordPress 5.7

* Insert the current user object to the global window object in Javascript, to have it accessible without a call to the API.

* add .env with CHOKIDAR_USEPOLLING=true to ensure watcher works with VM

* Use npm instead of yarn.

= 1.0.0 =

* Check for if it allows `shell_exec` and `exec`

* `npm -v >= 6.0.0` is reachable from WordPress

* Find out if we are in a Windows environment

* Deploy app to production

* Add TypeScript/template support

* Delete app

* Build app

* Extend index.html in React app to look like WordPress site

* Create new React app

* Add React app in specified page
