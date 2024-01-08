version='0.0.1'
 
# We don't want a ton of dev dependencies in our release.
composer install --no-dev --optimize-autoloader
rm -r /tmp/fulcrum-svn
mkdir -p /tmp/fulcrum-svn/tags/$version
cp -r ./ /tmp/fulcrum-svn/tags/$version/
rm /tmp/fulcrum-svn/tags/$version/.gitignore
rm -rf /tmp/fulcrum-svn/tags/$version/.git
rm -r /tmp/fulcrum-svn/tags/$version/.vscode
rm -r /tmp/fulcrum-svn/tags/$version/assets
# rm /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/*
rm /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/.gitignore
rm -r /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/node_modules
# rm -r /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/src
# rm -r /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/public
rm -rf /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/.git
rm /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/public/index.html
rm /tmp/fulcrum-svn/tags/$version/admin/js/reactpress-admin/postbuild.sh
#//! repeat wiki-app
#//...
# rm /tmp/fulcrum-svn/tags/$version/apps/wp-wiki/*
rm /tmp/fulcrum-svn/tags/$version/apps/wp-wiki/.gitignore
rm -r /tmp/fulcrum-svn/tags/$version/apps/wp-wiki/node_modules
# rm -r /tmp/fulcrum-svn/tags/$version/apps/wp-wiki/src
rm -rf /tmp/fulcrum-svn/tags/$version/apps/wp-wiki/.git

cp -r /tmp/fulcrum-svn/tags/$version/ /tmp/fulcrum-svn/
# Reinstall dev dependencies to be able to work on Fulcrum again
mv /tmp/fulcrum-svn/$version /tmp/fulcrum-svn/trunk
composer install