package upTests;

import java.io.File;

import junit.framework.Assert;

import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;

public class ConfigurationReloadTest {
    private static final transient Logger LOG = LoggerFactory.getLogger(ConfigurationReloadTest.class);

    @Test
    public void testConfig() {
        ConfigurationReloadTest.LOG.debug("Reloading config");

        final File one = new File("src/test/resources/configChanged/before.xml");
        final File two = new File("src/test/resources/configChanged/after.xml");

        two.setLastModified(two.lastModified() + 1);

        final XmlConfigurationLoader loader = new XmlConfigurationLoader();
        final FileChangeWatcher fcw = loader.load(one, false);

        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertTrue(Configuration.instance.services.containsId("mindstormPing"));

        fcw.setWatchedFile(two);
        fcw.checkForModification();

        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertFalse(Configuration.instance.services.containsId("mindstormPing"));
    }
}
