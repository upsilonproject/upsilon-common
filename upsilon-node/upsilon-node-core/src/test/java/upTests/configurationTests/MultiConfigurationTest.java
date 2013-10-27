package upTests.configurationTests;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.hasSize;
import static org.hamcrest.Matchers.is;

import org.junit.AfterClass;
import org.junit.BeforeClass;
import org.junit.Test;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;
import upsilon.util.UPath;

public class MultiConfigurationTest {
	@BeforeClass
	@AfterClass
	public static void setupConfig() {
		Configuration.instance.clear();
	}

	@Test
	public void testMultiConfiguration() throws Exception {
		final UPath masterFile = new UPath("file://src/test/resources/multi/config.xml");
		final UPath slaveFile = new UPath("file://src/test/resources/multi/slave.xml");
		final Configuration config = Configuration.instance;

		XmlConfigurationLoader loader = new XmlConfigurationLoader();
		loader.load(masterFile, false);

		assertThat(loader.getValidator().isParseClean(), is(true));
		assertThat(config.services.getImmutable(), hasSize(1));
		assertThat(config.services.containsId("echo"), is(true));

		assertThat(config.commands.getImmutable(), hasSize(1));
		assertThat(config.commands.containsId("echo"), is(true));

		FileChangeWatcher fcwSlave = loader.load(slaveFile, false);

		Thread.sleep(1000);

		assertThat(config.services.getImmutable(), hasSize(2));
		assertThat(config.commands.getImmutable(), hasSize(2));

		loader.reparse();

		assertThat(config.services.getImmutable(), hasSize(2));
		assertThat(config.commands.getImmutable(), hasSize(2));

		fcwSlave.setWatchedFile(new UPath("file://src/test/resources/multi/config.empty.xml"));
		fcwSlave.checkForModification();

		assertThat(config.services.getImmutable(), hasSize(1));
		assertThat(config.services.containsId("hostname"), is(true));

		assertThat(config.commands.getImmutable(), hasSize(1));
		assertThat(config.commands.containsId("hostname"), is(true));
	}
}
