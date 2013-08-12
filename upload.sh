basepath="/public_html/releases"

VERSION_NODE=0.115.0

srcs=()
files+=("upsilon-mobileWeb/bin/upsilon-mobileWeb.apk apk/");
files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-1.2.0.tar.gz upsilon-web-tgz/");
files+=("upsilon-node/upsilon-rpm-generic/target/rpm/upsilon-node/RPMS/noarch/upsilon-node-${VERSION_NODE}-generic.noarch.rpm rpm-generic/");
files+=("upsilon-node/upsilon-deb/target/upsilon-node-${VERSION_NODE}.deb deb/");

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
