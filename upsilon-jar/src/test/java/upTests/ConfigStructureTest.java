package upTests;

import org.junit.Test;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public class ConfigStructureTest {
    @Test
    public void testPutGet() {
        final CollectionOfStructures<StructureService> ss = new CollectionOfStructures<>("testingStructure");

        final StructureService structureOne = new StructureService();
        structureOne.setIdentifier("foo");

        ss.register(structureOne);

        // Assert.assertEquals(structureOne, ss.get("foo"));
    }
}
