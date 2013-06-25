#!/bin/bash
#
# upsilon        Startup script for upsilon.
#
# chkconfig: 2345 12 88
# description: Upsilon is a distributed, flexible and extensible \
# system monitoring application
### BEGIN INIT INFO
# Provides: $upsilon
# Required-Start: $local_fs $network
# Required-Stop: $local_fs 
# Default-Start:  2 3 4 5
# Default-Stop: 0 1 6
# Description: Upsilon is a distributed, flexible and extensible \
#		system monitoring application
### END INIT INFO

# Source function library.
. /etc/init.d/functions

checkpid() {
	PID=`ps aux | grep upsilon.jar | grep -v grep | awk '{print $2}'`
	return $PID
}

RETVAL=0
prog=upsilon
cmdline="/usr/bin/java -Djava.net.preferIPv4Stack=true -jar /usr/share/upsilon/upsilon.jar"

start() {
	if [[ -z "$PID" ]]; then
		echo -n "Upsilon is starting... "
		nohup $cmdline | logger -t upsilon &

		checkpid

		if [[ -z "$PID" ]]; then
			echo "Failed :(";
		else
			echo "OK! PID: $PID"
		fi

		return $RETVAL
	else
		echo "Upsilon is already running, wont start it again."
	fi
}

stop() {
	if [[ -z "$PID" ]]; then
		echo "Upsilon is not running."
	else
		PID=`ps aux | grep upsilon.jar | grep -v grep | awk '{print $2}'`

		kill -9 $PID
		echo -n $"Stopped upsilon ($PID). "

		RETVAL=$?
		echo

		return $RETVAL
	fi
}

status() {
	if [[ -z "$PID" ]]; then
		echo "Upsilon does not appear to be running."
	else
		echo "Upsilon is running as PID: $PID"
	fi
}

restart() {
        stop
        start
}


checkpid

case "$1" in
  start)
        start
        ;;
  stop)
        stop
        ;;
  restart)
        restart
        ;;
  reload)
        exit 3
        ;;
  force-reload)
        restart
        ;;
  status)
	status
        ;;
  *)
        echo $"Usage: $0 {start|stop|restart|status}"
        exit 3
esac

exit $?
