=== Fulcrum Wiki ===
Contributors: rockiger
Tags: wiki, knowledge management, confluence, intranet, react, single page application
Requires at least: 5.0
Tested up to: 6.4.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Capture knowledge. Find information faster. Share your ideas with others. Save projects, meeting notes and marketing plans right in your WordPress installation.

== Description ==

=== Your knowledge management should make you look smart. Like an assistant that always has your back. ===

Have you felt frustrated by your note-taking app?

* Did you endlessly look for a note you have written?
* Created time-consuming documents nobody looked at again?
* Never came back to that great idea you wrote down?
* Were you anxious for not hosting your notes on your own servers?

=== Manage your knowledge. Have your records always ready. ===

==== Capture Knowledge ====
Easily create pages with your knowledge. Projects, Meeting notes, marketing plans - everything saved in your WordPress installation.

==== Find information faster ====
Organize your records like your personal Wikipedia. Link, group and tag your content or use the search to find all your records fast.

==== Share Your Work ====
Did you create some awesome piece of content? Share it with others, that they can enjoy your hard work.

=== Build great looking wiki pages ===
Create pages with all content formats you need. Tables, Images, Lists - you name it. Write a new marketing plan, document your workflow for employee onboarding or write a memo for your co-workers.

The best is: Everything stays in your WordPress. Unlike other tools, we don\'t introduce a new SaaS infrastructure to your business. All the content you create is saved in your WordPress. Nothing is saved elsewhere.

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
3. Install, then Activate it.
4. Go to the Fulcrum-Menu, decide on which page to add your wiki.

=== Third-Party JavaScript Library ===

This plugin makes extensive use of third-party open-source JavaScript libraries. To reduce download size this libraries are only included in minified form. Both the admin-interface and the frontend page are React single page applications. All libraries are available at https://www.npmjs.com/.

The whole list of libraries used in the admin interface can be found in it's `package.json` file (see `admin/js/reactpress-admin/package.json` for more detailed information).

Respectively, the whole list of libraries used in the frontend can be found in it's `package.json` file  (see `apps/wiki/package.json` for more detailed information).

The sources of this plugin are seperated into two plugins:

- The plugin itself including the admin-interface: [https://github.com/rockiger/reactpress/tree/fulcrum-plugin](https://github.com/rockiger/reactpress/tree/fulcrum-plugin)
- The wiki single page application: [https://github.com/rockiger/wp-wiki](https://github.com/rockiger/wp-wiki)

==== Build instructions ====

To build this plugin yourself, you need to have [nodejs](https://nodejs.org/en), [composer](https://getcomposer.org/) and [git](https://git-scm.com/) installed. The build instructions are as follows:

`
git clone https://github.com/rockiger/reactpress/ fulcrum
cd fulcrum
git checkout fulcrum-plugin
composer install
cd admin/js/reactpress-admin
npm install
npm build         # npm start, if you want to start the dev server
cd ../../../apps
git clone https://github.com/rockiger/wp-wiki
npm install
npm build         # npm dev, if you want to start the dev server
`
