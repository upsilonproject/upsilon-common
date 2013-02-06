package upTests;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.Configuration;
import upsilon.dataStructures.StructureGroup;

public class GroupHeirarchyTest {
    @Test
    public void testGroupHeirarchyDeep() {
        StructureGroup one = new StructureGroup();
        one.setName("one");

        StructureGroup two = new StructureGroup();
        two.setName("two");

        StructureGroup three = new StructureGroup();
        three.setName("three");

        two.setParent("one");
        three.setParent("two");

        Configuration.instance.groups.register(one);
        Configuration.instance.groups.register(two);
        Configuration.instance.groups.register(three);

        Assert.assertEquals("[root]/one/two/three", three.getFullyQualifiedIdentifier());
    }

    @Test
    public void testGroupHeirarchyShallow() {
        StructureGroup one = new StructureGroup();
        one.setName("one");

        StructureGroup two = new StructureGroup();
        two.setName("two");

        two.setParent("one");

        Configuration.instance.groups.register(one);
        Configuration.instance.groups.register(two);

        Assert.assertEquals("[root]/one/two", two.getFullyQualifiedIdentifier());
    }
}
