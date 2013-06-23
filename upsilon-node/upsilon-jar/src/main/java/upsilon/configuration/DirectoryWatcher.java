package upsilon.configuration;

import java.io.File;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.HashMap;
import java.util.Vector;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Main;
import upsilon.util.Path;
import upsilon.util.Util;

public class DirectoryWatcher implements Runnable {
	final Path path;
	private static final Logger LOG = LoggerFactory.getLogger(DirectoryWatcher.class);
	
	private static Vector<File> index = new Vector<>();
	 
	public DirectoryWatcher(Path path) throws URISyntaxException {
		this.path = path;  
		  
		registry.add(this);
		 
		new Thread(this, "Directory Watcher: " + path).start(); 
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
						   
						Main.instance.getXmlConfigurationLoader().load(new Path(f), true); 
					}  
				}  
			}  
		} catch (Exception e) { 
			LOG.error("Err in directory watcher: " + e);
		}
	}

	public static boolean canMonitor(Path path) {
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

	public static void allowReloading(Path path) {
		index.remove(path);
	}
}
