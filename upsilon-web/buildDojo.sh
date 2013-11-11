# Note: edit src/main/dojo/profile.js
./setupLibraries.sh
rm -rf target/dojo-upsilon
mkdir -p src/main/php/resources/dojo/
mkdir -p target/dojo-upsilon/
echo "buildDojo.sh pwd: `pwd`"
./target/dojo-release/util/buildscripts/build.sh profile=src/main/dojo/profile.js

echo "Size of release before clean: `find target/dojo-upsilon | wc -l` files, `du -hs target/dojo-upsilon/`"

# Clean up the release
rm -rf target/dojo-upsilon/build-report.txt
rm -rf target/dojo-upsilon/dijit/themes/nihilo
rm -rf target/dojo-upsilon/dijit/themes/soria
rm -rf target/dojo-upsilon/dijit/themes/tundra
rm -rf target/dojo-upsilon/dojox/mobile

#find target/dojo-upsilon/dijit/form/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/dijit/_editor/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/dojox/editor/plugins/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/dojox/grid/enhanced/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/gridx/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/dojo/cldr/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;
#find target/dojo-upsilon/dojo/nls -maxdepth 1 -type d ! -name uk ! -name '.' | xargs rm -rf {} \;

echo "Size of release before clean: `find target/dojo-upsilon | wc -l` files, `du -hs target/dojo-upsilon/`"

# Copy it to the src/ directory.
rm -rf src/main/php/resources/dojo/*
cp -r target/dojo-upsilon/* src/main/php/resources/dojo/

