package upTests;

import java.io.File;
import java.net.URISyntaxException;
import java.nio.file.Files;
import java.nio.file.Path;

import junit.framework.Assert;

import org.junit.Ignore;
import org.junit.Test;

import upsilon.configuration.DirectoryWatcher;
import upsilon.util.UPath;

public class DirectoryWatcherTest implements DirectoryWatcher.Listener {
	private boolean foundFile = false;

	@Override
	public void onNewFile(File f) {
		this.foundFile = true;
	}

	@Test
	@Ignore
	public void testTempFile() throws URISyntaxException, Exception {
		Path tempDirectory = Files.createTempDirectory("testDirectoryWatcher");

		DirectoryWatcher dw = new DirectoryWatcher(new UPath(tempDirectory), this);
		dw.getThread().notify();
		File file1 = Files.createTempFile(tempDirectory, "file", ".xml").toFile();

		Assert.assertFalse(this.foundFile);
		Assert.assertTrue(file1.exists());

		Thread.sleep(6000);

		Assert.assertTrue(this.foundFile);

		file1.delete();

		Assert.assertFalse(file1.exists());
	}
}
