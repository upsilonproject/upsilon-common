package upsilon.configuration;

import java.io.File;

import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Unmarshaller;
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

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public class XmlConfigurationLoader implements FileChangeWatcher.Listener {
    private static final transient Logger LOG = LoggerFactory.getLogger(XmlConfigurationLoader.class);
    private File f;

    private void buildAndRunConfigurationTransaction(String xpath, CollectionOfStructures<?> col, Document d) throws XPathExpressionException, JAXBException {
        CollectionAlterationTransaction<?> cat = col.newTransaction();

        XPathExpression xpe = XPathFactory.newInstance().newXPath().compile(xpath);
        NodeList els = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        for (int i = 0; i < els.getLength(); i++) {
            Node el = els.item(i);
            LOG.debug("xpath result: " + xpath + " = " + el);
            cat.considerFromConfig(el);
        }

        cat.print();
        col.processTransaction(cat);
    }

    private void buildElements(String xpath, Document d) throws XPathExpressionException, JAXBException {
        XPathExpression xpe = XPathFactory.newInstance().newXPath().compile(xpath);
        NodeList els = (NodeList) xpe.evaluate(d, XPathConstants.NODESET);

        // JAXBContext jaxbContext =
        // JAXBContext.newInstance(ConfigStructure.class.getPackage().toString());
        JAXBContext jaxbContext = JAXBContext.newInstance(StructureService.class);
        Unmarshaller um = jaxbContext.createUnmarshaller();

        LOG.warn("Elements: " + els.getLength());

        for (int i = 0; i < els.getLength(); i++) {
            Node el = els.item(i);
            LOG.debug("xpath result: " + xpath + " = " + el);

            StructureService ss = (StructureService) um.unmarshal(el);

            LOG.warn(ss.getIdentifier());
        }
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

        this.reparse();

        return fcw;
    }

    public void reparse() {
        try {
            XmlConfigurationValidator val = new XmlConfigurationValidator(this.f);
            Document d = val.getDocument();

            LOG.debug("Configuration reparsed. Validation status: " + val.isValid());

            if (val.isValid()) {
                // this.buildAndRunConfigurationTransaction("config/service",
                // Configuration.instance.services, d);
                this.buildElements("config/service", d);
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
