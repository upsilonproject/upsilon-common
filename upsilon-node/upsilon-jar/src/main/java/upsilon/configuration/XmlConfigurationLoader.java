package upsilon.configuration;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Vector;

import javax.xml.bind.JAXBException;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpression;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXParseException;

import ch.qos.logback.core.pattern.parser.Node;

import upsilon.Configuration;
import upsilon.dataStructures.CollectionOfStructures;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener {
    private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);
    private File f;

    protected XmlConfigurationValidator val;

    private final Vector<FileChangeWatcher> listWatchers = new Vector<FileChangeWatcher>();
	private boolean remote = false;

    private void buildAndRunConfigurationTransaction(final String xpath, final CollectionOfStructures<?> col, final Document d) throws XPathExpressionException, JAXBException {
        final CollectionAlterationTransaction<?> cat = col.newTransaction();

        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile(xpath);
        final NodeList els = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);
        final Vector<XmlNodeHelper> list = this.parseNodelist(els);
        this.parseNodeParents(list);

        for (final XmlNodeHelper node : list) {
            XmlConfigurationLoader.LOG.trace("xpath result: " + xpath + " = " + node.getNodeName());
            cat.considerFromConfig(node);
        }

        col.processTransaction(cat);
    }

    @Override
    public void fileChanged(final File newFile) {
        this.f = newFile;
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

    public File getFile() {
        return this.f;
    }

    public XmlConfigurationValidator getValidator() {
        if (this.val.getFile() != this.f) {
            throw new IllegalArgumentException("Validator has expired. It refers to a file that is not current with the loader.");
        }

        return this.val;
    }

    public void load() {
        this.load(this.f, true);
    }

    public FileChangeWatcher load(final File f) {
        return this.load(f, true);
    }

    public FileChangeWatcher load(final File f, final boolean watch) {
        this.f = f;
        XmlConfigurationLoader.LOG.info("XMLConfigurationLoader is loading file: " + f);

        final FileChangeWatcher fcw = new FileChangeWatcher(f, this);
        this.listWatchers.add(fcw);

        if (watch) {
            fcw.start();
        }

        this.reparse();

        return fcw;
    }

    private void parseConfiguration(final Document d) throws XPathExpressionException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/system");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        if (nl.getLength() == 1) {
            Configuration.instance.update(new XmlNodeHelper(nl.item(0)));
        }
    }
    
    private void parseRemoteConfiguration(final Document d) throws XPathExpressionException, MalformedURLException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/remoteConfig");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);
        
        Vector<XmlNodeHelper> nodes = parseNodelist(nl);

        for (XmlNodeHelper node : nodes) {
        	String path = node.getAttributeValue("path", "");
        	 
        	LOG.warn("Setting up a new monitor for: " + path);
        	
        	FileChangeWatcher urlWatcher = new FileChangeWatcher(new URL(path), this);
        	urlWatcher.start();   
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

    public void reparse() { 
        try { 
            this.val = new XmlConfigurationValidator(this.f, remote);
            this.val.validate();
            final Document d = this.val.getDocument();

            XmlConfigurationLoader.LOG.info("Reparse of configuration of file {} Validation status: {}", new Object[] { this.f.getAbsolutePath(), this.val.isParseClean() });

            if (!this.val.isParsed()) {
                XmlConfigurationLoader.LOG.warn("Configuration file could not be loaded for parser: " + this.val.getFile().getAbsolutePath());

            } else if (!this.val.isParseClean()) {
                XmlConfigurationLoader.LOG.warn("Configuration file has parse {} errors. It will NOT be reloaded: {}", new Object[] { this.val.getParseErrors().size(), this.val.getFile().getAbsolutePath() });

                for (final SAXParseException e : this.val.getParseErrors()) {
                    XmlConfigurationLoader.LOG.warn("Configuration file parse error: {}:{} - {}", new Object[] { this.val.getFile().getName(), e.getLineNumber(), e.getMessage() });
                }
            } else {
                this.buildAndRunConfigurationTransaction("config/command", Configuration.instance.commands, d);
                this.buildAndRunConfigurationTransaction("config/service", Configuration.instance.services, d);
                this.buildAndRunConfigurationTransaction("config/peer", Configuration.instance.peers, d);
                this.parseConfiguration(d); 
                this.parseRemoteConfiguration(d);
            }
        } catch (final Exception e) {
            XmlConfigurationLoader.LOG.error("Could not reparse configuration: " + e.getMessage(), e);
        }
    }

    public void setFile(final File f) {
        this.f = f;
    }

    public void stopFileWatchers() {
        for (final FileChangeWatcher fcw : this.listWatchers) {
            fcw.stop();
        }
    }
 
	@Override
	public void fileChanged(URL url) {
		LOG.debug("File changed on URL");
		
		try {
			InputStream input = url.openConnection().getInputStream();
			
			File f = File.createTempFile("downloadedConfig", ".xml");
			OutputStream os = new FileOutputStream(f);
			
			int n;
			
			while ((n = input.read()) != -1) {
				os.write(n);
			}
			
			os.close();
			input.close();
			
			this.f = f;
			this.remote  = true;
			this.reparse();
			this.f.delete(); 
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}
