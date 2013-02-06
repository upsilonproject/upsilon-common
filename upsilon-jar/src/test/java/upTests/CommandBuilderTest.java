package upTests;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;
 
public class CommandBuilderTest {
    @Test
    public void testBuildCommand() {
        StructureCommand checkFoo = new StructureCommand();
        StructureService service = new StructureService();
        service.setCommand(checkFoo, "check_foo!one!two");
        service.setHostname("HOST1");

        StructureCommand cmd = new StructureCommand();
        cmd.setName("check_foo");
        cmd.setCommandLine("/usr/bin/foo -H '$HOSTADDRESS$' -w foo");

        Assert.assertEquals("/usr/bin/foo", cmd.getExecutable());
        Assert.assertEquals("/usr/bin/foo -H HOST1 -w foo", cmd.getFinalCommandLine(service));

        cmd.setCommandLine("/usr/bin/foo -H '$HOSTADDRESS$' '$ARG1$' '$ARG2$' ");
        Assert.assertEquals("/usr/bin/foo -H HOST1 one two", cmd.getFinalCommandLine(service));
    }

    @Test
    public void testServiceRegistered() {
        StructureService ss = new StructureService();

        ss.setRegistered("true");
        Assert.assertTrue(ss.isRegistered());
    }
}
