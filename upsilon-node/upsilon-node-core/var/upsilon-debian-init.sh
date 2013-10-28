#!/bin/bash
#
# upsilon-node        Startup script for upsilon-node.
#
# chkconfig: - 80 80
# description: Upsilon is a distributed, flexible and extensible \
# system monitoring application
### BEGIN INIT INFO
# Provides: $upsilon-node
# Required-Start: $local_fs $network
# Required-Stop: $local_fs 
# Default-Start: - 
# Default-Stop: -
# Short-Description: Upsilon is a distributed, flexible and extensible \
#		system monitoring application
# Description: Upsilon is a distributed, flexible and extensible \
#		system monitoring application
### END INIT INFO

checkpid() {
	PID=`ps aux | grep upsilon-node | grep java | head -1 | awk '{print $2}'`
}

RETVAL=0
prog=upsilon
cmdline="/usr/bin/java -Djava.net.preferIPv4Stack=true -jar /usr/share/upsilon-node/upsilon-node.jar"

start() {
	if [[ -z "$PID" ]]; then
		echo -n "Upsilon is starting... "
		nohup $cmdline | logger -t upsilon-node &

		sleep 2
		checkpid

		if [[ -z "$PID" ]]; then
			echo "Failed. Try looking in the following logs;";
			echo "    /var/log/upsilon-node.log (rsyslog)"
			echo "    /var/log/messages (normal syslog)"
			echo " "
			echo "If you cannot find anything in the logs, run:"
			echo "    java -jar /usr/share/upsilon-node/upsilon-node.jar"
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
		PID=`ps aux | grep upsilon-node | grep -v grep | awk '{print $2}'`

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
