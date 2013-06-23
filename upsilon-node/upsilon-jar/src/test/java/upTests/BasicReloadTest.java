package upTests;

import java.io.File;

import junit.framework.Assert;

import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upTests.configurationChanged.AbstractConfigurationChangeTest;
import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;
import upsilon.util.Path;

public class BasicReloadTest extends AbstractConfigurationChangeTest {
    public BasicReloadTest() throws Exception {
		super("basicReload"); 
	} 

	private static final transient Logger LOG = LoggerFactory.getLogger(BasicReloadTest.class);

    @Test
    public void testConfig() throws Exception {
        BasicReloadTest.LOG.debug("Reloading config");

        final XmlConfigurationLoader loader = new XmlConfigurationLoader();
        final FileChangeWatcher fcw = loader.load(before, false);
 
        Assert.assertTrue(loader.getValidator().isParseClean());

        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertTrue(Configuration.instance.services.containsId("mindstormPing"));
        Assert.assertEquals(Configuration.instance.services.size(), 2);
    
        fcw.setWatchedFile(after); 
        fcw.checkForModification();
 
        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        Assert.assertFalse(Configuration.instance.services.containsId("mindstormPing"));
        Assert.assertEquals(Configuration.instance.services.size(), 1);
    }
}
