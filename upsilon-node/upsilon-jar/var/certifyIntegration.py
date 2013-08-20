#!/bin/python

import os
import httplib

print "This script certifies an installation of upsilon. "

testsRun = 0
testsPassed = 0

def test(title, result):
	global testsRun, testsPassed

	testsRun += 1

	if result:
		print "[  OK  ]",
		testsPassed += 1
	else:
		print "[ FAIL ]",

	print "Test:", title

def findUpsilonDirectory():
	if os.path.isdir("/usr/share/upsilon"):
		return "/usr/share/upsilon"
	
	return ""

def findConfigDirectory():
	if os.path.isdir("/etc/upsilon/"):
		return "/etc/upsilon"

	return ""

upsilonDirectory = findUpsilonDirectory();
configDirectory = findConfigDirectory()

test("Upsilon directory exists", os.path.isdir(upsilonDirectory))
test("Upsilon directory contains upsilon.jar", os.path.isfile(os.path.join(upsilonDirectory, "upsilon.jar")))
test("Upsilon config dir exists", os.path.isdir(configDirectory))
test("Upsilon config.xml exists", os.path.isfile(os.path.join(configDirectory, "config.xml")))

httpClient = httplib.HTTPSConnection("localhost:4000", timeout=2)

req = httpClient.request("GET", "/");
res = httpClient.getresponse()

test("Got / (home)", res.status == 200)
test("Got some content", len(res.read()) > 500);

req = httpClient.request("GET", "/internalStatus");
res = httpClient.getresponse()

test("Got /internalStatus", res.status == 200)
test("Got some content", len(res.read()) > 100);

print "Tests run:", testsRun, " Passed:", testsPassed, " Failed:", testsRun - testsPassed