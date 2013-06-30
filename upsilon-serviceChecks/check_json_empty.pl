#!/usr/bin/perl 

use strict;
use warnings;

use JSON;
use LWP::Simple;
use Nagios::Plugin;
use Lingua::EN::Inflect qw(PL);

my $url = $ARGV[0] or die "Base URL not provided.";
my $countWarning = defined $ARGV[1] ? $ARGV[1] : 0;
my $countCritical = defined $ARGV[2] ? $ARGV[2] : 5;
my $content = get $url or die "Could not get URL.";
my $jsonStructure = JSON->new->decode($content) or die "Invalid JSON.";
my $jsonArraySize = scalar @{$jsonStructure};

print "List contains $jsonArraySize ", PL('item', $jsonArraySize), ". \n";

my %listMetrics = (
	"metrics" => [{
		"name" => "items",
		"value" => $jsonArraySize
	}],
	"subresults" => $jsonStructure
);

my $json = JSON->new->allow_nonref->allow_blessed->convert_blessed->encode(\%listMetrics);

print "<json>$json</json>";

if ($jsonArraySize > $countCritical) {
	print "Critical";
	exit Nagios::Plugin::CRITICAL;
} elsif ($jsonArraySize > $countWarning) {
	print "Warning";
	exit Nagios::Plugin::WARNING;
} else {
	print "OK";
	exit Nagios::Plugin::OK;
}


