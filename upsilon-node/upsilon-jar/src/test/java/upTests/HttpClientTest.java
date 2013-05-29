package upTests;

import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.management.rest.client.RestClient;

public class HttpClientTest {
	@Test(expected=MalformedURLException.class)
	public void testBadUrl() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		new RestClient(new URL(""));
	} 
	 
	@Test(expected=IllegalArgumentException.class)
	public void testBadPort() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException{
		RestClient rc = new RestClient(new URL("http://localhost:0"));
	}
	 
	@Test(expected=IllegalArgumentException.class)
	public void testBadHost() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException{
		new RestClient(new URL("http://:20"));
	} 
	
	@Test
	public void testUrlEquality() throws IllegalArgumentException, MalformedURLException, GeneralSecurityException {
		RestClient client = new RestClient(new URL("http://localhost:1234"));
		
		Assert.assertEquals(new URL("http://localhost:1234"), client.getUrl());
	}  
	
}
 