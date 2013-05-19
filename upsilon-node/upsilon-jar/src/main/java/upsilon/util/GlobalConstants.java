package upsilon.util;

import org.joda.time.Duration;

public class GlobalConstants {
	public static final Duration DEF_TIMER_EXECUTOR_DELAY = Duration.standardSeconds(2);
	public static final Duration DEF_TIMER_QUEUE_MAINTAINER_DELAY = Duration.standardSeconds(3);

	public static final Duration MAX_UPDATE_FREQUENCY = Duration.standardSeconds(60);
	public static final Duration MIN_UPDATE_FREQUENCY = Duration.standardSeconds(10);

	public static final Duration MAX_SERVICE_SLEEP = Duration.standardMinutes(15);
	public static final Duration MIN_SERVICE_SLEEP = Duration.standardSeconds(10);

	public static final Duration MIN_EXECUTOR_SLEEP = Duration.standardSeconds(2);
	public static final Duration MAX_EXECUTOR_SLEEP = Duration.standardSeconds(30);
	public static final Duration INC_EXECUTOR_SLEEP = Duration.standardSeconds(5);

	public static final Duration DEF_INC_SERVICE_UPDATE = GlobalConstants.MIN_SERVICE_SLEEP;
	public static final Duration DEF_TIMEOUT = Duration.standardSeconds(3);

	public static final int DEF_REST_PORT = 4000;
	public static final boolean DEF_CRYPTO_ENABLED = true;
	public static final boolean DEF_DAEMON_REST_ENABLED = true;
	public static final Duration CONFIG_WATCHER_DELAY = Duration.standardSeconds(2);
}
