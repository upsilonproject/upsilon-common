package upsilon.configuration;

import java.io.File;
import java.io.IOException;
import java.util.Vector;

import javax.xml.XMLConstants;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.validation.Schema;
import javax.xml.validation.SchemaFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.xml.sax.ErrorHandler;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;

public class XmlConfigurationValidator implements ErrorHandler {
    private final Vector<SAXParseException> parseErrors = new Vector<SAXParseException>();
    private final DocumentBuilder builder;
    private final File f;
    private Document d;
    private boolean isParsed = false;

    private static final Logger LOG = LoggerFactory.getLogger(XmlConfigurationValidator.class);

    public XmlConfigurationValidator(final File f) throws ParserConfigurationException, SAXException {
        final File xsdSchema = new File("src/test/resources/upsilon.xsd");

        final Schema s = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI).newSchema(xsdSchema);

        final DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
        dbf.setSchema(s);

        this.f = f;
        this.builder = dbf.newDocumentBuilder();
        this.builder.setErrorHandler(this);
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

    public File getFile() {
        return this.f;
    }

    public Vector<SAXParseException> getParseErrors() {
        return this.parseErrors;
    }

    public boolean isParseClean() {
        return this.parseErrors.isEmpty();
    }

    public boolean isParsed() {
        return this.isParsed;
    }

    public void validate() throws IOException, SAXException {
        if (!this.f.exists()) {
            XmlConfigurationValidator.LOG.warn("Wont parse non existant configuration file: " + this.f.getAbsolutePath());
        } else if (!this.f.isFile()) {
            XmlConfigurationValidator.LOG.warn("Wont parse thing on filesystem, it does not look like a file: " + this.f.getAbsolutePath());
        } else {
            this.d = this.builder.parse(this.f);
            this.isParsed = true;
        }
    }

    @Override
    public void warning(final SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }
}
