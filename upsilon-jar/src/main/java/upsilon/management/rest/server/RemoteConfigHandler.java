package upsilon.management.rest.server;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;

import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;

import upsilon.util.ResourceResolver;

@Path("/remoteConfig")
public class RemoteConfigHandler {
    @Path("/{id}/")
    @GET
    @Produces(MediaType.TEXT_PLAIN)
    public Response get(@PathParam(value = "id") String id) {
        try {
            return Response.status(Status.OK).entity("# Config for: " + id + "\n" + this.getConfig(id)).build();
        } catch (FileNotFoundException e) {
            return Response.status(Status.NOT_FOUND).entity("File not found: " + e.getMessage()).build();
        }
    }

    private String getConfig(String configFilename) throws FileNotFoundException {
        File remoteConfigFile = new File(ResourceResolver.getInstance().getConfigDir(), "/remoteConfig/" + configFilename + ".cfg");

        return this.isToString(new FileInputStream(remoteConfigFile));
    }

    private String isToString(InputStream is) {
        StringBuilder inputBuffer = new StringBuilder();
        BufferedInputStream bis = new BufferedInputStream(is);

        int c;

        try {
            while ((c = bis.read()) != -1) {
                inputBuffer.append((char) c);
            }

            bis.close();
        } catch (IOException e) {
            e.printStackTrace();
        }

        return inputBuffer.toString();
    }
}
