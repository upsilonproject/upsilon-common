basepath="upsilon-project.co.uk:public_html/releases/"

srcs=()
files+=("upsilon-mobileWeb/bin/foo.apk apk/");
files+=("upsilon-web/upsilon-web-tgz/target/upsilon-web-1.2.0.tar.gz upsilon-web-tgz/");

for file in "${files[@]}"; do
	src=`echo "$file" | awk '{print $1}'`
	dst=`echo "$file" | awk '{print $2}'`

	echo $src;
	echo $dst;
	if [ ! -f "$src" ]; then
		echo "Build does not look complete, missing file."
		echo "Filename: $src" 
		exit;
	fi
done

echo "Will upload ${#files[@]} file(s)."


for file in "${files[@]}"; do
	src=`echo "$file" | awk '{print $1}'`
	dst=`echo "$file" | awk '{print $2}'`

	echo "put $file" | sftp "$basepath/$dst" 
done
