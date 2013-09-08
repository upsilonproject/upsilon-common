# Note: edit src/main/dojo/profile.js
./setupLibraries.sh
rm -rf target/dojo-upsilon
mkdir -p src/main/php/resources/dojo/
mkdir -p target/dojo-upsilon/
echo "buildDojo.sh pwd: `pwd`"
./target/dojo-release/util/buildscripts/build.sh profile=src/main/dojo/profile.js
#cp target/dojo-upsilon/dojo/dojo.js src/main/php/resources/dojo/dojo/dojo.js
cp -r target/dojo-upsilon/* src/main/php/resources/dojo/

