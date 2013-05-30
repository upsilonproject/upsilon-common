package upsilon;

import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.configuration.XmlNodeHelper;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;

public class Configuration {
	public final CollectionOfStructures<StructureCommand> commands = new CollectionOfStructures<StructureCommand>("StructureCommand");
	public final CollectionOfStructures<StructureService> services = new CollectionOfStructures<StructureService>("StructureService");
	public final Vector<StructureRemoteService> remoteServices = new Vector<>();
	public final CollectionOfStructures<StructurePeer> peers = new CollectionOfStructures<StructurePeer>("StructurePeer");
	public final CollectionOfStructures<StructureNode> remoteNodes = new CollectionOfStructures<StructureNode>("StructureNode");

	public final static Configuration instance = new Configuration();

	private static transient final Logger LOG = LoggerFactory.getLogger(Configuration.class);

	public Duration executorDelay = GlobalConstants.DEF_TIMER_EXECUTOR_DELAY;
	public boolean daemonRestEnabled = GlobalConstants.DEF_DAEMON_REST_ENABLED;
	public boolean isCryptoEnabled = GlobalConstants.DEF_CRYPTO_ENABLED;
	public int restPort = GlobalConstants.DEF_REST_PORT;
	public Duration queueMaintainerDelay = GlobalConstants.DEF_TIMER_QUEUE_MAINTAINER_DELAY;
	public String passwordKeystore = "";
	public String passwordTrustStore = "";

	public Vector<String> trustedCertificates = new Vector<String>();

	private boolean initialFileParsed = false;
	public int maxThreadsRestKernel = 1;
	public int maxThreadsRestWorkers = 1;

	public void parseTrustFingerprint(String fingerprint) {
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

	public void update(final XmlNodeHelper node) {
		if (this.initialFileParsed == false) {
			this.restPort = node.getAttributeValueOrDefault("restPort", GlobalConstants.DEF_REST_PORT);
			this.queueMaintainerDelay = Duration.parse(node.getAttributeValueOrDefault("queueMaintainerDelay", GlobalConstants.DEF_TIMER_QUEUE_MAINTAINER_DELAY.toString()));
			this.daemonRestEnabled = node.getAttributeValueOrDefault("daemonRestEnabled", GlobalConstants.DEF_DAEMON_REST_ENABLED);
			this.isCryptoEnabled = node.getAttributeValueOrDefault("crypto", GlobalConstants.DEF_CRYPTO_ENABLED);
			this.maxThreadsRestKernel = node.getAttributeValueOrDefault("maxThreadsRestKernel", 1);
			this.maxThreadsRestWorkers = node.getAttributeValueOrDefault("maxThreadsRestWorkers", 1);

			if (node.hasChildElement("keystore")) {
				this.passwordKeystore = node.getFirstChildElement("keystore").getAttributeValueOrDefault("password", "");
			}

			if (node.hasChildElement("truststore")) {
				this.passwordTrustStore = node.getFirstChildElement("truststore").getAttributeValueOrDefault("password", "");
			}

			if (node.hasChildElement("database")) {
				final XmlNodeHelper dbElement = node.getFirstChildElement("database");
				final String hostname = dbElement.getAttributeValueUnchecked("hostname");
				final String username = dbElement.getAttributeValueUnchecked("username");
				final String password = dbElement.getAttributeValueUnchecked("password");
				final String dbname = dbElement.getAttributeValueUnchecked("dbname");
				final int port = dbElement.getAttributeValueOrDefault("port", 3306);

				Database.instance = new Database(hostname, username, password, port, dbname);

				try {
					Database.instance.connect();
				} catch (final Exception e) {
					Configuration.LOG.warn("Cannot connect to database: " + e.getMessage());
				}

				Configuration.LOG.info("Registered DB instance: hostname: {} user: {} port: {} dbname: {}", new Object[] { hostname, username, port, dbname });
			}

			this.initialFileParsed = true;
		}
	}
}
