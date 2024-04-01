=== Fulcrum Wiki ===
Contributors: rockiger
Tags: wiki, knowledge management, confluence, intranet, notion
Requires at least: 5.0
Tested up to: 6.4.3
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Capture knowledge. Find information faster. Share your ideas with others. Save projects, meeting notes and marketing plans in WordPress.

== Description ==

**Disclaimer: Fulcrum is still early-stage software. Please don't expect fully finished software. If you have any issues or feature requests, please post them in the support forum.**

Fulcrum aims to be a wiki system similar to Confluence or Notion for WordPress.

==== Capture Knowledge ====
Easily create pages with your knowledge. Projects, meeting notes, marketing plans - everything saved in WordPress.

==== Find information faster ====
Organize your records like your personal Wikipedia. Link, group and tag your content, or use the search to find all your records fast.

==== Share Your Work ====
Did you create some wonderful piece of content? Share it with others, that they can enjoy your hard work.

=== Build great-looking wiki pages ===
Create pages with all content formats you need. Tables, Images, Lists - you name it. Write a new marketing plan, document your workflow for employee onboarding, or write a memo for your co-workers.

The best is: Everything stays in WordPress. Unlike other tools, we don't introduce a new SaaS infrastructure to your business. All the content you create is saved in WordPress. Nothing is saved elsewhere.

==== Easily find & navigate your work ====
Always stay on top of your work. Organize your work in different wikis and create sub-pages of your work. Access your most recent and important work instantly.

Don\'t waste your time searching. Find what you are looking for with a powerful search.

=== 3 Steps to being effortlessly organized ===

1. Install the plugin
2. Decide on which page to display your wiki
3. Capture your knowledge

== Installation ==
1. Go to the WordPress Dashboard “Add New Plugin” section.
2. Search For “Fulcrum Wiki”.
3. Install, then activate it.
4. Go to the Fulcrum-Menu, decide on which page to add your wiki.

== Screenshots ==

1. Screenshot-1.png
2. Screenshot-2.png
3. Screenshot-3.png
4. Screenshot-4.png
5. Screenshot-5.png

== Third-Party JavaScript Library ==

This plugin makes extensive use of third-party open-source JavaScript libraries. To reduce download size this libraries are only included in minified form. Both the admin-interface and the frontend page are React single page applications. All libraries are available at https://www.npmjs.com/.

The whole list of libraries used in the admin interface can be found in it's `package.json` file (see `admin/js/reactpress-admin/package.json` for more detailed information).

Respectively, the whole list of libraries used in the frontend can be found in it's `package.json` file  (see `apps/wiki/package.json` for more detailed information).

The sources of this plugin are separated into two plugins:

- The plugin itself, including the admin-interface: [https://github.com/rockiger/reactpress/tree/fulcrum-plugin](https://github.com/rockiger/reactpress/tree/fulcrum-plugin)
- The wiki single page application: [https://github.com/rockiger/wp-wiki](https://github.com/rockiger/wp-wiki)

==== Build instructions ====

To build this plugin yourself, you need to have [nodejs](https://nodejs.org/en), [composer](https://getcomposer.org/) and [git](https://git-scm.com/) installed. The build instructions are as follows:

`
git clone https://github.com/rockiger/reactpress.git fulcrum
cd fulcrum
git checkout fulcrum-plugin
composer install
cd admin/js/reactpress-admin
npm install
npm build         # npm start, if you want to start the dev server
cd ../../../
mkdir apps
cd apps
git clone https://github.com/rockiger/wp-wiki.git
npm install
npm build         # npm dev, if you want to start the dev server
`
== Changelog ==

= 1.0.0 =

* Initial Release

= 1.0.1 =

* Remove "Update Dev-Environment" button in admin area
* Remove 'Private: ' in Title of pages and add a label 'private'
* Image upload from editor
* Directly open the editor when creating a new page
* Make the title editable
* Add localstorage based caching for improved performance
* Reload page to renew WordPress nonce after invalidation
