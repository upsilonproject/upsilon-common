package upsilon.configuration;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.util.GlobalConstants;

public class FileChangeWatcher {
	interface Listener {
		public void fileChanged(File f);
	}

	private static final transient Logger LOG = LoggerFactory.getLogger(FileChangeWatcher.class);

	private File fileBeingWatched;
	private long mtime = 0;
	private final Listener l;
	private final Thread t;
	private boolean continueMonitoring = true;

	public FileChangeWatcher(final File f, final Listener l) {
		this.fileBeingWatched = f;
		this.mtime = this.fileBeingWatched.lastModified();
		this.l = l;
		this.t = new Thread("File watcher for: " + this.fileBeingWatched.getName()) {
			@Override
			public void run() {
				FileChangeWatcher.this.watchForChanges();
			}
		};
	}

	public void checkForModification() {
		FileChangeWatcher.LOG.trace("Checking file for modification: " + this.mtime + " vs " + this.fileBeingWatched.lastModified() + " watching:" + this.fileBeingWatched.getAbsolutePath() + " ");

		if (this.mtime < this.fileBeingWatched.lastModified()) {
			this.mtime = this.fileBeingWatched.lastModified();

			FileChangeWatcher.LOG.debug("Configuration file has changed, notifying listeners.");
			this.l.fileChanged(this.fileBeingWatched);
		}
	}

	public void setWatchedFile(final File f) {
		FileChangeWatcher.LOG.debug("Watched file changed from " + this.fileBeingWatched.getName() + " to " + f.getName());

		this.fileBeingWatched = f;
	}

	public void start() {
		this.t.start();
	}

	public synchronized void stop() {
		this.continueMonitoring = false;
		this.notify();
	}

	private synchronized void watchForChanges() {
		while (FileChangeWatcher.this.continueMonitoring) {
			this.checkForModification();

			try {
				this.wait(GlobalConstants.CONFIG_WATCHER_DELAY.getMillis());
			} catch (final InterruptedException e) {
				e.printStackTrace();
				break;
			}
		}

		FileChangeWatcher.LOG.info("No longer watching file for changes: " + this.fileBeingWatched.getAbsolutePath());
	}
}
