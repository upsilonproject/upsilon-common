package upTests;

import java.net.InetAddress;
import java.net.UnknownHostException;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.TimeoutException;

import junit.framework.Assert;

import org.joda.time.Duration;
import org.junit.Ignore;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.RobustProcessExecutor;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

public class ProcessExecutorTest {
	private static final transient Logger LOG = LoggerFactory.getLogger(ProcessExecutorTest.class);
	
	@Test
	public void testHostname() throws InterruptedException, ExecutionException, TimeoutException, UnknownHostException {
		final StructureCommand cmd = new StructureCommand();
		cmd.setCommandLine("hostname");
		
		final StructureService dummyService = new StructureService();
		dummyService.setCommand(cmd);
		dummyService.setTimeout(Duration.standardSeconds(3));
		
		RobustProcessExecutor rpe = new RobustProcessExecutor(dummyService);
		rpe.execAsync();
		 
		Thread.sleep(500); // FIXME
		  
		Assert.assertEquals("GOOD", dummyService.getKarmaString());
		
		String execHostnameOutput = dummyService.getOutput();
		 
		Assert.assertEquals(InetAddress.getLocalHost().getHostName(), execHostnameOutput);
	} 
	
	@Test
	public void testFailedDependant() throws InterruptedException, ExecutionException, TimeoutException {
		StructureCommand cmd = new StructureCommand();
		cmd.setCommandLine("echo");
		
		StructureService srv1 = new StructureService();
		srv1.setCommand(cmd);
		
		StructureService srv2 = new StructureService();
		srv2.setCommand(cmd);
		 
		srv1.setDependsOn(srv2);
		
		RobustProcessExecutor rpe = new RobustProcessExecutor(srv1);
		rpe.execAsync();
		 
		Thread.sleep(1000); //FIXME 
		 
		Assert.assertEquals("SKIPPED", srv1.getKarmaString());
	}
}
