package upTests;

import org.junit.Test;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public class ConfigStructureTest {
    @Test
    public void testPutGet() {
        CollectionOfStructures<StructureService> ss = new CollectionOfStructures<>();

        StructureService structureOne = new StructureService();
        structureOne.setIdentifier("foo");

        ss.register(structureOne);

        // Assert.assertEquals(structureOne, ss.get("foo"));
    }
} 
