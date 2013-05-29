package upTests;

import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;

import junit.framework.Assert;

import org.glassfish.grizzly.http.server.HttpServer;
import org.junit.After;
import org.junit.AfterClass;
import org.junit.Before;
import org.junit.BeforeClass;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.DaemonRest;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructureService;
import upsilon.management.rest.client.RestClient;

public class HttpServerAndClientTest {
	private static final Logger LOG = LoggerFactory.getLogger(HttpServerAndClientTest.class);
	
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
	
	private static DaemonRest server;
	private static Thread serverThread; 
	
	@Test
	public void testPostNode() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException  {	   
		RestClient client = new RestClient(new URL("http://localhost:7605"));
		
		StructureNode testingNode = new StructureNode();
		client.postNode(testingNode);
	}
	 
	@Test
	public void testPostUnregisteredService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));
		
		StructureService service = new StructureService();
		service.setRegistered(false); 
		client.postService(service);
	}
	
	@Test
	public void testPostUneededService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));
		
		StructureService service = new StructureService();
		service.setPeerUpdateRequired(false);
		client.postService(service);
	}
	
	@Test
	public void testPostService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));
			
		client.postService(getSuperService());
	}
	
	private StructureService getSuperService() {
		StructureCommand command = new StructureCommand();
		command.setCommandLine("sleep");
		
		StructureService service = new StructureService();
		service.setIdentifier("superService");
		service.setCommand(command);
		service.setRegistered(true);
		service.setPeerUpdateRequired(true); 
		  
		return service;
	}
	 
	@Test
	public void testGetService() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:7605"));
		
		Configuration.instance.services.register(getSuperService());
		 
		StructureService ss = client.getService("superService"); 
		
		Assert.assertEquals(getSuperService(), ss);
	}
	 
	@AfterClass 
	public static void stopServer() throws InterruptedException {
		server.stop();
		serverThread.join();
	}
}
