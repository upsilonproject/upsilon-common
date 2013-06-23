package upTests;

import java.io.File;

import junit.framework.Assert;

import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;
import upsilon.util.Path;

public class ConfigurationReloadTest {
    private static final transient Logger LOG = LoggerFactory.getLogger(ConfigurationReloadTest.class);

    @Test
    public void testConfig() throws Exception {
        ConfigurationReloadTest.LOG.debug("Reloading config");

        final Path one = new Path("src/test/resources/configChanged/config.before.xml");
        final Path two = new Path("src/test/resources/configChanged/config.after.xml");

        two.setLastModified(one.lastModified() + 1000);

        Assert.assertTrue(one.lastModified() < two.lastModified());

        final XmlConfigurationLoader loader = new XmlConfigurationLoader();
        final FileChangeWatcher fcw = loader.load(one, false);
 
        Assert.assertTrue(loader.getValidator().isParseClean());

        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertTrue(Configuration.instance.services.containsId("mindstormPing"));
        Assert.assertEquals(Configuration.instance.services.size(), 2);
  
        fcw.setWatchedFile(two); 
        fcw.checkForModification();
 
        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertFalse(Configuration.instance.services.containsId("mindstormPing"));
        Assert.assertEquals(Configuration.instance.services.size(), 1);
    }
}
