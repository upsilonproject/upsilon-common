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
        LOG.debug("Reloading config");

        File one = new File("src/test/resources/configChanged/before.xml");
        File two = new File("src/test/resources/configChanged/after.xml");
        two.setLastModified(two.lastModified() + 1);

        XmlConfigurationLoader loader = new XmlConfigurationLoader();
        FileChangeWatcher fcw = loader.load(one, false);
        fcw.checkForModification();

        Assert.assertTrue(Configuration.instance.services.containsId("mindstormPing"));

        fcw.setWatchedFile(two);
        fcw.checkForModification();

        Assert.assertFalse(Configuration.instance.services.containsId("mindstormPing"));
    }
}
