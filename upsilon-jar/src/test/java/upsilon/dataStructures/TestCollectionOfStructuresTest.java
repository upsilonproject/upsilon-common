package upsilon.dataStructures;

import org.junit.Assert;
import org.junit.Test;

public class TestCollectionOfStructuresTest {

    @Test
    public void testGetType() {
        final CollectionOfStructures<StructureCommand> listCommands = new CollectionOfStructures<>("StructureCommand");

        Assert.assertEquals("StructureCommand", listCommands.getTitle());
    }

}
