# ReactPress

Easily create, build and deploy React apps into your existing WordPress sites.

Get started in seconds and develop your React app with instant feedback and your WordPress theme in mind.

Combine the flexibility of WordPress with the UI capabilities of React and seamlessly integrate create-react-app into your WordPress project for your next SaaS.

ReactPress does 3 things:

- It integrates your local dev server into your WordPress theme, that you have instant feedback, how your React app looks in the context of your WordPress website.
- It builds your React app in a way that it is usable from your WordPress site.
- It makes it easy to upload your app to a live server after building.

## TODO

- [ ] v1.2.0 Revamp the process of adding using reactpress.
      -- [x] Don't start the react app any more, only update the index.html from admin
      -- [x] Make it possible to add apps manually with npm or yarn
- [x] v1.2.0 Add fallback if we can't find the plugin directory programmatically.
- [ ] v1.x.0 Add the ability to create admin pages
- [ ] v1.x.0 Provide a mechanism to log in on the dev server, to have a realistic dev flow. Could be some mocking or documentation.
- [ ] v1.x.0 validate onchange form input
- [ ] v1.x.0 Swap file_get_contents for wp_remote_get.
- [ ] v1.x.0 Check if servers are running every 60 seconds and on focus
- [ ] v1.x.0 Check if windows version can be implemented
