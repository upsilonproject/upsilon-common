#!/bin/bash
# Everyone likes a nice big fat warning message...
echo "------------------------------------------------------------------------"
echo "WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING"
echo ""
echo ">>> THIS SCRIPT WILL ***DESTROY*** YOUR DATABASE."
echo ""
echo "WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING"
echo "------------------------------------------------------------------------"
echo " "
echo "This script is indented for developers. "
echo " "
echo "------------------------------------------------------------------------"
echo "Press CTRL+C to quit without importing anything."
read -sp "Enter your MySQL password (ENTER for none) to continue: " sqlpassword

if [ -n "$sqlpassword" ]; then
		sqlpassword="";
else
		sqlpassword="-p $sqlpassword";
fi

mysql -u root $sqlpassword -e 'DROP DATABASE upsilon; '
mysql -u root $sqlpassword -e 'CREATE DATABASE upsilon'
mysql -u root $sqlpassword upsilon < schema.sql 
mysql -u root $sqlpassword upsilon < initialData.sql
