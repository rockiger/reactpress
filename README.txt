=== ReactPress - Create React App for Wordpress ===
Contributors: rockiger
Tags: react, embed, developer, javascript, js
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create, build and deploy React apps into your existing WordPress sites. 

== Description ==

Easily create, build and deploy React apps into your existing WordPress sites.

Get started in seconds and develop your React app with instant feedback and your WordPress theme in mind.

Combine the flexibility of WordPress with the UI capabilities of React and seamlessly integrate create-react-app into your WordPress project for your next SaaS.

[youtube www.youtube.com/watch?v=uHqPbFLy-3Y]

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

=== Usage ===

To create and deploy your first app:

1. In your command line use npx create-react-app [your-app-name] in the apps directory of ReactPress, e.g. `[path-to-WordPress]/wp-content/plugins/reactpress/apps/my-app`

2. Reload the ReactPress admin page and add a URL Slug for your app.

3. In the command line start the React app with `npm start` or `yarn start`.

4. Develop your app, changes will automatically hot reloaded.

5. When you are finished, build the app from the command line. You can now see your app embedded in your WordPress instance. Open it at [your-domain]/[your-slug].

6. To deploy create the same app on your live server. Choose "Deploy an already build app." for the type. Make sure you use the same name for the app - otherwise the app won't work as expected.

7. Upload the build folder from your dev system under `plugins/wp-create/react-app/apps/[your-app-name]` to the same directory onto your live server.

8. Open the React app under [your-domain]/[your-slug].

Repeat steps 5 to 8 when you have new releases you want to deploy.


== Installation ==


1. Like any other plugin install via *Plugins/Add New*. You can download the plugin via admin or upload it to the plugins directory.

2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

This release does change to way to use ReactPress and gives you much more control. It should solve a lot of issues with using ReactPress in local development environments like Local, DevKinsta, and similar.

Nonetheless, backward compatibility should be given.

== Frequently Asked Questions ==


= The app won't show on the page. =

Have you build the React app and, in case of a live server, uploaded the build folder to the right location?

= It shows I am in deployment mode, but I am on a local server. =

Has your WordPress/PHP installation access to `npm`. If you use a docker container like Local, then you are probably not. [We provide a VirtualBox that is made to work with ReactPress.](https://rockiger.com/en/reactpress-dev-environment/) 

= I am a Windows user and can't use WSL-2. Is there a way to use ReactPress? =

If you have no chance using a POSIX compatible system, you can use ReactPress if you do 2 things after Step 2:

1. Change the build command in your `package.json` from `"build react-scripts build"` to `"PUBLIC_URL=/wp-content/plugins/reactpress/apps/[my-app]/build react-scripts build"`. Make sure that relative pathe to the build directory above is correct.

2. To have the styling of your WordPress site when developing your React app, go to the URL slug. Save the HTML as `index.html` into the `public` folder of your React app. Remove all tags that have an ID that starts with `id='rp-react-app-asset-`. Save it and you should see your dev server in the same style as your WordPress site.


== Screenshots ==


1. Create a new React app for development called *reactino*.

2. The new React app is created and running.

3. The local React dev server is running on port: 3000. Every change will hot reload immediately.

4. Create a new React app for deployment on the server.

5. The new React app is created, but no dev server is running.

6. The React app is deployed on the public server.

== Changelog ==

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
