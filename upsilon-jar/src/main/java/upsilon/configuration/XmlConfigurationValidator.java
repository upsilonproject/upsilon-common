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

import org.w3c.dom.Document;
import org.xml.sax.ErrorHandler;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;

public class XmlConfigurationValidator implements ErrorHandler {
    private final Vector<SAXParseException> parseErrors = new Vector<SAXParseException>();
    private final DocumentBuilder builder;
    private final File f;
    private final Document d;

    public XmlConfigurationValidator(File f) throws IOException, SAXException, ParserConfigurationException {
        File xsdSchema = new File("src/test/resources/upsilon.xsd");

        Schema s = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI).newSchema(xsdSchema);

        DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
        dbf.setSchema(s);

        this.f = f;
        this.builder = dbf.newDocumentBuilder();
        this.builder.setErrorHandler(this);

        this.d = this.builder.parse(this.f);
    }

    @Override
    public void error(SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }

    @Override
    public void fatalError(SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }

    public Document getDocument() {
        return this.d;
    }

    public Vector<SAXParseException> getParseErrors() {
        return this.parseErrors;
    }

    public boolean isValid() {
        return this.parseErrors.isEmpty();
    }

    @Override
    public void warning(SAXParseException exception) throws SAXException {
        this.parseErrors.add(exception);
    }
}
