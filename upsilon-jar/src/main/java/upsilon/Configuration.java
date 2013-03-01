package upsilon;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;
import java.util.Arrays;
import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureGroup;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;
import upsilon.util.ResourceResolver;

public class Configuration {
    public final CollectionOfStructures<StructureCommand> commands = new CollectionOfStructures<StructureCommand>();
    public final CollectionOfStructures<StructureService> services = new CollectionOfStructures<StructureService>();
    public final CollectionOfStructures<StructureGroup> groups = new CollectionOfStructures<StructureGroup>();
    public final Vector<StructureRemoteService> remoteServices = new Vector<>();
    public final CollectionOfStructures<StructurePeer> peers = new CollectionOfStructures<StructurePeer>();
    public final CollectionOfStructures<StructureNode> remoteNodes = new CollectionOfStructures<StructureNode>();

    public final static Configuration instance = new Configuration();

    private static transient final Logger LOG = LoggerFactory.getLogger(Configuration.class);

    public Duration executorDelay = GlobalConstants.DEF_TIMER_EXECUTOR_DELAY;
    private final boolean daemonRestEnabled = true;
    private final boolean isCryptoEnabled = false;

    public int restPort = 4000;
    public Duration queueMaintainerDelay = GlobalConstants.DEF_TIMER_QUEUE_MAINTAINER_DELAY;
    public String passwordKeystore = "";
    public String passwordTrustStore = "";

    public Vector<String> trustedCertificates = new Vector<String>();

    public boolean isCryptoEnabled() {
        return this.isCryptoEnabled;
    }

    public boolean isDaemonRestEnabled() {
        return this.daemonRestEnabled;
    }

    private void parse(File file) {
        InputStream is;
        try {
            if (Main.getConfigurationOverridePath() != null) {
                is = new FileInputStream(new File(Main.getConfigurationOverridePath(), file.getName()));
            } else {
                is = ResourceResolver.getInstance().getFromFilename(file.getName());
            }

            Configuration.LOG.debug("Parsed configuration stream: " + file.getAbsolutePath());

            is.close();
        } catch (FileNotFoundException e) {
            Configuration.LOG.error("Required file could not be parsed (not found) ", e);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void parseTrustFingerprint(String fingerprint) {
        fingerprint = fingerprint.trim();
        fingerprint = fingerprint.replace(" ", "");
        fingerprint = fingerprint.replace(":", "");
        fingerprint = fingerprint.toLowerCase();

        Pattern p = Pattern.compile("[a-z|0-9]{40}");
        Matcher m = p.matcher(fingerprint);

        if (!m.matches()) {
            LOG.warn("Wont trust a dodgy looking SHA-1 fingerprint: " + fingerprint);
        } else {
            this.trustedCertificates.add(fingerprint);

            LOG.info("Trusting certificate with SHA1 fingerprint: " + fingerprint);
        }
    }

    public void reparse() {
        File configDirectory = ResourceResolver.getInstance().getConfigDir();

        if (!configDirectory.exists()) {
            Configuration.LOG.error("Local configuration directory does not exist: " + configDirectory.getAbsolutePath());
        } else {
            Configuration.LOG.info("Reparsing local configuration, using dir: " + configDirectory.getAbsolutePath());

            File[] listFiles = configDirectory.listFiles();

            Arrays.sort(listFiles);

            for (File f : listFiles) {
                if (f.getName().endsWith(".cfg")) {
                    this.parse(f);
                }
            }
        }
    }
}
