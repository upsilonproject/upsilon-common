package upTests;

import java.net.MalformedURLException;

import org.junit.Test;

import upsilon.util.UPath;
import junit.framework.Assert;
 
public class PathTest {
	@Test
	public void testGetFilename() throws MalformedURLException {
		UPath path1 = new UPath("http://example.com/?foo=bar&bar=foo");
		Assert.assertEquals("/?foo=bar&bar=foo", path1.getFilename()); 
		
		UPath path2 = new UPath("/etc/sample.conf");
		Assert.assertEquals("sample.conf", path2.getFilename());
	} 
}
