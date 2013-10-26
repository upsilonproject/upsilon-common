package upTests.configurationChanged;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;

public class InvalidDependantCommand extends AbstractConfigurationChangeTest {
	public InvalidDependantCommand() throws Exception {
		super("invalidDependantCommand");
	}

	@Test
	public void testConfig() throws Exception {
		final XmlConfigurationLoader loader = new XmlConfigurationLoader();
		final FileChangeWatcher fcw = loader.load(this.before, false);

		Assert.assertEquals(loader.getUrl(), this.before);
		Assert.assertEquals(loader.getValidator().getPath(), this.before);
		Assert.assertTrue(loader.getValidator().isParseClean());

		Assert.assertTrue(Configuration.instance.services.containsId("helloWorld"));
		Assert.assertEquals(Configuration.instance.services.size(), 1);

		fcw.setWatchedFile(this.after);
		fcw.checkForModification();

		Assert.assertEquals(loader.getUrl(), this.after);
		Assert.assertEquals(loader.getValidator().getPath(), this.after);
		Assert.assertFalse(loader.getValidator().isParseClean());

		// Check that no structured were tarnished
		Assert.assertTrue(Configuration.instance.services.containsId("helloWorld"));
		Assert.assertEquals(Configuration.instance.services.size(), 1);
	}
}
