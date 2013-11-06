rm -f *.jar *.deb *.gz

BASE=../../
cp $BASE/upsilon-node/upsilon-jar/target/*.jar ./
cp $BASE/upsilon-node/upsilon-node-rpm-fedora18/target/rpm/upsilon-node/RPMS/noarch/*.rpm ./
cp $BASE/upsilon-node/upsilon-node-rpm-generic/target/rpm/upsilon-node/RPMS/noarch/*.rpm ./
cp $BASE/upsilon-node/upsilon-node-rpm-rhel6/target/rpm/upsilon-node/RPMS/noarch/*.rpm ./
cp $BASE/upsilon-node/upsilon-node-deb/target/*.deb ./
cp $BASE/upsilon-node/upsilon-node-tgz/target/*.tar.gz ./
cp $BASE/upsilon-node/upsilon-node-tgz/target/*.zip ./
cp $BASE/upsilon-web/upsilon-web-tgz/target/*.tar.gz ./
