package upTests;

import org.hamcrest.CoreMatchers;
import org.junit.Assert;
import org.junit.Test;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

public class StructureEqualityTest {
    @Test
    public void testEqualCommands() {
        final CollectionOfStructures<StructureCommand> col = new CollectionOfStructures<>("testingStructure");

        final StructureCommand c1 = new StructureCommand();
        final StructureCommand c2 = new StructureCommand();

        c1.setIdentifier("one");
        c2.setIdentifier("two");
        Assert.assertThat(c1, CoreMatchers.not(c2));

        c1.setIdentifier("pie");
        c2.setIdentifier("pie");
        Assert.assertThat(c1, CoreMatchers.equalTo(c2));

        col.register(c1);
        Assert.assertTrue(col.contains(c2));

        col.register(c1);
        col.register(c2);
        col.register(c1);

        Assert.assertEquals(1, col.size());
    }

    @Test
    public void testEqualServices() {
        final StructureCommand check_command = new StructureCommand();

        final StructureService s1 = new StructureService();
        final StructureService s2 = new StructureService();

        s1.setIdentifier("one");
        s1.setCommand(check_command, "check_pie!foo");
        s2.setIdentifier("two");
        s1.setCommand(check_command, "check_pie!bar");
        Assert.assertThat(s1, CoreMatchers.not(s2));

        s1.setIdentifier("pie");
        s2.setIdentifier("pie");
        Assert.assertThat(s1, CoreMatchers.equalTo(s2));

        s1.setCommand(check_command, "check_pie!foo");
        s2.setCommand(check_command, "check_pie!bar");
        Assert.assertThat(s1, CoreMatchers.equalTo(s2));

        Assert.assertThat(s1, CoreMatchers.not(new Object()));
    }
}
