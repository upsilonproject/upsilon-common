#!/usr/bin/python

import httplib
import sys
import socket
import argparse

def getHttpClient(ssl, address, port):
	if ssl:
		httpClient = httplib.HTTPSConnection(address + ":" + str(port), timeout=2)
	else:
		httpClient = httplib.HTTPConnection(address + ":" + str(port), timeout=2)
	
	return httpClient

def getHttpContent(client, url):
	try:
		client.request("GET", url)
		res = client.getresponse()
	except socket.error as e:
		print "Could not even connect. Upsilon may not be running at this address & port."
		print "Socket error: " + str(e)
		sys.exit()
	except httplib.BadStatusLine as e:
		print "Connected, but could not parse HTTP response."
		print "If this server is running SSL, try again with --ssl"
		sys.exit()

	if res.status != 200:
		error("Requested: %s, Expected HTTP 200, got HTTP %d" % (url, res.status))

	res = res.read()

	assert len(res) > 0, "Expected non-empty response."

	return res

def commonArgumentParser():
	parser = argparse.ArgumentParser();
	parser.add_argument('--address', '-a', help = "Hostname or IP address of upsilon-node", default = "localhost")
	parser.add_argument('--port', '-p', help = "Port", default = 4000)
	parser.add_argument('--ssl', action = "store_true")

	return parser

def error(message = None, e = None):
	print "[ERROR]", message

	if not e == None:
		print "Exception:", str(e)

	sys.exit(1)

