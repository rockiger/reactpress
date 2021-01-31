=== ReactPress - Create React App for Wordpress ===
Contributors: rockiger
Donate link: https://rockiger.com
Tags: react, embed, developer, javascript, js
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create, build and deploy React apps into your existing WordPress sites. 

== Description ==

Easily create, build and deploy React apps into your existing WordPress sites.

Get started in seconds and develop your React app with instant feedback and your WordPress theme in mind.

Combine the flexibility of WordPress with the UI capabilities of React and seamlessly integrate create-react-app into your WordPress project for your next SaaS.

[youtube www.youtube.com/watch?v=pVi07A_OZYA ]

ReactPress does 3 things:
* It integrates your local dev server into your WordPress theme, that you have instant feedback, how your React app looks in the context of your WordPress website.
* It builds your React app in a way that it is usable from your WordPress site.
* It makes it easy to upload your app to a live server after building.

=== System Requirements ===

To develop React app your WordPress installations needs access to:

* the PHP function `shell_exec` and `exec`,

* the nodejs package manager `npm` version 6 or higher

* and a POSIX compatible system ([Windows users can use WSL2](https://rockiger.com/en/windows-survival-guide-to-for-react-and-web-developers/ "Windows Survival Guide for React and Web Developers")).

=== Usage ===

To create and deploy your first app:

1. Click on *Create React App* in the sidebar of your WordPress admin.

2. Fill out the *Create new React form*, choose "Develop a new app." as type.

3. Click on *Start* and open the link with the port number.

4. Develop your app, changes will automatically hot reloaded.

5. When you are finished, build the app. You can find it on development WordPress installation on [your-domain]/[your-slug]. 

6. To deploy create the same app on your live server. This time choose "Deploy an already build app." for the type. Make sure you use the same name for the app - otherwise the app won't work as expected.

7. Upload the build folder from your dev system under `plugins/wp-create/react-app/apps/[your-app-name]` to the same directory onto your live server.

8. Open the React app under [your-domain]/[your-slug].

Repeat steps 5 to 8 when you have new releases you want to deploy.


== Installation ==


1. Like any other plugin install via *Plugins/Add New*. You can download the plugin via admin or upload it to the plugins directory.

2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==


= The app won't show in the page. =

Have you build the React app and, in case of a live server, uploaded the build folder to the right location?

= It shows I am in development mode, but I am on a local server. =

Has your WordPress/PHP installation access to `npm`. If you use a docker container like Local, you need to install node in that container or use a non virtualized dev server like Bitnami WordPress Installer.


== Screenshots ==


1. Create a new React app for development called *reactino*.

2. The new React app is created and running.

3. The local React dev server is running on port: 3000. Every change will hot reload immediately.

4. Create a new React app for deployment on the server.

5. The new React app is created, but no dev server is running.

6. The React app is deployed on the public server.

== Changelog ==

= 1.0 =

* Check for if it allows `shell_exec` and `exec`

* `npm -v >= 6.0.0` is reachable from WordPress

* Find out if we are in Windows environment

* Deploy app to production

* Add TypeScript/template support

* Delete app

* Build app

* Extend index.html in React app to look like WordPress site

* Create new React app

* Add React app in specified page