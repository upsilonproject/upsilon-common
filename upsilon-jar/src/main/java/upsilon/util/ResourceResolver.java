package upsilon.util;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Main;

public abstract class ResourceResolver {
    public static class ResourceResolverJar extends ResourceResolver {

        @Override
        public InputStream getFromFilename(final String filename) throws FileNotFoundException {
            final File f = new File(this.getConfigDir(), filename);

            if (!f.exists()) {
                throw new FileNotFoundException(filename);
            }

            return new FileInputStream(f);
        }

        @Override
        public InputStream getInternalFromFilename(final String filename) throws FileNotFoundException {
            return this.getClass().getResourceAsStream("/" + filename);
        }
    }

    private transient static final Logger LOG = LoggerFactory.getLogger(ResourceResolver.class);

    public static ResourceResolver getInstance() {
        return new ResourceResolverJar();
    }

    public File getConfigDir() {
        if (Main.getConfigurationOverridePath() != null) {
            return Main.getConfigurationOverridePath();
        } else {
            return this.getOsConfigDir();
        }
    }

    public abstract InputStream getFromFilename(String filename) throws Exception;

    public abstract InputStream getInternalFromFilename(String filename) throws Exception;

    public File getOsConfigDir() {
        final String os = System.getProperty("os.name");
        File f;

        if (os.contains("Windows")) {
            f = new File(System.getenv("PROGRAMDATA"), "/Upsilon");
        } else if (os.contains("Linux")) {
            f = new File("/etc/upsilon/");

            if (!f.exists()) {
                if (!f.mkdirs()) {
                    f = new File(System.getenv("HOME") + "/.upsilon/");
                }
            }
        } else {
            ResourceResolver.LOG.warn("Could not find OS specific directory.");
            f = new File("./");
        }

        if (!f.exists()) {
            if (!f.mkdir()) {
                ResourceResolver.LOG.warn("Could not create configuration directory on this platform, which should be: " + f.getAbsolutePath());
            }
        }

        return f;
    }

}
