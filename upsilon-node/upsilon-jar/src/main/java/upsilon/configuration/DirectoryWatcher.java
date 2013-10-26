package upsilon.configuration;

import java.io.File;
import java.net.URISyntaxException;
import java.util.Vector;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.util.UPath;
import upsilon.util.Util;

public class DirectoryWatcher implements Runnable {
	public static interface Listener {
		void onNewFile(File f);
	}

	final UPath path;

	private static final Logger LOG = LoggerFactory.getLogger(DirectoryWatcher.class);

	private static Vector<File> index = new Vector<>();

	public static void allowReloading(UPath path) {
		index.remove(path);
	}

	public static boolean canMonitor(UPath path) {
		if (!path.isLocal()) {
			return false;
		} else {
			return path.isDirectory();
		}
	}

	public static void stopAll() {
		for (DirectoryWatcher w : registry) {
			w.continueMonitoring = false;
		}
	}

	private final Listener listener;

	private final Thread thread;

	private boolean continueMonitoring = true;

	private static Vector<DirectoryWatcher> registry = new Vector<>();

	public DirectoryWatcher(UPath path, Listener listener) throws URISyntaxException {
		this.path = path;
		this.listener = listener;

		registry.add(this);

		this.thread = new Thread(this, "Directory Watcher: " + path);
		this.thread.start();
	}

	public Thread getThread() {
		return this.thread;
	}

	@Override
	public void run() {
		try {
			while (this.continueMonitoring) {
				Util.lazySleep(Duration.standardSeconds(5));

				for (File f : this.path.listFiles()) {
					if (f.isFile() && f.getName().endsWith("xml") && !index.contains(f)) {
						index.add(f);

						LOG.info("Found new configuration file in monitored directory:" + f.getAbsolutePath());

						this.listener.onNewFile(f);
					}
				}
			}
		} catch (Exception e) {
			LOG.error("Err in directory watcher: " + e);
		}
	}
}
