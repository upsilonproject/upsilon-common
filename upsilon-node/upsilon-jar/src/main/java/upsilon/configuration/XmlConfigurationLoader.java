package upsilon.configuration;

import java.io.File;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.util.Vector;

import javax.xml.bind.JAXBException;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpression;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXParseException;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.util.UPath;
import upsilon.util.Util;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener, DirectoryWatcher.Listener {
	@XmlRootElement(name = "config")
	public static class ConfigStatus {
		private UPath path;

		@XmlElement
		public boolean isParsed = false;

		@XmlElement
		public boolean isParseClean = false;

		@XmlElement
		public boolean isAux = false;

		@XmlElementWrapper(name = "parseErrors")
		@XmlElement(name = "error")
		private final Vector<String> stringParseErrors = new Vector<String>();

		public void clearParseErrors() {
			this.stringParseErrors.clear();
		}

		@XmlElement
		public String getPath() {
			return this.path.toString();
		}

		public void setParseErrors(Vector<SAXParseException> parseErrors) {
			for (SAXParseException e : parseErrors) {
				this.stringParseErrors.add("Line: " + e.getLineNumber() + " Message: " + e.getMessage());
			}
		}
	}

	private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);

	@XmlElement
	public static Vector<ConfigStatus> configFileStatuses = new Vector<ConfigStatus>();

	protected XmlConfigurationValidator val;

	private final Vector<FileChangeWatcher> listWatchers = new Vector<FileChangeWatcher>();

	private UPath path;

	private void buildAndRunConfigurationTransaction(final String xpath, final CollectionOfStructures<?> col, final Document d) throws XPathExpressionException, JAXBException {
		String sourceTag = this.getSourcetag();

		final CollectionAlterationTransaction<?> cat = col.newTransaction(sourceTag);

		final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile(xpath);
		final NodeList els = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);
		final Vector<XmlNodeHelper> list = this.parseNodelist(els);
		this.parseNodeParents(list);

		for (final XmlNodeHelper node : list) {
			XmlConfigurationLoader.LOG.trace("Node found. xpath: " + xpath + ". name: " + node.getNodeName());
			node.setSource(sourceTag);

			cat.considerFromConfig(node);
		}

		cat.print();
		col.processTransaction(cat);
	}

	@Override
	public void fileChanged(UPath url) {
		this.path = url;

		ConfigStatus configStatus = this.getConfigStatus(this.path);
		configStatus.isParseClean = false;

		this.reparse();
	}

	private void findParent(final XmlNodeHelper node, final Vector<XmlNodeHelper> linage, final Vector<XmlNodeHelper> availableNodes) {
		if (node.hasAttribute("parent")) {
			final String parentId = node.getAttributeValueUnchecked("parent");

			for (final XmlNodeHelper search : availableNodes) {
				if (search.getAttributeValueUnchecked("id").equals(parentId)) {
					node.setParent(search);

					this.findParent(search, linage, availableNodes);

					return;
				}
			}
		}
	}

	private ConfigStatus getConfigStatus(UPath path) {
		for (ConfigStatus cs : configFileStatuses) {
			if (cs.path == path) {
				return cs;
			}
		}

		ConfigStatus newStatus = new ConfigStatus();
		newStatus.path = path;
		configFileStatuses.add(newStatus);

		return newStatus;
	}

	private String getSourcetag() {
		String file = this.path.getFilename();

		return file.substring(0, file.indexOf("."));
	}

	public Vector<ConfigStatus> getStatuses() {
		return configFileStatuses;
	}

	public UPath getUrl() {
		return this.path;
	}

	public XmlConfigurationValidator getValidator() {
		if (this.val.getPath() != this.path) {
			throw new IllegalArgumentException("Validator has expired. It refers to a file that is not current with the loader.");
		}

		return this.val;
	}

	private boolean isAuxConfig() {
		return !this.path.getFilename().startsWith("config.");
	}

	public void load() {
		this.load(this.path, true);
	}

	public FileChangeWatcher load(final UPath u) {
		return this.load(u, true);
	}

	public FileChangeWatcher load(UPath path, final boolean watch) {
		LOG.info("XMLConfigurationLoader is loading file: " + path);

		this.path = path;

		FileChangeWatcher fcw = new FileChangeWatcher(path, this);
		this.listWatchers.add(fcw);

		this.reparse();

		if (watch) {
			fcw.start();
		}

		return fcw;
	}

	@Override
	public void onNewFile(File f) {
		try {
			Main.instance.getXmlConfigurationLoader().load(new UPath(f), true);
		} catch (Exception e) {
			LOG.warn("Informed of new file in a directory, but the configuration loader encountered an exception: " + e.getMessage());
		}
	}

	private void parseIncludedConfiguration(final Document d) throws XPathExpressionException, MalformedURLException, URISyntaxException {
		final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/include");
		final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

		Vector<XmlNodeHelper> nodes = this.parseNodelist(nl);

		for (XmlNodeHelper node : nodes) {
			UPath path = new UPath(node.getAttributeValue("path", ""));

			if (!path.isAbsolute()) {
				LOG.warn("Path is not absolute. Relative paths should not be used: " + path);
			} else {
				if (DirectoryWatcher.canMonitor(path)) {
					new DirectoryWatcher(path, this);
				} else if (FileChangeWatcher.isAlreadyMonitoring(path)) {
					LOG.info("Already monitoring included configuration file: " + path);
				} else {
					LOG.info("Loading included configuration file: " + path);

					this.load(path, true);
				}
			}
		}
	}

	private Vector<XmlNodeHelper> parseNodelist(final NodeList nl) {
		final Vector<XmlNodeHelper> list = new Vector<XmlNodeHelper>();

		for (int i = 0; i < nl.getLength(); i++) {
			list.add(new XmlNodeHelper(nl.item(i)));
		}

		return list;
	}

	private void parseNodeParents(final Vector<XmlNodeHelper> list) {
		final Vector<XmlNodeHelper> linage = new Vector<XmlNodeHelper>();

		for (final XmlNodeHelper node : list) {
			this.findParent(node, linage, list);
			linage.clear();
		}
	}

	private void parseSystem(final Document d) throws XPathExpressionException {
		final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/system");
		final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

		if (nl.getLength() == 1) {
			Configuration.instance.update(new XmlNodeHelper(nl.item(0)));
		}
	}

	private void parseTrusts(Document d) throws XPathExpressionException {
		final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/trust");
		final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

		Vector<XmlNodeHelper> nodes = this.parseNodelist(nl);

		for (XmlNodeHelper node : nodes) {
			String fingerprint = node.getAttributeValueUnchecked("certSha1Fingerprint");

			Configuration.instance.parseTrustFingerprint(fingerprint);
		}
	}

	public synchronized void reparse() {
		ConfigStatus configStatus = this.getConfigStatus(this.path);

		try {
			this.val = new XmlConfigurationValidator(this.path, this.isAuxConfig());
			this.val.validate();
			final Document d = this.val.getDocument();

			XmlConfigurationLoader.LOG.info("Reparse of configuration of file {}. Schema: {}. Validation status: {}", new Object[] { this.val.getPath(), Util.bool2s(this.val.isAux(), "AUX", "MAIN"), Util.bool2s(this.val.isParseClean(), "VALID", "INVALID") });
			configStatus.isAux = this.val.isAux();
			configStatus.isParsed = this.val.isParsed();
			configStatus.isParseClean = this.val.isParseClean();
			configStatus.clearParseErrors();

			if (!this.val.isParsed()) {
				XmlConfigurationLoader.LOG.warn("Configuration file could not be loaded for parser: " + this.val.getPath());
			} else if (!this.val.isParseClean()) {
				XmlConfigurationLoader.LOG.warn("Configuration file has parse {} errors. It will NOT be reloaded: {}", new Object[] { this.val.getParseErrors().size(), this.val.getPath() });
				configStatus.setParseErrors(this.val.getParseErrors());

				for (final SAXParseException e : this.val.getParseErrors()) {
					XmlConfigurationLoader.LOG.warn("Configuration file parse error: {}:{} - {}", new Object[] { this.val.getPath(), e.getLineNumber(), e.getMessage() });
				}
			} else {
				this.parseTrusts(d);
				this.parseSystem(d);
				this.parseIncludedConfiguration(d);
				this.buildAndRunConfigurationTransaction("config/command", Configuration.instance.commands, d);
				this.buildAndRunConfigurationTransaction("config/service", Configuration.instance.services, d);
				this.buildAndRunConfigurationTransaction("config/peer", Configuration.instance.peers, d);
			}
		} catch (final Exception e) {
			XmlConfigurationLoader.LOG.error("Could not reparse configuration: " + e.getMessage(), e);
		}
	}

	public void setUrl(UPath path) {
		this.path = path;
	}

	public void stopFileWatchers() {
		synchronized (this.listWatchers) {
			for (final FileChangeWatcher fcw : this.listWatchers) {
				fcw.stop();
			}
		}
	}
}
