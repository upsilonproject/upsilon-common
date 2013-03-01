package upsilon.configuration;

import java.io.File;
import java.io.IOException;
import java.util.Vector;

import org.jdom2.Document;
import org.jdom2.JDOMException;
import org.jdom2.input.SAXBuilder;
import org.jdom2.input.sax.XMLReaderJDOMFactory;
import org.jdom2.input.sax.XMLReaderXSDFactory;
import org.xml.sax.ErrorHandler;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;

public class XmlConfigurationValidator implements ErrorHandler {
    private final Vector<SAXParseException> parseErrors = new Vector<SAXParseException>();
    private final SAXBuilder builder;
    private final File f;
    private final Document d;

    public XmlConfigurationValidator(File f) throws JDOMException, IOException {
        File xsdSchema = new File("src/test/resources/upsilon.xsd");
        XMLReaderJDOMFactory schemaFactory = new XMLReaderXSDFactory(xsdSchema);

        SAXBuilder builder = new SAXBuilder(schemaFactory);
        builder.setErrorHandler(this);

        this.f = f;
        this.builder = builder;

        this.d = this.builder.build(this.f);
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
