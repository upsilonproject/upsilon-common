package upsilon.configuration;

import java.io.File;
import java.util.List;

import org.jdom2.Document;
import org.jdom2.Element;
import org.jdom2.filter.Filters;
import org.jdom2.xpath.XPathExpression;
import org.jdom2.xpath.XPathFactory;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.xml.sax.SAXParseException;

import upsilon.Configuration;
import upsilon.dataStructures.CollectionOfStructures;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener {
    private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);
    private File f;

    private void buildAndRunConfigurationTransaction(String xpath, CollectionOfStructures<?> col, Document d) {
        CollectionAlterationTransaction<?> cat = col.newTransaction();

        XPathExpression<Element> xpe = XPathFactory.instance().compile(xpath, Filters.element());
        List<Element> els = xpe.evaluate(d);

        LOG.debug("Number of elements found with xpath:" + xpath + " = " + els.size());

        for (Element el : els) {
            LOG.debug("xpath result: " + xpath + " = " + el);
            cat.considerFromConfig(el);
        }

        cat.print();
        col.processTransaction(cat);
    }

    @Override
    public void fileChanged(File f) {
        this.reparse();
    }

    public FileChangeWatcher load(File f) {
        return this.load(f, true);
    }

    public FileChangeWatcher load(File f, boolean watch) {
        this.f = f;
        LOG.info("XMLConfigurationLoader is loading file: " + f);

        FileChangeWatcher fcw = new FileChangeWatcher(f, this);

        if (watch) {
            fcw.start();
        }

        return fcw;
    }

    private void reparse() {
        try {
            XmlConfigurationValidator val = new XmlConfigurationValidator(this.f);
            Document d = val.getDocument();

            LOG.debug("Configuration reparsed. Validation status: " + val.isValid());

            if (val.isValid()) {
                this.buildAndRunConfigurationTransaction("config/service", Configuration.instance.services, d);
            } else {
                for (SAXParseException e : val.getParseErrors()) {
                    LOG.warn("Parse error: " + e);
                }
            }
        } catch (Exception e) {
            LOG.error("Could not reparse configuration.", e);
        }
    }
}
