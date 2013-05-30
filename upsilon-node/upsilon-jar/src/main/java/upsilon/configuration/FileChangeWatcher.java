package upsilon.configuration;

import java.io.File;
import java.net.URL;
import java.net.URLConnection;
import java.util.HashMap;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.util.GlobalConstants;

public class FileChangeWatcher {
	private static final HashMap<String, FileChangeWatcher> registry = new HashMap<String, FileChangeWatcher>(); 
	
	interface Listener {
		public void fileChanged(File f);
		public void fileChanged(URL url); 
	}

	private static final transient Logger LOG = LoggerFactory.getLogger(FileChangeWatcher.class);

	private File fileBeingWatched;
	private long mtime = 0;
	private final Listener l;
	private Thread t;
	private boolean continueMonitoring = true;
	private URL url;
	
	public FileChangeWatcher(final URL url, final Listener l) {
		this.url = url;
		this.l = l; 
		
		setupMonitoringThread();
	}

	public FileChangeWatcher(final File f, final Listener l) {
		if (registry.containsKey(f.getAbsolutePath())) {  
			throw new IllegalStateException("Already monitoring: " + f.getAbsolutePath());
		} else {
			registry.put(f.getAbsolutePath(), this);			
		}
		
		this.fileBeingWatched = f;
		this.mtime = this.fileBeingWatched.lastModified();
		this.l = l;
		
		setupMonitoringThread();
	}
	
	public void setupMonitoringThread() {
		String path;
		
		if (fileBeingWatched == null) {
			path = url.toString();
		} else {
			path = fileBeingWatched.getAbsolutePath();
		}
		 
		this.t = new Thread("File watcher for: " + path) {
			@Override
			public void run() {
				FileChangeWatcher.this.watchForChanges();
			}
		};
	}

	public void checkForModification() {
		long mtime = 0;
				
		if (url == null) {
			mtime = fileBeingWatched.lastModified();
		} else {
			try {
				URLConnection conn = url.openConnection();
				mtime = conn.getLastModified(); 
			} catch (Exception e) {
				LOG.warn("Cannot monitor URL: " + e.toString());
				return;
			}  
		}
		  
		FileChangeWatcher.LOG.trace("Checking file for modification: " + this.mtime + " vs " + mtime);

		if (this.mtime < mtime) {
			this.mtime = mtime;

			FileChangeWatcher.LOG.debug("Configuration file has changed, notifying listeners.");
			
			if (url == null) {
				this.l.fileChanged(this.fileBeingWatched);
			} else { 
				this.l.fileChanged(url);
			}
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
