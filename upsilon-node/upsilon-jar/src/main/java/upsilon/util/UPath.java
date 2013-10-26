package upsilon.util;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.net.URL;
import java.net.URLConnection;
import java.nio.file.Path;

public class UPath {
	private final URL url;

	public UPath(File f) throws MalformedURLException {
		this.url = f.toURI().toURL();
	}

	public UPath(File configurationOverridePath, String filename) throws MalformedURLException {
		this.url = new URL(configurationOverridePath.toURI().toURL() + File.separator + filename);
	}

	public UPath(Path tempDirectory) throws Exception {
		this(tempDirectory.toFile());
	}

	public UPath(String path) throws MalformedURLException {
		int protoSeparator = path.indexOf(":");

		if (protoSeparator == -1) {
			path = "file://" + path;
		}

		this.url = new URL(path);
	}

	public UPath(URL url) {
		this.url = url;
	}

	public boolean exists() {
		if (this.isLocal()) {
			return this.toFile().exists();
		} else {
			return true; // Could be a 404, but, pfft.
		}
	}

	public String getFilename() {
		if (this.isLocal()) {
			return this.toFile().getName();
		} else {
			String url = this.url.toString();
			String filename = url.substring(url.lastIndexOf("/"));

			return filename;
		}
	}

	public InputStream getInputStream() throws IOException {
		if (this.isLocal()) {
			return new FileInputStream(this.toFile());
		} else {
			return this.url.openStream();
		}
	}

	public long getMtime() {
		long mtime = 0;

		try {
			if (this.isLocal()) {
				if (!this.exists()) {
					throw new IllegalStateException("Configuration file does not exist: " + this.toString());
				}

				mtime = this.toFile().lastModified();
			} else {
				URLConnection conn = this.url.openConnection();
				mtime = conn.getLastModified();
			}
		} catch (Exception e) {
			throw new IllegalStateException("Cannot monitor URL: " + e.toString());
		}

		return mtime;
	}

	public URL getUrl() {
		return this.url;
	}

	public boolean isAbsolute() {
		try {
			return this.url.toURI().isAbsolute();
		} catch (URISyntaxException e) {
			return false;
		}
	}

	public boolean isDirectory() {
		if (!this.isLocal()) {
			return false;
		} else {
			return this.toFile().isDirectory();
		}
	}

	public boolean isFile() {
		if (!this.isLocal()) {
			return true; // There is always something at the end of a URL, even
							// if it is a 404
		} else {
			return this.toFile().isFile();
		}
	}

	public boolean isLocal() {
		if (this.url.getProtocol().equals("file")) {
			return true;
		} else {
			return false;
		}
	}

	public boolean isRemote() {
		return this.url.getProtocol().equals("http");
	}

	public long lastModified() {
		return this.getMtime();
	}

	public File[] listFiles() {
		return this.toFile().listFiles();
	}

	public void setLastModified(long l) {
		this.toFile().setLastModified(l);
	}

	private File toFile() {
		String s = this.url.getHost() + File.separator + this.url.getFile();
		File f = new File(s);

		return f;
	}

	@Override
	public String toString() {
		return this.url.toString();
	}
}
