package upsilon;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;
import java.net.URL;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import nagiosParser.LexicalAnalyiser;
import nagiosParser.SyntaxAnalyiser;
import nagiosParser.SyntaxAnalyiser.SyntaxException;
import nagiosParser.structure.Program;
import nagiosParser.structure.Structure;
import nagiosParser.structure.Structure.StructureArgumentProperty;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.ResultKarma;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;
import upsilon.util.ResourceResolver;

public class Configuration {
	public final CollectionOfStructures<StructureCommand> commands = new CollectionOfStructures<StructureCommand>();
	public final CollectionOfStructures<StructureService> services = new CollectionOfStructures<StructureService>();
	public final Vector<StructureRemoteService> remoteServices = new Vector<>();
	public final CollectionOfStructures<StructurePeer> peers = new CollectionOfStructures<StructurePeer>();
	public final CollectionOfStructures<StructureNode> remoteNodes = new CollectionOfStructures<StructureNode>();

	public final static Configuration instance = new Configuration();

	private static transient final Logger LOG = LoggerFactory.getLogger(Configuration.class);

	private String serviceWhitelist;
	public Duration executorDelay = GlobalConstants.DEF_TIMER_EXECUTOR_DELAY;
	private boolean daemonRestEnabled = true;
	private boolean isCryptoEnabled = true;

	public int restPort = 1337;
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

			this.parseFromIs(is);
			Configuration.LOG.debug("Parsed configuration stream: " + file.getAbsolutePath());

			is.close();
		} catch (FileNotFoundException e) {
			Configuration.LOG.error("Required file could not be parsed (not found) ", e);
		} catch (SyntaxException e) {
			Configuration.LOG.error("Syntax Exception: " + e.toString(), e);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	private void parseFromIs(InputStream is) throws Exception {
		LexicalAnalyiser la = new LexicalAnalyiser();
		la.parseFromInputStream(is);

		SyntaxAnalyiser sa = new SyntaxAnalyiser(la, false);
		sa.warningsEnabled = false;
		sa.parse();

		this.parseStructures(sa.getProgram());

		la.reset();
		sa.reset();

		is.close();
	}

	private void parseRemoteConfig() {
		for (StructurePeer peer : Configuration.instance.peers) {
			if (!peer.isRemoteConfigProvider()) {
				continue;
			}

			URL remoteConfig = peer.getRemoteConfig();

			if (remoteConfig == null) {
				continue;
			} else {
				try {
					Configuration.LOG.info("Parsing remote configuration: " + remoteConfig.toString());

					InputStream is = remoteConfig.openStream();

					this.parseFromIs(is);
				} catch (Exception e) {
					Configuration.LOG.error("Could not parse remote config: " + e.getMessage());
				}
			}
		}
	}

	private void parseStructureCommand(Structure s) {
		String commandName = s.getFirstParameterValue("name", "command_name");

		StructureCommand command = this.commands.get(commandName);

		if (command == null) {
			command = new StructureCommand();
		}

		command.setCommandLine(s.getFirstParameterValue("exec", "command_line"));
		command.setName(commandName);

		this.commands.register(command);
	}

	private void parseStructureConfig(Structure s) {
		this.serviceWhitelist = s.getParameterValue("serviceWhitelist");
		this.executorDelay = s.getParameterValueDuration("executorDelay", "10s");
		this.daemonRestEnabled = Boolean.parseBoolean(s.getParameterValue("daemonRestEnabled", "true"));
		this.restPort = s.getParameterValueInt("restPort", this.restPort);
		this.passwordKeystore = s.getParameterValue("passwordKeystore", "");
		this.passwordTrustStore = s.getParameterValue("passwordTruststore", "");
		this.isCryptoEnabled = Boolean.parseBoolean(s.getParameterValue("crypto", "true"));
	}

	private void parseStructureDatabase(Structure structure) {
		if (Database.instance != null) {
			LOG.warn("A database instance is already registered, will not register a new instance or update existing instance.");
		}

		String host = structure.getParameterValue("host");
		String user = structure.getParameterValue("user");
		String pass = structure.getParameterValue("pass");
		int port = structure.getParameterValueInt("port", 3306);
		String dbname = structure.getParameterValue("name", "upsilon");

		Database d;

		try {
			d = new Database(host, user, pass, port, dbname);
			d.connect();
		} catch (Exception e) {
			LOG.error("Cannot connect to database.", e);
			return;
		}

		Database.instance = d;
	}

	private void parseStructureDatabase(StructureService service) {
		String message = "service rebuilt from DB";
		HashMap<String, String> rs = Database.instance.getRow("services", "identifier", service.getIdentifier(), "id", "goodCount", "karma");

		if (!rs.isEmpty()) {
			int goodCount = Integer.parseInt(rs.get("goodCount"));

			if (goodCount > 0) {
				service.addResult(ResultKarma.GOOD, goodCount, message);
			} else {
				service.addResult(ResultKarma.valueOfOrUnknown(rs.get("karma")), 0, message);
			}
		}
	}

	private void parseStructurePeer(Structure s) {
		if (!s.hasParameter("host")) {
			LOG.warn("Peers require a 'host' parameter.");
			return;
		}

		if (s.hasParameter("register")) {
			if (s.getParameterValue("register").equals("false")) {
				LOG.warn("Ignoring a peer that is defined in configuration, but is not registered: " + s.getParameterValue("host"));
				return;
			}
		}

		if (s.hasParameter("cert")) {
			this.parseTrustFingerprint(s.getParameterValue("cert"));
		}

		try {
			StructurePeer peer = new StructurePeer(s.getParameterValue("host"), s.getParameterValueInt("port"));
			peer.setRemoteConfig(s.getParameterValue("remoteConfig"));

			Configuration.LOG.info("Registered peer: " + peer.getHostname() + ":" + peer.getPort());
			this.peers.register(peer);
		} catch (Exception e) {
			Configuration.LOG.warn("Could not register peer: " + s.getParameterValue("host") + ":" + s.getParameterValueInt("port"), e);
		}
	}

	private void parseStructures(Program p) {
		for (Structure s : p.getStructures()) {
			String clazz = s.getClazz();

			switch (clazz) {
			case "trust":
				this.parseStructureTrust(s);
				break;
			case "peer":
				this.parseStructurePeer(s);
				break;
			case "config":
				this.parseStructureConfig(s);
				break;
			case "service":
				if ((this.serviceWhitelist != null) && !this.serviceWhitelist.isEmpty()) {
					if (this.serviceWhitelist.contains(s.getFirstParameterValue("service_description", "description"))) {
						s.setParameterIfNotAlreadySet("register", "true");
					} else {
						Configuration.LOG.warn("Not registering service as it is not in the whitelist: " + s.getFirstParameterValue("service_description", "description"));
						s.setParameterIfNotAlreadySet("register", "false");
					}
				} else {
					s.setParameterIfNotAlreadySet("register", "true");
				}

				s.setParamaterProperties("use", StructureArgumentProperty.NOINHERIT);
				s.setParamaterProperties("register", StructureArgumentProperty.NOINHERIT);
				s.setParamaterProperties("name", StructureArgumentProperty.NOINHERIT);
				s.setParamaterProperties("service_description", StructureArgumentProperty.NOINHERIT);
				this.parseStructureService(s);
				break;
			case "command":
				this.parseStructureCommand(s);
				break;
			case "database":
				this.parseStructureDatabase(s);
				break;
			default:
				Configuration.LOG.warn("Unknown structure class: " + clazz);
			}
		}
	}

	private void parseStructureService(Structure structure) {
		String description = structure.getFirstParameterValue("name", "description", "service_description");
		String ccline = structure.getFirstParameterValue("command", "check_command");
		String command = StructureCommand.parseCallCommandExecutable(ccline);
		String identifier = description + ":" + StructureCommand.parseCallCommandExecutable(ccline);

		if (description.isEmpty()) {
			Configuration.LOG.warn("Description for service is empty, wont register.");
			return;
		}

		StructureService service = this.services.get(identifier);

		if (service == null) {
			service = new StructureService();
		} else {
			LOG.info("Updating service: " + identifier);
		}

		service.setDescription(description);
		service.setHostname(structure.getFirstParameterValue("hostname", "host_name"));
		service.setTimerMax(structure.getParameterValueDuration("maxUpdateFrequency", "60s"));
		service.setTimerMin(structure.getParameterValueDuration("minUpdateFrequency", "10s"));
		service.setUpdateIncrement(structure.getParameterValueDuration("successfulUpdateIncrement", "1m"));
		service.setRegistered(structure.getParameterValue("register"));
		service.setTimeout(structure.getParameterValueDuration("timeout", "3s"));

		if (structure.hasParameter("depends_on")) {
			StructureService dependsOn = this.services.get(structure.getParameterValue("depends_on"));

			if (dependsOn == null) {
				Configuration.LOG.warn("While registering service " + service.getIdentifier() + ", could not find it's dependancy: " + structure.getParameterValue("depends_on"));
			} else {
				service.setDependsOn(dependsOn);

				Configuration.LOG.debug("Found service with dependency: " + service.getIdentifier() + " depends on: " + dependsOn.getIdentifier());
			}
		}

		if (service.isRegistered()) {
			StructureCommand cmd = this.commands.get(command);

			service.setCommand(cmd, ccline);

			if (Database.instance != null) {
				this.parseStructureDatabase(service);
				// FIXME if running with no local db, get initial service from
				// peer
			}

			this.services.register(service);

			Configuration.LOG.debug("Parsed service " + service.getDescription() + " with a good count of: " + service.getResultConsequtiveCount());
		} else {
			Configuration.LOG.debug("Ignoring unregistered service: " + service.getDescription());
		}
	}

	private void parseStructureTrust(Structure s) {
		if (!s.hasParameter("fingerprint")) {
			LOG.warn("Trust relationship in configuration without fingerprint field");
			return;
		}

		this.parseTrustFingerprint(s.getParameterValue("fingerprint"));
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

		this.parseRemoteConfig();
	}
}
