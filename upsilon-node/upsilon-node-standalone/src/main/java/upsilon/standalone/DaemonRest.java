package upsilon.standalone;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.net.URI;
import java.security.UnrecoverableKeyException;

import javax.ws.rs.core.UriBuilder;

import org.glassfish.grizzly.http.server.HttpHandler;
import org.glassfish.grizzly.http.server.HttpServer;
import org.glassfish.grizzly.http.server.NetworkListener;
import org.glassfish.grizzly.http.server.ServerConfiguration;
import org.glassfish.grizzly.ssl.SSLContextConfigurator;
import org.glassfish.grizzly.ssl.SSLEngineConfigurator;
import org.glassfish.grizzly.threadpool.ThreadPoolConfig;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.bridge.SLF4JBridgeHandler;

import upsilon.Configuration;
import upsilon.Daemon;
import upsilon.util.ResourceResolver;

import com.sun.jersey.api.container.ContainerFactory;
import com.sun.jersey.api.core.PackagesResourceConfig;
import com.sun.jersey.api.core.ResourceConfig;

public class DaemonRest extends Daemon {
	public static URI getBaseUri() {
		final String proto;

		if (Configuration.instance.isCryptoEnabled) {
			proto = "https";
		} else {
			proto = "http";
		}

		return UriBuilder.fromUri(proto + "://0.0.0.0/").port(Configuration.instance.restPort).build();
	}

	private final Logger log = LoggerFactory.getLogger(DaemonRest.class);
	private HttpServer server;

	private HttpServer createServer() throws IllegalArgumentException, NullPointerException, IOException {
		this.setStatus("Going to try and start webserver at: " + DaemonRest.getBaseUri());

		final URI baseUri = DaemonRest.getBaseUri();

		final String host = (baseUri.getHost() == null) ? NetworkListener.DEFAULT_NETWORK_HOST : baseUri.getHost();
		final int port = (baseUri.getPort() == -1) ? 80 : baseUri.getPort();

		final HttpServer server = new HttpServer();
		final NetworkListener listener = new NetworkListener("grizzly", host, port);
		listener.getTransport().setKernelThreadPoolConfig(this.getKernelThreadPoolConfig());
		listener.getTransport().setSelectorRunnersCount(Configuration.instance.maxThreadsRestKernel);
		listener.getTransport().setWorkerThreadPoolConfig(this.getWorkerThreadPoolConfig());

		if (Configuration.instance.isCryptoEnabled) {
			listener.setSecure(true);
			listener.setSSLEngineConfig(this.getSslEngineConfig());
		} else {
			listener.setSecure(false);
		}

		server.addListener(listener);

		final ResourceConfig rc = new PackagesResourceConfig("upsilon.management.rest");
		final HttpHandler httpHandler = ContainerFactory.createContainer(HttpHandler.class, rc);
		final ServerConfiguration config = server.getServerConfiguration();
		config.addHttpHandler(httpHandler, baseUri.getPath());
		config.setHttpServerName("upsilon node");

		return server;
	}

	private ThreadPoolConfig getKernelThreadPoolConfig() {
		final ThreadPoolConfig config = ThreadPoolConfig.defaultConfig().copy();
		config.setPoolName("Rest Server Selector Runner");
		config.setCorePoolSize(1);
		config.setMaxPoolSize(Configuration.instance.maxThreadsRestKernel);

		return config;
	}

	private SSLEngineConfigurator getSslEngineConfig() throws IllegalArgumentException {
		final File keyStore = new File(ResourceResolver.getInstance().getConfigDir(), "keyStore.jks");
		final File trustStore = new File(ResourceResolver.getInstance().getConfigDir(), "trustStore.jks");

		this.log.trace(String.format("ks path: %s, exists: %s, password specified: %s", keyStore.getAbsolutePath(), keyStore.exists(), !Configuration.instance.passwordKeystore.isEmpty()));
		this.log.trace(String.format("ts path: %s, exists: %s, password specified: %s", trustStore.getAbsolutePath(), trustStore.exists(), !Configuration.instance.passwordTrustStore.isEmpty()));

		final SSLContextConfigurator sslContextConfigurator = new SSLContextConfigurator();
		sslContextConfigurator.setKeyStoreFile(keyStore.getAbsolutePath());
		sslContextConfigurator.setKeyStorePass(Configuration.instance.passwordKeystore);
		sslContextConfigurator.setTrustStoreFile(trustStore.getAbsolutePath());
		sslContextConfigurator.setTrustStorePass(Configuration.instance.passwordTrustStore);

		try {
			if (!sslContextConfigurator.validateConfiguration(true)) {
				throw new IllegalArgumentException("SSL Configuration validity in DaemonRest is invalid for some reason.");
			} else {
				this.log.info("SSL Engine configuration was sucessfully validated.");
			}
		} catch (final Exception e) {
			if (e.getCause() instanceof UnrecoverableKeyException) {
				throw new IllegalArgumentException("Could not open keystores, possibly due to a unset or incorrect password.", e);
			}

			throw e;
		}

		final SSLEngineConfigurator engineConfig = new SSLEngineConfigurator(sslContextConfigurator);
		engineConfig.setClientMode(false);
		engineConfig.setNeedClientAuth(false);
		engineConfig.setEnabledCipherSuites(new String[] { "TLS_DHE_RSA_WITH_AES_256_CBC_SHA" });
		engineConfig.setEnabledProtocols(new String[] { "TLSv1" });

		return engineConfig;
	}

	private ThreadPoolConfig getWorkerThreadPoolConfig() {
		final ThreadPoolConfig config = ThreadPoolConfig.defaultConfig().copy();
		config.setPoolName("Rest Server Worker");
		config.setCorePoolSize(1);
		config.setMaxPoolSize(Configuration.instance.maxThreadsRestWorkers);

		return config;
	}

	@Override
	public void run() {
		SLF4JBridgeHandler.removeHandlersForRootLogger();
		SLF4JBridgeHandler.install();

		try {
			this.server = this.createServer();
			this.server.start();

			this.setStatus("Server started at: " + DaemonRest.getBaseUri());
			this.log.debug("Server started at: " + DaemonRest.getBaseUri());
		} catch (final FileNotFoundException e) {
			this.log.warn("Essential file was not found when starting the REST Daemon: " + e.getMessage() + ". This is probably your certificate keystore. Please follow installation instructions. The REST server will be unavailble until the service is restarted.");
		} catch (IOException | IllegalArgumentException e) {
			this.log.warn(e.getClass().getSimpleName() + " when starting REST Daemon. The REST interface will be unavailable. Cause: " + e.getMessage(), e);
		}
	}

	@Override
	public void stop() {
		if (this.server != null) {
			this.server.stop();
		}

		this.setStatus("stopped");
	}
}
