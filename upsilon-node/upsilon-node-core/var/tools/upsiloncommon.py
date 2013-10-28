#!/usr/bin/python

import httplib
import sys
import socket

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

	assert res.status == 200, "Expected HTTP 200."

	res = res.read()

	assert len(res) > 0, "Expected non-empty response."

	return res

