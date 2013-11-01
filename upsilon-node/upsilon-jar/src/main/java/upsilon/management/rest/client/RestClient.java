package upsilon.management.rest.client;

import java.net.URL;
import java.security.GeneralSecurityException;

import javax.ws.rs.core.HttpHeaders;
import javax.ws.rs.core.MediaType;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Main;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;
import upsilon.util.SslUtil;

import com.sun.jersey.api.client.Client;
import com.sun.jersey.api.client.ClientHandlerException;
import com.sun.jersey.api.client.ClientRequest;
import com.sun.jersey.api.client.ClientResponse;
import com.sun.jersey.api.client.UniformInterfaceException;
import com.sun.jersey.api.client.WebResource;
import com.sun.jersey.api.client.config.ClientConfig;
import com.sun.jersey.api.client.config.DefaultClientConfig;
import com.sun.jersey.api.client.filter.ClientFilter;
import com.sun.jersey.client.urlconnection.HTTPSProperties;

public class RestClient {
	private final URL url;
	private final Client client;

	private static final Logger LOG = LoggerFactory.getLogger(RestClient.class);

	public RestClient(final URL uri) throws IllegalArgumentException, GeneralSecurityException {
		this.url = uri;

		RestClient.LOG.info("Creating new REST client: " + uri);

		if (uri.getPort() == 0) {
			throw new IllegalArgumentException("The port for the remote host URL in a rest client is not valid: " + uri.getPort());
		}

		if (uri.getHost().isEmpty()) {
			throw new IllegalArgumentException("The host part for the remote host URL in a rest client is not valid: " + uri.getHost());
		}

		final ClientConfig config = new DefaultClientConfig();
		config.getProperties().put(HTTPSProperties.PROPERTY_HTTPS_PROPERTIES, new HTTPSProperties(SslUtil.getInsecureHostnameVerifier(), SslUtil.getContext()));

		this.client = Client.create(config);
		this.client.addFilter(new ClientFilter() {
			@Override
			public ClientResponse handle(final ClientRequest cr) throws ClientHandlerException {
				cr.getHeaders().add(HttpHeaders.USER_AGENT, "Upsilon " + Main.getVersion());

				return this.getNext().handle(cr);
			}
		});
	}

	protected WebResource getNewResouce() {
		return this.client.resource(this.url.toString());
	}

	public StructureService getService(final String identifier) {
		return this.getNewResouce().path("/services/id/" + identifier).get(StructureService.class);
	}

	public URL getUrl() {
		return this.url;
	}

	public void postNode(final StructureNode node) {
		if (!node.isPeerUpdateRequired()) {
			return;
		}

		RestClient.LOG.debug("Peer update required, posting node: " + node);

		try {
			this.getNewResouce().path("/nodes/update/").entity(node, MediaType.APPLICATION_XML).post();
		} catch (final UniformInterfaceException e) {
			RestClient.LOG.warn("Failed to push node to path: " + e.getResponse() + ", client response: " + e.getResponse().getEntity(String.class) + ", exception: " + e, e);
		} catch (final Exception e) {
			RestClient.LOG.warn("Failed to push node to path: " + e.getClass().getSimpleName(), e);
		}
	}

	public void postService(final StructureService s) {
		if (!s.isRegistered()) {
			return;
		}

		if (!s.isPeerUpdateRequired()) {
			return;
		}

		final StructureRemoteService srs = new StructureRemoteService();
		srs.setNodeIdentifier(Main.instance.node.getIdentifier());
		srs.setKarmaString(s.getKarmaString());
		srs.setOutput(s.getOutput());
		srs.setIdentifier(s.getIdentifier());
		srs.setDescription(s.getDescription());
		srs.setLastUpdated(s.getLastUpdated());
		srs.setEstimatedNextCheck(s.getEstimatedNextCheck());
		srs.setExecutable(s.getExecutable());
		srs.setFinalCommandLine(s.getFinalCommandLine(s));
		srs.setResultConsequtiveCount(s.getFlexiTimer().getConsequtiveCount());
		srs.setLastChanged(s.getLastChanged());
		srs.setCommandIdentifier(s.getCommand().getIdentifier());

		RestClient.LOG.debug("Pushing service: " + s.getIdentifier() + " to: " + this.url);

		try {
			this.getNewResouce().path("/services/updateRemoteService/").entity(srs, MediaType.APPLICATION_XML).post();
		} catch (final Exception e) {
			// don't include the exception in this log message, it is very long.
			RestClient.LOG.warn("Failed to post service: " + e.getMessage() + ". Exception available in TRACE.");
			RestClient.LOG.trace("Failed to post service (cont): ", e);
		}

		s.setPeerUpdateRequired(false);
	}
}
