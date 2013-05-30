mysqldump -u root -p upsilon --no-data | sed -e 's/AUTO_INCREMENT=[0-9][0-9]*//' > schema.sql
