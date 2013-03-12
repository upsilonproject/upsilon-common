package upsilon.configuration;

import java.io.File;

import javax.xml.bind.JAXBException;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpression;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXParseException;

import upsilon.Configuration;
import upsilon.dataStructures.CollectionOfStructures;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener {
    private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);
    private File f;

    private void buildAndRunConfigurationTransaction(final String xpath, final CollectionOfStructures<?> col, final Document d) throws XPathExpressionException, JAXBException {
        final CollectionAlterationTransaction<?> cat = col.newTransaction();

        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile(xpath);
        final NodeList els = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        for (int i = 0; i < els.getLength(); i++) {
            final Node el = els.item(i);
            XmlConfigurationLoader.LOG.trace("xpath result: " + xpath + " = " + el);
            cat.considerFromConfig(el);
        }

        col.processTransaction(cat);
    }

    @Override
    public void fileChanged(final File newFile) {
        this.f = newFile;
        this.reparse();
    }

    public FileChangeWatcher load(final File f) {
        return this.load(f, true);
    }

    public FileChangeWatcher load(final File f, final boolean watch) {
        this.f = f;
        XmlConfigurationLoader.LOG.info("XMLConfigurationLoader is loading file: " + f);

        final FileChangeWatcher fcw = new FileChangeWatcher(f, this);

        if (watch) {
            fcw.start();
        }

        this.reparse();

        return fcw;
    }

    public void reparse() {
        try {
            final XmlConfigurationValidator val = new XmlConfigurationValidator(this.f);
            final Document d = val.getDocument();

            XmlConfigurationLoader.LOG.debug("Configuration of file {} Validation status: {}", new Object[] { this.f.getAbsolutePath(), val.isValid() });

            if (val.isValid()) {
                this.buildAndRunConfigurationTransaction("config/command", Configuration.instance.commands, d);
                this.buildAndRunConfigurationTransaction("config/service", Configuration.instance.services, d);
            } else {
                for (final SAXParseException e : val.getParseErrors()) {
                    XmlConfigurationLoader.LOG.warn("Parse error: " + e);
                }
            }
        } catch (final Exception e) {
            XmlConfigurationLoader.LOG.error("Could not reparse configuration: " + e.getMessage(), e);
        }
    }
}
