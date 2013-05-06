package upTests;

import junit.framework.Assert;

import org.junit.Ignore;
import org.junit.Test;

import upsilon.Configuration;
import upsilon.DaemonRest;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;
import upsilon.management.rest.client.RestClient;

public class ServiceGetTest {
    @Test
    @Ignore
    public void testGetService() throws Exception {
        final StructureService s = new StructureService();
        s.setIdentifier("foo");
        s.setCommand(new StructureCommand());

        Assert.assertEquals("foo:", s.getIdentifier());

        Configuration.instance.services.register(s);

        final DaemonRest srv = new DaemonRest();
        new Thread(srv).start();

        Thread.sleep(3000);

        final RestClient rc = new RestClient(DaemonRest.getBaseUri().toURL());

        final StructureService snew = rc.getService("foo:");

        Assert.assertNotNull(snew);
        Assert.assertEquals("foo:", snew.getIdentifier());

        srv.stop();
    }
}
