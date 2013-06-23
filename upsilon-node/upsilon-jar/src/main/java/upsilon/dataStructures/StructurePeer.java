package upsilon.dataStructures;

import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;

import javax.xml.bind.annotation.XmlRootElement;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.configuration.XmlNodeHelper;
import upsilon.management.rest.client.RestClient;
import upsilon.util.GlobalConstants;

@XmlRootElement
public class StructurePeer extends ConfigStructure {
    public final static void updateAll() {
        Main.instance.node.refresh();

        for (final StructurePeer p : Configuration.instance.peers) {
            final RestClient clientService = p.getClientService();

            if (clientService == null) {
                StructurePeer.LOG.warn("Peer does not have a client service, it possibly has not been initialized yet: " + p.getIdentifier());
            } else {
                clientService.postNode(Main.instance.node);

                for (final StructureService s : upsilon.Configuration.instance.services) {
                    clientService.postService(s);
                }
            }
        }

        Main.instance.node.setPeerUpdateRequired(false);
    }

    private String hostname;

    private int port = 4000;

    private static final transient Logger LOG = LoggerFactory.getLogger(StructurePeer.class);

    private RestClient restClient;

    private String identifier;

    public StructurePeer() {
    }

    public StructurePeer(final String hostname, final int port) throws MalformedURLException, IllegalArgumentException, GeneralSecurityException {
        this.hostname = hostname;
        this.port = port;

        this.newClient();
    }

    public RestClient getClientService() {
        return this.restClient;
    }

    public String getHostname() {
        return this.hostname;
    }

    @Override
    public String getIdentifier() {
        return this.identifier;
    }

    public int getPort() {
        return this.port;
    }

    private void newClient() throws MalformedURLException, IllegalArgumentException, GeneralSecurityException {
        if (this.restClient == null) {
            final String proto;

            if (Configuration.instance.isCryptoEnabled) {
                proto = "https";
            } else {
                proto = "http";
            }

            final URL serverUrl = new URL(proto + "://" + this.hostname + ":" + this.port);
            this.restClient = new RestClient(serverUrl);
        }
    }

    public void setHostname(final String hostname) {
        this.hostname = hostname.trim();
    }

    public void setPort(final int port) {
        this.port = port;
    }
 
    @Override
    public void update(final XmlNodeHelper xmlNode) {
        this.identifier = xmlNode.getAttributeValueUnchecked("id");

        if (this.getHostname() == null) {
            this.setHostname(xmlNode.getAttributeValueUnchecked("address"));
            this.setPort(xmlNode.getAttributeValue("port", GlobalConstants.DEF_REST_PORT));

            Configuration.instance.parseTrustFingerprint(xmlNode.getAttributeValueOrDefault("certSha1Fingerprint", ""));

            try {
                this.newClient();
            } catch (final Exception e) {
                StructurePeer.LOG.warn("Could not set up REST Client: " + e.getMessage());
            }
        } else {
            StructurePeer.LOG.warn("Peer connections cannot be online updated.");
        }
    }
}
