# Note: edit src/main/dojo/profile.js
rm -rf target
mkdir target
./var/dojo-release/util/buildscripts/build.sh profile=src/main/dojo/profile.js
#cp target/dojo-upsilon/dojo/dojo.js src/main/php/resources/dojo/dojo/dojo.js
cp -r target/dojo-upsilon/* src/main/php/resources/dojo/

