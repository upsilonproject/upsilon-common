package upsilon.configuration;

import java.io.File;
import java.net.URL;
import java.net.URLConnection;
import java.util.HashMap;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.util.GlobalConstants;
import upsilon.util.UPath;

public class FileChangeWatcher {
	private static final HashMap<UPath, FileChangeWatcher> fileChangeRegistry = new HashMap<UPath, FileChangeWatcher>(); 
	
	interface Listener {  
		public void fileChanged(UPath url); 
	} 

	private static final transient Logger LOG = LoggerFactory.getLogger(FileChangeWatcher.class);

	private final Listener l;
	private Thread monitoringThread;
	private boolean continueMonitoring = true;
	private UPath path;
	
	public FileChangeWatcher(final UPath path, final Listener l) {
		this.path = path;
		this.l = l; 
		 
		FileChangeWatcher.updateMtime(path, path.lastModified());
		
		setupMonitoringThread();
	}

	public static boolean isAlreadyMonitoring(UPath path) {
		return fileChangeRegistry.containsKey(path); 
	}   
 
	public void setupMonitoringThread() { 
		fileChangeRegistry.put(this.path, this);
		   
		this.monitoringThread = new Thread("File watcher for: " + path.getFilename()) {
			@Override
			public void run() {
				FileChangeWatcher.this.watchForChanges();
			}
		}; 
	}

	public void checkForModification() {
		if (FileChangeWatcher.isChanged(this.path)) {  
			FileChangeWatcher.updateMtime(this.path, getMtime(path));
			FileChangeWatcher.LOG.debug("Configuration file has changed, notifying listeners.");
			
			this.l.fileChanged(path);   
		}
	} 
		
	private static final HashMap<UPath, Long> fileModificationTimes = new HashMap<>();
	 
	public static long getMtime(UPath path) {
		long mtime;
		
		if (fileModificationTimes.containsKey(path)) {
			mtime = fileModificationTimes.get(path);
		} 
		
		mtime = path.getMtime();
		 
		return mtime;
	}
	 
	private static boolean isChanged(UPath url) throws IllegalStateException {
		long mtime = getMtime(url); 
		long dbmtime = fileModificationTimes.get(url);
		
		FileChangeWatcher.LOG.trace("Checking file for modification: " + dbmtime + " vs " + mtime);
		 
		return mtime > dbmtime;	
	} 
	
	public static void updateMtime(UPath url, long newTime) {
		fileModificationTimes.put(url, newTime); 
	}
 
	public void setWatchedFile(final UPath path) {
		fileModificationTimes.put(this.path, path.getMtime() - 1);
		fileModificationTimes.put(path, path.getMtime() - 1); 
		     
		FileChangeWatcher.LOG.debug("Watched file changed from " + this.path + " to " + path);

		this.path = path;
	}

	public void start() {
		this.monitoringThread.start();
	}

	public synchronized void stop() {
		this.continueMonitoring = false;
		this.notify();
	}

	private synchronized void watchForChanges() {
		while (FileChangeWatcher.this.continueMonitoring) {
			try {
				this.wait(GlobalConstants.CONFIG_WATCHER_DELAY.getMillis());
				
				this.checkForModification(); 
			} catch (final InterruptedException | IllegalStateException e) {
				continueMonitoring = false;
			} 
		}  
  
		FileChangeWatcher.LOG.info("No longer watching file for changes: " + this.path);
		fileChangeRegistry.remove(path);  
		DirectoryWatcher.allowReloading(path); 
	}  

	public static void stopAll() {
		for (FileChangeWatcher fcw : fileChangeRegistry.values()) {
			fcw.continueMonitoring = false; 
		} 
	}  
} 
