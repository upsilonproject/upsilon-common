#!/usr/bin/perl

use strict;
use warnings;

use Nagios::Plugin;

my $disk = $ARGV[0] or die "Need to provide search term";
my $mount = `mount | grep $disk`;

if (length($mount) == 0) {
	print "Nothing returned from output\n";
	exit Nagios::Plugin::WARNING;
} else {
	print $mount;
	exit Nagios::Plugin::OK;
}
