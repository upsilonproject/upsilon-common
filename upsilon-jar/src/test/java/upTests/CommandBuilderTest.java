package upTests;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

public class CommandBuilderTest {
    @Test
    public void testBuildCommand() {
        final StructureCommand checkFoo = new StructureCommand();
        final StructureService service = new StructureService();
        service.setCommand(checkFoo, "check_foo!one!two");
        service.setHostname("HOST1");

        final StructureCommand cmd = new StructureCommand();
        cmd.setName("check_foo");
        cmd.setCommandLine("/usr/bin/foo -H '$HOSTADDRESS$' -w foo");

        Assert.assertEquals("/usr/bin/foo", cmd.getExecutable());
        Assert.assertEquals("/usr/bin/foo -H HOST1 -w foo", cmd.getFinalCommandLine(service));

        cmd.setCommandLine("/usr/bin/foo -H '$HOSTADDRESS$' '$ARG1$' '$ARG2$' ");
        Assert.assertEquals("/usr/bin/foo -H HOST1 one two", cmd.getFinalCommandLine(service));
    }

    @Test
    public void testEscapedArgumentSeparators() {
        final StructureCommand checkCommand = new StructureCommand();
        checkCommand.setCommandLine("/bin/true '$ARG0$' '$ARG1$'");

        final StructureService service = new StructureService();
        service.setCommand(checkCommand, "one!two\\!three");

        Assert.assertEquals("/bin/true one two!three", checkCommand.getFinalCommandLine(service));
    }

    @Test
    public void testServiceRegistered() {
        final StructureService ss = new StructureService();

        ss.setRegistered("true");
        Assert.assertTrue(ss.isRegistered());
    }
}
