echo $2
VERSION=$2

basepath="public_html"

srcs=()

# usilon-mobileWeb

case "$1" in 
	--node)
		files+=("upsilon-node/upsilon-node-rpm-fedora18/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION}-1.fc18.noarch.rpm releases/rpm-fedora18/");
		files+=("upsilon-node/upsilon-node-rpm-generic/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION}-1.generic.noarch.rpm releases/rpm-generic/");
		files+=("upsilon-node/upsilon-node-rpm-rhel6/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION}-1.el6.noarch.rpm releases/rpm-rhel6/");

		files+=("upsilon-node/upsilon-node-deb/target/upsilon-node-${VERSION}.deb releases/deb/");

		files+=("upsilon-node/upsilon-node-tgz/target/upsilon-node-${VERSION}.tar.gz releases/tgz/");
		files+=("upsilon-node/upsilon-node-tgz/target/upsilon-node-${VERSION}.zip releases/tgz/");
		;;
	--web)
		files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-${VERSION}.tar.gz releases/upsilon-web-tgz/");
		files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-${VERSION}.zip releases/upsilon-web-tgz/");
		;;
	--mobileWeb)
		files+=("upsilon-mobileWeb/bin/upsilon-mobileWeb.apk releases/apk/");
		;;
esac

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
