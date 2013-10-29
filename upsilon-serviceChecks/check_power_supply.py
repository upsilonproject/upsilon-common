#!/usr/bin/python

from upsilon_common import *
from os import listdir
from os.path import join

powerSupplies = "/sys/class/power_supply/"

onAcPower = False
hasBattery = False
batteryCharge = 0

for supply in listdir(powerSupplies):
	if "AC" in supply and open(join(powerSupplies, supply, "online"), 'r').read().strip() == "1":
		onAcPower = True

	if "BAT" in supply:
		hasBattery = True

		batteryCharge = open(join(powerSupplies, supply, "capacity"), 'r').read().strip()

if onAcPower:
	exit(OK, None, "On AC Power")
elif hasBattery:
	metadata = clsmetadata()
	metdata.addMetric("Battery charge", batteryCharge, OK if batteryCharge > 50 else CRITICAL)
	exit(WARNING, None, "On Battery, charge is: " + str(batteryCharge) + "%")
else:
	exit(CRITICAL, None, "Can't detect power source!")
