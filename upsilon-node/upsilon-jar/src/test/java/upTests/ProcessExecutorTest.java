package upTests;

import org.joda.time.Duration;
import org.junit.Ignore;
import org.junit.Test;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.RobustProcessExecutor;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

@Ignore
public class ProcessExecutorTest {
    private static final transient Logger LOG = LoggerFactory.getLogger(ProcessExecutorTest.class);

    @Test
    public void testOvercommit() {
        final StructureCommand cmd = new StructureCommand();
        cmd.setCommandLine("ls");

        final StructureService dummyService = new StructureService();
        dummyService.setCommand(cmd);
        dummyService.setTimeout(Duration.standardSeconds(3));

        for (int i = 0; i < 20; i++) {
            final RobustProcessExecutor rpe1 = new RobustProcessExecutor(dummyService);

            try {
                rpe1.exec();
                ProcessExecutorTest.LOG.info(i + "output: " + rpe1.getOutput() + " result: " + rpe1.getReturn());
                rpe1.destroy();
            } catch (final Exception e) {
                e.printStackTrace();
            }
        }
    }
}
