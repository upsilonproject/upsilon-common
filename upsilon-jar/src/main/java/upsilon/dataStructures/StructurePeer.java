package upsilon.dataStructures;

import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;

import javax.xml.bind.annotation.XmlRootElement;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.management.rest.client.RestClient;

@XmlRootElement
public class StructurePeer extends ConfigStructure {
    public final static void updateAll() {
        Main.instance.node.refresh();

        for (StructurePeer p : Configuration.instance.peers) {
            p.getClientService().postNode(Main.instance.node);

            for (StructureService s : upsilon.Configuration.instance.services) {
                p.getClientService().postService(s);
            }
        }
           
        Main.instance.node.setPeerUpdateRequired(false);
    }

    private String hostname;

    private int port;
    private URL remoteConfig;

    private static final transient Logger LOG = LoggerFactory.getLogger(StructurePeer.class);

    private RestClient restClient;

    public StructurePeer(String hostname, int port) throws MalformedURLException, IllegalArgumentException, GeneralSecurityException {
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
        return this.getHostname();
    }

    public int getPort() {
        return this.port;
    }

    public URL getRemoteConfig() {
        return this.remoteConfig;
    }

    private void newClient() throws MalformedURLException, IllegalArgumentException, GeneralSecurityException {
        final String proto;
        
        if (Configuration.instance.isCryptoEnabled()) {
            proto = "https";
        } else { 
            proto = "http";
        }
           
        URL serverUrl = new URL(proto + "://" + this.hostname + ":" + this.port);
        this.restClient = new RestClient(serverUrl); 
    }  

    public void setHostname(String hostname) {
        this.hostname = hostname.trim();
    }

    public void setPort(int port) { 
        this.port = port;
    }
    
    public boolean isRemoteConfigProvider() {
        return this.remoteConfig != null; 
    }  

    public void setRemoteConfig(String path) {
        if (path == null || path.isEmpty()) {
            return; 
        }
  
        try {
            this.remoteConfig = new URL("https://" + this.hostname + ":" + this.port + "/remoteConfig/" + path);
        } catch (MalformedURLException e) {
            StructurePeer.LOG.warn("Could not register remote config, malformed url.");
        }
    }
}
