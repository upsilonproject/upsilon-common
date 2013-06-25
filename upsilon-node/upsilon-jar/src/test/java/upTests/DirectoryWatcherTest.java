package upTests;

import java.io.File;
import java.io.IOException;
import java.net.URISyntaxException;
import java.nio.file.Files;
import java.nio.file.Path;

import org.junit.Ignore;
import org.junit.Test;

import junit.framework.Assert;

import upsilon.configuration.DirectoryWatcher;
import upsilon.util.UPath;


public class DirectoryWatcherTest implements DirectoryWatcher.Listener {
	private boolean foundFile = false;
	 
	@Test
	@Ignore
	public void testTempFile() throws URISyntaxException, Exception {
		Path tempDirectory = Files.createTempDirectory("testDirectoryWatcher");
		 
		DirectoryWatcher dw = new DirectoryWatcher(new UPath(tempDirectory), this);
		dw.getThread().notify(); 
		File file1 = Files.createTempFile(tempDirectory, "file", ".xml").toFile();
		 
		Assert.assertFalse(foundFile); 
		Assert.assertTrue(file1.exists()); 
		
		Thread.sleep(6000);
		
		Assert.assertTrue(foundFile);
		
		file1.delete();
		 
		Assert.assertFalse(file1.exists()); 
	}
 
	@Override
	public void onNewFile(File f) {
		foundFile = true;
	} 
}
