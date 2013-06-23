package upsilon.util;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.net.URL;
import java.net.URLConnection;

public class Path {
	private URL url;
	
	public Path(URL url) { 
		this.url = url;
	} 
	
	public Path(String path) throws MalformedURLException {
		int protoSeparator = path.indexOf(":");
		 
		if (protoSeparator == -1) {
			path = "file://" + path;
		//} else {
			//String proto = path.substring(0, protoSeparator);
		}
			
 
		 
		this.url = new URL(path); 
	} 
	
	public Path(File f ) throws MalformedURLException { 
		this.url = f.toURI().toURL();
	} 
	
	public Path(File configurationOverridePath, String filename) throws MalformedURLException {
		this.url = new URL(configurationOverridePath.toURI().toURL() + File.separator + filename);
	}  

	public boolean isLocal() { 
		if (url.getProtocol().equals("file")) {  
			return true;
		} else {
			return false;
		}
	}
	
	public boolean isDirectory() {
		if (!isLocal()) {
			return false;
		} else {
			return toFile().isDirectory(); 
		}
	}
	
	public boolean isFile() {
		if (!isLocal()) { 
			return true; // There is always something at the end of a URL, even if it is a 404
		} else { 
			return toFile().isFile();
		}
	} 
	
	public boolean isRemote() { 
		return this.url.getProtocol().equals("http");
	}
	
	private File toFile() {
		String s = url.getHost() + File.separator + url.getFile();
		File f = new File(s);
		 
		return f;
	}
 
	public URL getUrl() {
		return url;
	}
   
	public InputStream getInputStream() throws IOException {
		if (isLocal()) {
			return new FileInputStream(toFile());
		} else { 
			return url.openStream();
		} 
	}

	public boolean exists() {
		if (isLocal()) { 
			return toFile().exists(); 
		} else {
			return true; // Could be a 404, but, pfft.
		}
	}
	
	@Override
	public String toString() {
		return url.toString();
	}
 
	public File[] listFiles() {
		return toFile().listFiles();
	}

	public boolean isAbsolute() { 
		try {
			return this.url.toURI().isAbsolute();
		} catch (URISyntaxException e) {
			return false;
		} 
	}

	public long getMtime() {
		long mtime = 0;
		 
		try {
			if (this.isLocal()) {  
				if (!this.exists()) { 
					throw new IllegalStateException("Configuration file does not exist: " + this.toString());
				}
				
				mtime = toFile().lastModified();
			} else {
				URLConnection conn = url.openConnection();
				mtime = conn.getLastModified();
			}
		} catch (Exception e) {
			throw new IllegalStateException("Cannot monitor URL: " + e.toString());
		} 
		 
		return mtime;  
	}

	public String getFilename() {
		if (isLocal()) {
			return toFile().getName();
		} else {
			String url = this.url.toString();
			String filename = url.substring(url.lastIndexOf("/"));
			 
			return filename;
		}
	} 

	public void setLastModified(long l) {
		this.toFile().setLastModified(l);
	}
 
	public long lastModified() {
		return getMtime();
	}  
}
