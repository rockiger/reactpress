rename reactpress.php to fulcrum.php /
replace ReactPress with Fulcrum (namespaces, classes, etc) outside of ReactPress admin React app /
replace reactpress with fulcrum /
change apps directorty from wp-content/reactpress/apps to wp-content/plugin/fulcrum/apps /
move app from reactpress/apps zu plugins/fulcrum/apps /
change path in `index.html` /
replace ReactPress icon with Fulcrum icon (development app) /
remove app irrelevant infos from admin page /
/wp-content/reactpress/apps/wp-wiki => /wp-content/plugins/fulcrum/apps/wp-wiki /
repr => fulc
REPR\_ => FULC\_ /
add post types /
add actions for graphql /
require dependencies (wp-graphql, WPGraphQL Tax Query, LH Private Content Login) /
Create default space with overview page /
redirect to overview page /
fix errors in react app again /
create page /
Check that authentication works right /
Update Readme's /
Add versions /
Change release script
Make plugin /
Test plugin
Submit plugin

set page to private.

---

Ideas for ReactPress
Create plugin mode to remove unnecessary features

Known Issues:
When opening the disclosure the link activated => can't stop event propagation on Disclosure button, move to radix

New Stories:
Create a publishing workflow with draft, private, published
