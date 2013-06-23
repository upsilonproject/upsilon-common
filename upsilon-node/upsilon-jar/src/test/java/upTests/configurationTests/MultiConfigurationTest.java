package upTests.configurationTests;

import java.io.File;
import java.net.URL;

import junit.framework.Assert;

import org.junit.Before;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.configuration.FileChangeWatcher;
import upsilon.configuration.XmlConfigurationLoader;
import upsilon.util.Path;
import static org.hamcrest.Matchers.*; 
import static org.hamcrest.MatcherAssert.*;
 
public class MultiConfigurationTest { 
	@Before
	public void setupConfig() {
		Configuration.instance.clear();
	}
	@Test
	public void testMultiConfiguration() throws Exception {
		final Path masterFile = new Path("file://src/test/resources/multi/config.xml");
		final Path slaveFile = new Path("file://src/test/resources/multi/slave.xml"); 
		final Configuration config = Configuration.instance;   
		
		XmlConfigurationLoader loader = new XmlConfigurationLoader();
		FileChangeWatcher fcw = loader.load(masterFile, false);  
		
		assertThat(loader.getValidator().isParseClean(), is(true));
		assertThat(config.services.getImmutable(), hasSize(1)); 
		assertThat(config.services.containsId("echo"), is(true));
		
		assertThat(config.commands.getImmutable(), hasSize(1)); 
		assertThat(config.commands.containsId("echo"), is(true));
		       
		FileChangeWatcher fcwSlave = loader.load(slaveFile, false);
		
		Thread.sleep(1000);
		
		assertThat(config.services.getImmutable(), hasSize(2));  
		assertThat(config.commands.getImmutable(), hasSize(2));
		
		loader.reparse();   
		
		assertThat(config.services.getImmutable(), hasSize(2));
		assertThat(config.commands.getImmutable(), hasSize(2));
		     
		fcwSlave.setWatchedFile(new Path("file://src/test/resources/multi/config.empty.xml"));
		fcwSlave.checkForModification(); 
		 
		assertThat(config.services.getImmutable(), hasSize(1));
		assertThat(config.services.containsId("hostname"), is(true));
		
		assertThat(config.commands.getImmutable(), hasSize(1));
		assertThat(config.commands.containsId("hostname"), is(true));
	}
	
	private static final Logger LOG = LoggerFactory.getLogger(MultiConfigurationTest.class); 
}
  