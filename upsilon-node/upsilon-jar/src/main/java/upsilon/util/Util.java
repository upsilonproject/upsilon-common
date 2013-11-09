package upsilon.util;

import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.nio.CharBuffer;
import java.nio.charset.Charset;
import java.util.concurrent.Executors;
import java.util.concurrent.ThreadFactory;
import java.util.concurrent.atomic.AtomicLong;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Main;

public class Util {
	private static final transient Logger LOG = LoggerFactory.getLogger(Util.class);

	private static final int BUF_SIZE = 0x800; // 2K chars (4K bytes)

	private static final Charset UTF_8 = Charset.forName("UTF-8");

	public static String bool2s(boolean value, String t, String f) {
		if (value) {
			return t;
		} else {
			return f;
		}
	}

	public static long copy(Readable from, Appendable to) throws IOException {
		CharBuffer buf = CharBuffer.allocate(BUF_SIZE);
		long total = 0;
		while (from.read(buf) != -1) {
			buf.flip();
			to.append(buf);
			total += buf.remaining();
			buf.clear();
		}
		return total;
	}

	public static String implode(final String[] array) {
		final StringBuilder sb = new StringBuilder();

		for (final String bit : array) {
			sb.append(bit);
			sb.append(" ");
		}

		return sb.toString().trim();
	}

	public static String isToString(InputStream stream) throws IOException {
		return toString(new InputStreamReader(stream, UTF_8));
	}

	public static void lazySleep(final Duration howLong) {
		try {
			Thread.sleep(howLong.getMillis());
		} catch (final InterruptedException e) {
			Util.LOG.warn("Insomnia in thread.", e);
		}
	}

	public static ThreadFactory newThreadFactory(final String nameFormat) {
		return newThreadFactory(nameFormat, Thread.currentThread().isDaemon(), Thread.currentThread().getPriority());
	}

	private static ThreadFactory newThreadFactory(final String nameFormat, final boolean daemon, final int priority) {
		return new ThreadFactory() {
			private final ThreadFactory backingThreadFactory = Executors.defaultThreadFactory();
			private final AtomicLong count = new AtomicLong(0);

			@Override
			public Thread newThread(Runnable runnable) {
				Thread thread = this.backingThreadFactory.newThread(runnable);
				thread.setName(String.format(nameFormat, this.count.getAndIncrement()));
				thread.setDaemon(daemon);
				thread.setPriority(priority);

				thread.setUncaughtExceptionHandler(Main.instance);
				return thread;
			}
		};
	}

	public static String toString(Readable r) throws IOException {
		return toStringBuilder(r).toString();
	}

	private static StringBuilder toStringBuilder(Readable r) throws IOException {
		StringBuilder sb = new StringBuilder();
		copy(r, sb);
		return sb;
	}
}
