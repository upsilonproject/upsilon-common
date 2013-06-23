package upsilon.util;

import java.net.URL;
import java.util.concurrent.ThreadFactory;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.google.common.util.concurrent.ThreadFactoryBuilder;

public class Util {
	private static final transient Logger LOG = LoggerFactory.getLogger(Util.class);

	public static ThreadFactory getThreadFactory(final String string) {
		return new ThreadFactoryBuilder().setNameFormat(string + " (%d)").build();
	}

	public static String implode(final String[] array) {
		final StringBuilder sb = new StringBuilder();

		for (final String bit : array) {
			sb.append(bit);
			sb.append(" ");
		}

		return sb.toString().trim();
	}

	public static void lazySleep(final Duration howLong) {
		try {
			Thread.sleep(howLong.getMillis());
		} catch (final InterruptedException e) {
			Util.LOG.warn("Insomnia in thread.", e);
		}
	}
	 
	public static String bool2s(boolean value, String t, String f) {
		if (value) { 
			return t;
		} else {
			return f; 
		}
	}
}
