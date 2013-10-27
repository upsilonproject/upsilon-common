package upTests;

import junit.framework.Assert;

import org.junit.AfterClass;
import org.junit.BeforeClass;
import org.junit.Test;

import upTests.configurationChanged.AbstractConfigurationChangeTest;
import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;

public class BasicReloadTest extends AbstractConfigurationChangeTest {
	@BeforeClass
	@AfterClass
	public static void clearConfig() {
		Configuration.instance.clear();
	}

	public BasicReloadTest() throws Exception {
		super("basicReload");
	}

	@Test
	public void testConfig() throws Exception {
		final XmlConfigurationLoader loader = new XmlConfigurationLoader();
		final FileChangeWatcher fcw = loader.load(this.before, false);

		Assert.assertTrue(loader.getValidator().isParseClean());

		Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
		Assert.assertTrue(Configuration.instance.services.containsId("mindstormPing"));
		Assert.assertEquals(Configuration.instance.services.size(), 2);

		fcw.setWatchedFile(this.after);
		fcw.checkForModification();

		Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
		Assert.assertFalse(Configuration.instance.services.containsId("mindstormPing"));
		Assert.assertEquals(Configuration.instance.services.size(), 1);
	}
}
