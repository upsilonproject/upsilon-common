package upTests;

import junit.framework.Assert;

import org.junit.Test;

import upsilon.Database;

public class DatabaseEqualityTest {
    @Test
    public void testDbEquality() {
        Database d1 = new Database("localhost", null, null, 1000, null);
        Database d2 = new Database("localhost", null, null, 1000, null);

        Assert.assertEquals(d1, d2);
        Assert.assertNotSame(d1, false); 
        Assert.assertNotSame(d1, new Integer(0));
        Assert.assertNotSame(d1, null);
    }  
}  
