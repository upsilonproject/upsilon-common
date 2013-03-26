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

    protected XmlConfigurationValidator val;

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

    public File getFile() {
        return this.f;
    }

    public XmlConfigurationValidator getValidator() {
        if (this.val.getFile() != this.f) {
            throw new IllegalArgumentException("Validator has expired. It refers to a file that is not current with the loader.");
        }

        return this.val;
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

    private void parseConfiguration(final Document d) throws XPathExpressionException {
        final XPathExpression xpe = XPathFactory.newInstance().newXPath().compile("config/system");
        final NodeList nl = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        if (nl.getLength() == 1) {
            Configuration.instance.update(nl.item(0));
        }
    }

    public void reparse() {
        try {
            this.val = new XmlConfigurationValidator(this.f);
            this.val.validate();
            final Document d = this.val.getDocument();

            XmlConfigurationLoader.LOG.info("Reparse of onfiguration of file {} Validation status: {}", new Object[] { this.f.getAbsolutePath(), this.val.isParseClean() });

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
            }
        } catch (final Exception e) {
            XmlConfigurationLoader.LOG.error("Could not reparse configuration: " + e.getMessage(), e);
        }
    }
}
