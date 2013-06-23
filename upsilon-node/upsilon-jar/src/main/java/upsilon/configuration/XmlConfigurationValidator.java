package upsilon.configuration;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.net.CookieManager;
import java.net.CookiePolicy;
import java.net.CookieStore;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.Vector;

import javax.xml.XMLConstants;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.transform.stream.StreamSource;
import javax.xml.validation.Schema;
import javax.xml.validation.SchemaFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.xml.sax.ErrorHandler;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;

import upsilon.util.Path;
import upsilon.util.ResourceResolver;

public class XmlConfigurationValidator implements ErrorHandler {
    private final Vector<SAXParseException> parseErrors = new Vector<SAXParseException>();
    private final DocumentBuilder builder;
    private Document d;
    private boolean isParsed = false;
	private boolean isAux = false;

    private static final Logger LOG = LoggerFactory.getLogger(XmlConfigurationValidator.class);
    
    static {   
    	CookieManager.setDefault(new CookieManager(null, CookiePolicy.ACCEPT_ALL));
    }

    public XmlConfigurationValidator(final Path u, boolean isAux) throws Exception {
    	this.isAux = isAux; 
    	
        final InputStream xsdSchemaStream = selectSchema();
        final StreamSource xsdSchema = new StreamSource(xsdSchemaStream);

        final Schema s = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI).newSchema(xsdSchema);

        final DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
        dbf.setSchema(s);
  
        this.path = u;  
        this.builder = dbf.newDocumentBuilder();
        this.builder.setErrorHandler(this);
    }
        
    private InputStream selectSchema() throws Exception {
    	if (this.isAux) {  
    		return ResourceResolver.getInstance().getInternalFromFilename("upsilon.aux.xsd");
    	} else {
    		return ResourceResolver.getInstance().getInternalFromFilename("upsilon.xsd");
    	}
    } 

    @Override
    public void error(final SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }

    @Override
    public void fatalError(final SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }

    public Document getDocument() {
        return this.d;
    }

    public Vector<SAXParseException> getParseErrors() {
        return this.parseErrors;
    }

    public boolean isParseClean() {
        return this.isParsed && this.parseErrors.isEmpty();
    }

    public boolean isParsed() {
        return this.isParsed;
    }

    public void validate() throws IOException, SAXParseException, SAXException, URISyntaxException {
        if (!this.path.exists()) {
            XmlConfigurationValidator.LOG.warn("Wont parse non existant configuration file: " + this.path);
        } else if (!this.path.isFile()) {
            XmlConfigurationValidator.LOG.warn("Wont parse thing on filesystem, it does not look like a file: " + this.path);
        } else {   
	    	InputSource is = new InputSource(path.getInputStream());
	    	   
	        this.d = this.builder.parse(is);
	        this.isParsed = true;
        }
    }

    @Override
    public void warning(final SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }
     
    public boolean isAux() {
    	return isAux; 
    }

	public Path getPath() {
		return this.path; 
	}  
	 
	private Path path; 
}
