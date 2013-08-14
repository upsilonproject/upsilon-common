package upsilon.configuration;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.net.URL;
import java.nio.file.DirectoryStream;
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
 
import upsilon.Configuration;
import upsilon.Main;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.util.UPath;
import upsilon.util.Util;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener, DirectoryWatcher.Listener {
    private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);
 
    protected XmlConfigurationValidator val;

    private final Vector<FileChangeWatcher> listWatchers = new Vector<FileChangeWatcher>();
    
    private UPath path;
    
    public void setUrl(UPath path) {  
    	this.path = path;  
    }  
    
    private String getSourcetag() {
    	String file = path.getFilename();
    	 
    	return file.substring(0, file.indexOf("."));
    }
    
	private void buildAndRunConfigurationTransaction(final String xpath, final CollectionOfStructures<?> col, final Document d) throws XPathExpressionException, JAXBException {
		String sourceTag = getSourcetag();  
		   
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

    public XmlConfigurationValidator getValidator() {
        if (this.val.getPath() != this.path) {
            throw new IllegalArgumentException("Validator has expired. It refers to a file that is not current with the loader.");
        } 

        return this.val;
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

    private void parseSystem(final Document d) throws XPathExpressionException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/system");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        if (nl.getLength() == 1) {
            Configuration.instance.update(new XmlNodeHelper(nl.item(0)));
        }
    }
    
    private void parseIncludedConfiguration(final Document d) throws XPathExpressionException, MalformedURLException, URISyntaxException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/include");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);
        
        Vector<XmlNodeHelper> nodes = parseNodelist(nl);
 
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
    
    private boolean isAuxConfig() {    	  
    	return !path.getFilename().startsWith("config.");
    } 

    public synchronized void reparse() { 
        try {      
            this.val = new XmlConfigurationValidator(this.path, isAuxConfig());
            this.val.validate();    
            final Document d = this.val.getDocument();
  
            XmlConfigurationLoader.LOG.info("Reparse of configuration of file {}. Schema: {}. Validation status: {}", new Object[] { this.val.getPath(), Util.bool2s(val.isAux(), "AUX", "MAIN"), Util.bool2s(this.val.isParseClean(), "VALID", "INVALID") }); 

            if (!this.val.isParsed()) { 
                XmlConfigurationLoader.LOG.warn("Configuration file could not be loaded for parser: " + this.val.getPath());
            } else if (!this.val.isParseClean()) {
                XmlConfigurationLoader.LOG.warn("Configuration file has parse {} errors. It will NOT be reloaded: {}", new Object[] { this.val.getParseErrors().size(), this.val.getPath() });

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
    
    private void parseTrusts(Document d) throws XPathExpressionException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/trust");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);
        
        Vector<XmlNodeHelper> nodes = parseNodelist(nl); 
  
        for (XmlNodeHelper node : nodes) {
        	String fingerprint = node.getAttributeValueUnchecked("certSha1Fingerprint");
        	 
        	Configuration.instance.parseTrustFingerprint(fingerprint);
        }
    }

    public void stopFileWatchers() {
    	synchronized (this.listWatchers) {
	        for (final FileChangeWatcher fcw : this.listWatchers) {
	            fcw.stop();
	        }
    	} 
    }
 
	@Override
	public void fileChanged(UPath url) {
		this.path = url;  
		this.reparse();  
	}
	   
	public UPath getUrl() {
		return path; 
	}

	@Override
	public void onNewFile(File f) {  
		try {
			Main.instance.getXmlConfigurationLoader().load(new UPath(f), true);
		} catch (Exception e) { 
			LOG.warn("Informed of new file in a directory, but the configuration loader encountered an exception: " + e.getMessage());
		}
	}
}
