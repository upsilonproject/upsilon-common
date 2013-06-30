#!/usr/bin/perl

use strict;
use warnings;
use Nagios::Plugin;
use Data::Dumper;

use Mail::IMAPClient;
use IO::Socket::SSL;

use JSON;

my $user = $ARGV[0] or die("Username not provided.");
my $pass = $ARGV[1] or die("Password not provided.");
my $serverAddr = $ARGV[2] or die("Server address not provided.");

my $socket = IO::Socket::SSL->new(SSL_verify_mode => SSL_VERIFY_NONE, PeerAddr => $serverAddr, PeerPort => '993') or die "Cannot open socket.";
my $conn;
$conn = Mail::IMAPClient->new(Server => $serverAddr, Socket => $socket, User => $user, Password => $pass) or die "Cannot connect to email server";
$conn->IsConnected() or die ("Failed to connect");
$conn->IsAuthenticated() or die ("Failed to authenticate");
$conn->select('INBOX') or die ("Failed to select mailbox");

my $messages = $conn->unseen_count;

print $messages . " messages in inbox\n";

$conn->logout();
my %listMetrics = (
	"metrics" => [{
	"name" => "unread",
	"value" => $messages}]
);

my $json = JSON->new->allow_nonref->allow_blessed->convert_blessed->encode(\%listMetrics);

print "<json>$json</json>";


if ($messages == 0) {
	exit Nagios::Plugin::OK;
} else {
	exit Nagios::Plugin::CRITICAL;
}
