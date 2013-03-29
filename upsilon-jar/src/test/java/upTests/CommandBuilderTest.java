package upTests;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;
import upsilon.util.Util;

public class CommandBuilderTest {

    @Test
    public void testBuildCommand() {
        final StructureCommand checkFoo = new StructureCommand();
        final StructureService service = new StructureService();
        service.setCommand(checkFoo, "one", "two");

        final StructureCommand cmd = new StructureCommand();
        cmd.setIdentifier("check_foo");
        cmd.setCommandLine("/usr/bin/foo -w foo");

        Assert.assertEquals("/usr/bin/foo", cmd.getExecutable());
        Assert.assertEquals("/usr/bin/foo -w foo", Util.implode(cmd.getFinalCommandLinePieces(service)));

        cmd.setCommandLine("/usr/bin/foo $ARG1 $ARG2");
        Assert.assertEquals("/usr/bin/foo one two", Util.implode(cmd.getFinalCommandLinePieces(service)));
    }

    @Test
    public void testServiceRegistered() {
        final StructureService ss = new StructureService();

        ss.setRegistered(true);
        Assert.assertTrue(ss.isRegistered());
    }
}
