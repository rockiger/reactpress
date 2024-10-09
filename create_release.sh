version='3.3.0'
 
# We don't want a ton of dev dependencies in our release.
composer install --no-dev --optimize-autoloader
rm -r /tmp/reactpress-svn
mkdir -p /tmp/reactpress-svn/tags/$version
cp -r ./ /tmp/reactpress-svn/tags/$version/
rm /tmp/reactpress-svn/tags/$version/.gitignore
rm -rf /tmp/reactpress-svn/tags/$version/.git
rm -r /tmp/reactpress-svn/tags/$version/.vscode
rm -r /tmp/reactpress-svn/tags/$version/assets
rm /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/*
rm /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/.gitignore
rm -r /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/node_modules
rm -r /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/src
rm -r /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/public
rm -rf /tmp/reactpress-svn/tags/$version/admin/js/reactpress-admin/.git
rm -r /tmp/reactpress-svn/trunk
cp -r /tmp/reactpress-svn/tags/$version/ /tmp/reactpress-svn/
# Reinstall dev dependencies to be able to work on ReactPress again
mv /tmp/reactpress-svn/$version /tmp/reactpress-svn/trunk
composer install