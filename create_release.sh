rm -r /tmp/reactpress-svn
mkdir -p /tmp/reactpress-svn/tags/x.x.x
cp -r ./ /tmp/reactpress-svn/tags/x.x.x/
rm /tmp/reactpress-svn/tags/x.x.x/.gitignore
rm -rf /tmp/reactpress-svn/tags/x.x.x/.git
rm -r /tmp/reactpress-svn/tags/x.x.x/.vscode
rm -r /tmp/reactpress-svn/tags/x.x.x/assets
rm /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/*
rm /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/.gitignore
rm -r /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/node_modules
rm -r /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/src
rm -r /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/public
rm -rf /tmp/reactpress-svn/tags/x.x.x/admin/js/reactpress-admin/.git
rm -r /tmp/reactpress-svn/trunk
cp -r /tmp/reactpress-svn/tags/x.x.x/ /tmp/reactpress-svn/
mv /tmp/reactpress-svn/x.x.x /tmp/reactpress-svn/trunk
