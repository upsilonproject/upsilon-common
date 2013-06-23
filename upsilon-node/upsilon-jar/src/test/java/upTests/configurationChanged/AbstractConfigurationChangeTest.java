package upTests.configurationChanged;

import java.io.File;

import junit.framework.Assert;

import org.junit.Before;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
 
import upsilon.Configuration;
import upsilon.util.Path;

public abstract class AbstractConfigurationChangeTest {
    protected final Path before;
    protected final Path after;
 
    private static final Logger LOG = LoggerFactory.getLogger(AbstractConfigurationChangeTest.class);
 
    public AbstractConfigurationChangeTest(final String folderName) throws Exception {
        this.before = new Path("src/test/resources/configChanged/" + folderName + "/config.before.xml");
        this.after = new Path("src/test/resources/configChanged/" + folderName + "/config.after.xml");

        AbstractConfigurationChangeTest.LOG.debug("before: [{}] after: [{}]", new Object[] { this.before, this.after });
    }
    
    @Before
    public void setupConfig() {
    	Configuration.instance.clear();
    }

    @Before
    public void setBasics() { 
        Assert.assertTrue(this.before.exists());
        Assert.assertTrue(this.after.exists());

        this.after.setLastModified(this.before.getMtime() + 1000);
 
        Assert.assertTrue(this.before.getMtime() < this.after.getMtime());
    }
}
