VERSION_NODE=0.128.0
VERSION_WEB=1.4.0

basepath="/public_html/"

srcs=()

# usilon-mobileWeb
#files+=("upsilon-mobileWeb/bin/upsilon-mobileWeb.apk releases/apk/");

# upsilon-web
files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-${VERSION_WEB}.tar.gz releases/upsilon-web-tgz/");
files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-${VERSION_WEB}.zip releases/upsilon-web-tgz/");

# upsilon-node
#files+=("upsilon-node/upsilon-node-rpm-fedora18/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION_NODE}-1.fc18.noarch.rpm releases/rpm-fedora18/");
#files+=("upsilon-node/upsilon-node-rpm-generic/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION_NODE}-1.generic.noarch.rpm releases/rpm-generic/");
#files+=("upsilon-node/upsilon-node-rpm-rhel6/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION_NODE}-1.el6.noarch.rpm releases/rpm-rhel6/");

#files+=("upsilon-node/upsilon-node-deb/target/upsilon-node-${VERSION_NODE}.deb releases/deb/");

#files+=("upsilon-node/upsilon-node-tgz/target/upsilon-node-${VERSION_NODE}.tar.gz releases/tgz/");
#files+=("upsilon-node/upsilon-node-tgz/target/upsilon-node-${VERSION_NODE}.zip releases/tgz/");

for file in "${files[@]}"; do
	src=`echo "$file" | awk '{print $1}'`
	dst=`echo "$file" | awk '{print $2}'`

	if [ ! -f "$src" ]; then
		echo "Build does not look complete, missing file."
		echo "Filename: $src" 
		exit;
	fi
done

echo "Will upload ${#files[@]} file(s)."
echo

for file in "${files[@]}"; do
	src=`echo "$file" | awk '{print $1}'`
	dst=`echo "$file" | awk '{print $2}'`

	echo "File: $src -> $basepath/$dst"
	echo "put $src" | sftp -b - upsilon-project.co.uk:$basepath/$dst
	echo "-"
done
