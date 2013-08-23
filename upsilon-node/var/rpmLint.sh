#!/bin/bash

for rpm in `find ../ -regex '.+rpm$' -type f `; do
	echo $rpm
	echo "-------------------------------"
	rpmlint $rpm
done
