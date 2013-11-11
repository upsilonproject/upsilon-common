package upTests;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.endsWith;
import static org.hamcrest.Matchers.instanceOf;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;

import junit.framework.Assert;

import org.junit.AfterClass;
import org.junit.BeforeClass;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.DaemonRest;
import upsilon.Main;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructureService;
import upsilon.management.rest.client.RestClient;
import upsilon.util.SslUtil;
import upsilon.util.Util;

public class HttpServerAndClientTest {
	private static final Logger LOG = LoggerFactory.getLogger(HttpServerAndClientTest.class);

	private static DaemonRest server;

	private static Thread serverThread;

	@BeforeClass
	public static void setupServer() throws InterruptedException {
		Configuration.instance.isCryptoEnabled = false;
		Configuration.instance.restPort = 7605;

		server = new DaemonRest();
		serverThread = new Thread(server);
		serverThread.start();

		while (!server.getStatus().contains("started")) {
			LOG.debug("Waiting for server to start");
			Thread.sleep(100);
		}
	}

	@BeforeClass
	public static void setupSsl() throws Exception {
		SslUtil.init();
	}

	@AfterClass
	public static void stopServer() throws InterruptedException {
		server.stop();
		serverThread.join();
	}

	private StructureService getSuperService() {
		StructureCommand command = new StructureCommand();
		command.setCommandLine("sleep");

		StructureService service = new StructureService();
		service.setIdentifier("superService");
		service.setCommandWithOnlyPositionalArgs(command);
		service.setRegistered(true);
		service.setPeerUpdateRequired(true);

		return service;
	}

	@Test
	public void testGetCommands() throws IOException {
		URL u = new URL("http://localhost:7605/commands/list");
		String content = Util.isToString((u.openStream()));

		assertThat(content, instanceOf(String.class));
	}

	@Test
	public void testGetIndex() throws Exception {
		URL u = new URL("http://localhost:7605");
		String content = Util.isToString((u.openStream()));

		assertThat(content, containsString("<h1>"));
		assertThat(content, containsString(Main.getVersion()));
	}

	@Test
	public void testGetInternalStatus() throws IllegalArgumentException, GeneralSecurityException, IOException {
		URL u = new URL("http://localhost:7605/internalStatus");
		String content = Util.isToString((u.openStream()));

		assertThat(content, instanceOf(String.class));
		assertThat(content, endsWith("</internalStatus>"));
	}

	@Test
	public void testGetService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));

		Configuration.instance.services.register(this.getSuperService());

		StructureService ss = client.getService("superService");

		Assert.assertEquals(this.getSuperService(), ss);
	}

	@Test
	public void testPostNode() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));

		StructureNode testingNode = new StructureNode();
		client.postNode(testingNode);

		testingNode.setPeerUpdateRequired(false);
		client.postNode(testingNode);
	}

	@Test
	public void testPostService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));

		client.postService(this.getSuperService());
	}

	@Test
	public void testPostUneededService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));

		StructureService service = new StructureService();
		service.setPeerUpdateRequired(false);
		client.postService(service);
	}

	@Test
	public void testPostUnregisteredService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));

		StructureService service = new StructureService();
		service.setRegistered(false);
		client.postService(service);
	}
}
