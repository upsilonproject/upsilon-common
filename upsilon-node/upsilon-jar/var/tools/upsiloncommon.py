#!/usr/bin/python

import httplib
import sys

def getHttpClient(ssl, address, port):
	if ssl:
		httpClient = httplib.HTTPSConnection(address + ":" + str(port), timeout=2)
	else:
		httpClient = httplib.HTTPConnection(address + ":" + str(port), timeout=2)
	
	return httpClient

def getHttpContent(client, url):
	client.request("GET", url)

	try:
		res = client.getresponse()
	except httplib.BadStatusLine as e:
		print "Connected, but could not parse HTTP response."
		print "If this server is running SSL, try again with --ssl"
		sys.exit()

	assert res.status == 200, "Expected HTTP 200."

	res = res.read()

	assert len(res) > 0, "Expected non-empty response."

	return res

