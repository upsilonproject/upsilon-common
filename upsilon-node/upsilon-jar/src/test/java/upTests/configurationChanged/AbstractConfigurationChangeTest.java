package upTests.configurationChanged;

import java.io.File;

import junit.framework.Assert;

import org.junit.Before;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public abstract class AbstractConfigurationChangeTest {
    protected final File before;
    protected final File after;

    private static final Logger LOG = LoggerFactory.getLogger(AbstractConfigurationChangeTest.class);

    public AbstractConfigurationChangeTest(final String folderName) {
        this.before = new File("src/test/resources/configChanged/" + folderName + "/before.xml");
        this.after = new File("src/test/resources/configChanged/" + folderName + "/after.xml");

        AbstractConfigurationChangeTest.LOG.debug("before: [{}] after: [{}]", new Object[] { this.before, this.after });
    }

    @Before
    public void setBasics() {
        Assert.assertTrue(this.before.exists());
        Assert.assertTrue(this.after.exists());

        this.after.setLastModified(this.before.lastModified() + 1000);

        Assert.assertTrue(this.before.lastModified() < this.after.lastModified());
    }
}
