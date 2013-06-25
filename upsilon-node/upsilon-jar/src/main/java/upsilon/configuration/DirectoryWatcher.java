package upsilon.configuration;

import java.io.File;
import java.net.URISyntaxException;
import java.net.URL;
import java.nio.file.DirectoryStream;
import java.util.HashMap;
import java.util.Vector;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Main;
import upsilon.util.UPath;
import upsilon.util.Util;

public class DirectoryWatcher implements Runnable {
	final UPath path; 
	private static final Logger LOG = LoggerFactory.getLogger(DirectoryWatcher.class);
	
	private static Vector<File> index = new Vector<>();
	
	private final Listener listener;
	
	public static interface Listener {
		void onNewFile(File f);
	}
	 
	public DirectoryWatcher(UPath path, Listener listener) throws URISyntaxException {
		this.path = path;
		this.listener = listener; 
		  
		registry.add(this);
		 
		this.thread = new Thread(this, "Directory Watcher: " + path); 
		this.thread.start();
	} 
	
	private final Thread thread; 
	
	public Thread getThread() {
		return this.thread; 
	} 
		
	@Override 
	public void run() {
		try {
			while(continueMonitoring) {
				Util.lazySleep(Duration.standardSeconds(5)); 
				
				for (File f : this.path.listFiles()) {
					if (f.isFile() && f.getName().endsWith("xml") && !index.contains(f)) { 
						index.add(f);
						 
						LOG.info("Found new configuration file in monitored directory:" + f.getAbsolutePath());
						   
						listener.onNewFile(f); 
					}   
				}  
			}  
		} catch (Exception e) { 
			LOG.error("Err in directory watcher: " + e);
		}	
	}

	public static boolean canMonitor(UPath path) {
		if (!path.isLocal()) {
			return false;
		} else { 
			return path.isDirectory(); 
		} 
	}
	
	private boolean continueMonitoring = true; 
	private static Vector<DirectoryWatcher> registry = new Vector<>();

	public static void stopAll() {
		for(DirectoryWatcher w : registry) {
			w.continueMonitoring = false; 
		}
	}

	public static void allowReloading(UPath path) {
		index.remove(path);
	}
}
