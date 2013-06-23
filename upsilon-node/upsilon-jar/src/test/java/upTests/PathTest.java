package upTests;

import java.net.MalformedURLException;

import org.junit.Test;

import upsilon.util.Path;
import junit.framework.Assert;
 
public class PathTest {
	@Test
	public void testGetFilename() throws MalformedURLException {
		Path path1 = new Path("http://example.com/?foo=bar&bar=foo");
		Assert.assertEquals("/?foo=bar&bar=foo", path1.getFilename()); 
		
		Path path2 = new Path("/etc/sample.conf");
		Assert.assertEquals("sample.conf", path2.getFilename());
	} 
}
