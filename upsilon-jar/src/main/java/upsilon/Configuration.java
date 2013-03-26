package upsilon;

import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Node;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureGroup;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;

public class Configuration {
    public final CollectionOfStructures<StructureCommand> commands = new CollectionOfStructures<StructureCommand>("StructureCommand");
    public final CollectionOfStructures<StructureService> services = new CollectionOfStructures<StructureService>("StructureService");
    public final CollectionOfStructures<StructureGroup> groups = new CollectionOfStructures<StructureGroup>("StructureGroup");
    public final Vector<StructureRemoteService> remoteServices = new Vector<>();
    public final CollectionOfStructures<StructurePeer> peers = new CollectionOfStructures<StructurePeer>("StructurePeer");
    public final CollectionOfStructures<StructureNode> remoteNodes = new CollectionOfStructures<StructureNode>("StructureNode");

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

    private void parseTrustFingerprint(String fingerprint) {
        fingerprint = fingerprint.trim();
        fingerprint = fingerprint.replace(" ", "");
        fingerprint = fingerprint.replace(":", "");
        fingerprint = fingerprint.toLowerCase();

        final Pattern p = Pattern.compile("[a-z|0-9]{40}");
        final Matcher m = p.matcher(fingerprint);

        if (!m.matches()) {
            Configuration.LOG.warn("Wont trust a dodgy looking SHA-1 fingerprint: " + fingerprint);
        } else {
            this.trustedCertificates.add(fingerprint);

            Configuration.LOG.info("Trusting certificate with SHA1 fingerprint: " + fingerprint);
        }
    }

    public void reparse() {
    }

    public void update(final Node node) {
        this.restPort = Integer.parseInt(node.getAttributes().getNamedItem("restPort").getNodeValue());
    }
}
