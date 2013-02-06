package upsilon.management.rest.server;

import java.util.List;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import upsilon.Configuration;
import upsilon.dataStructures.StructureGroup;

@Path("groups")
@Produces(MediaType.APPLICATION_XML)
public class GroupHandler {
    @GET
    @Path("/list") 
    @XmlElementWrapper  
    @XmlElement  
    public List<StructureGroup> list() {
    	return Configuration.instance.groups.getImmutable();
    }    
} 
