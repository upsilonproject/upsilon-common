#!/bin/python

from upsilon_common import *

dat = reqJson("http://tydus.net/MumPI/viewer/ajax.getUsers.php?serverId=1");

metadata = clsmetadata()

for user in dat:
		metadata.addSubresult(user['username'], comment = user['channel']);

exit(OK, metadata)
