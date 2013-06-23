package upTests.configurationTests;

import java.io.File;
import java.net.URL;

import junit.framework.Assert;

import org.joda.time.Duration;
import org.junit.Before;
import org.junit.Test;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;
import upsilon.util.Path;

public class ServiceInheritance {
	@Before
	public void clearConfig() {
		Configuration.instance.clear();
	}

    @Test 
    public void testConfiguration() throws Exception {
        final Path before = new Path("file://src/test/resources/configChanged/serviceInheritance/config.xml");
  
        final XmlConfigurationLoader loader = new XmlConfigurationLoader();
        final FileChangeWatcher fcw = loader.load(before, false);
 
        Assert.assertEquals(loader.getUrl(), before); 
        Assert.assertEquals(loader.getValidator().getPath(), before);
        Assert.assertTrue(loader.getValidator().isParseClean());

        Assert.assertTrue(Configuration.instance.services.containsId("baseService"));
        final StructureService baseService = Configuration.instance.services.get("baseService");

        Assert.assertEquals("baseService", baseService.getIdentifier());
        Assert.assertNotSame(GlobalConstants.DEF_TIMEOUT, baseService.getTimeout());
        Assert.assertEquals(Duration.standardSeconds(5), baseService.getTimeout());

        Assert.assertTrue(Configuration.instance.services.containsId("childService"));
        final StructureService childService = Configuration.instance.services.get("childService");
        Assert.assertEquals(2, Configuration.instance.services.size());

        Assert.assertEquals(Duration.standardSeconds(5), childService.getTimeout());
    } 
}
