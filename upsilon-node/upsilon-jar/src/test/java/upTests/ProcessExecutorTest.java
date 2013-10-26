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
import upsilon.dataStructures.ResultKarma;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

public class ProcessExecutorTest {
	private static final transient Logger LOG = LoggerFactory.getLogger(ProcessExecutorTest.class);

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

		Thread.sleep(1000); // FIXME

		Assert.assertEquals("SKIPPED", srv1.getKarmaString());
	}

	@Ignore
	public void testHogStdIn() {
	}

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
	public void testResultKarma() {
		Assert.assertEquals(ResultKarma.UNKNOWN, ResultKarma.valueOfOrUnknown("?"));
		Assert.assertEquals(ResultKarma.GOOD, ResultKarma.valueOfOrUnknown("GOOD"));
		Assert.assertEquals(ResultKarma.BAD, ResultKarma.valueOfOrUnknown("BAD"));
	}

	@Test
	public void testStdError() throws Exception {
		final StructureCommand cmd = new StructureCommand();
		cmd.setCommandLine("src/test/resources/executionTestScripts/testStdError.sh");

		final StructureService dummyService = new StructureService();
		dummyService.setIdentifier("Dummy Service");
		dummyService.setCommand(cmd);
		dummyService.setTimeout(Duration.standardSeconds(3));

		RobustProcessExecutor rpe = new RobustProcessExecutor(dummyService);
		rpe.execAsync();

		Thread.sleep(1000); // FIXME

		Assert.assertEquals("GOOD", dummyService.getKarmaString());

		String execHostnameOutput = dummyService.getOutput();

		Assert.assertEquals("STDERROR: This message is on stderr\nThis message is on stdout", execHostnameOutput);
	}

	@Test
	public void testUnicodeInOutput() throws Exception {
		final StructureCommand cmd = new StructureCommand();
		cmd.setCommandLine("src/test/resources/executionTestScripts/testMbStrings.py");

		LOG.debug("testUnicodeInOutput");

		final StructureService dummyService = new StructureService();
		dummyService.setIdentifier("Dummy Service");
		dummyService.setCommand(cmd);
		dummyService.setTimeout(Duration.standardSeconds(3));

		RobustProcessExecutor rpe = new RobustProcessExecutor(dummyService);
		rpe.execAsync();

		Thread.sleep(1500); // FIXME This is really stupid.

		String expected = "\u5f15\u8d77\u7684\u6216";
		LOG.debug("Service unicodeInOutput: karma: " + dummyService.getKarmaString() + ". Output: " + dummyService.getOutput() + ". Expected: " + expected);

		Assert.assertEquals("GOOD", dummyService.getKarmaString());

		String execHostnameOutput = dummyService.getOutput();

		Assert.assertEquals(expected, execHostnameOutput);
	}
}
