package upsilon.management.rest.server;

import java.util.List;

import javax.ws.rs.Consumes;
import javax.ws.rs.GET;
import javax.ws.rs.POST;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.dataStructures.StructureGroup;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;

@Path("services/")
@Consumes(MediaType.APPLICATION_XML)
public class ServiceHandler {
	private static transient final Logger LOG = LoggerFactory.getLogger(ServiceHandler.class);

	@GET
	@Path("/id/{id}")
	@Produces(MediaType.APPLICATION_XML)
	public StructureService get(@PathParam("id") String id) {
		return Configuration.instance.services.get(id);
	}

	@GET
	@Path("/list")
	@XmlElementWrapper
	@XmlElement
	public List<StructureService> list() {
		return Configuration.instance.services.getImmutable();
	}

	@GET
	@Path("/id/{id}/queue")
	public Response queueServiceCheck(@PathParam("id") String id) {
		StructureService ss = Configuration.instance.services.get(id);

		if (ss == null) {
			return Response.status(Status.INTERNAL_SERVER_ERROR).entity("Could not find service.").build();
		} else {
			Main.instance.queueMaintainer.queueUrgent(ss);

			return Response.status(Status.OK).entity("Service queued.").build();
		}
	}

	@POST
	@Path("/updateRemoteService")
	public Response updateRemoteService(StructureRemoteService rss) {
		ServiceHandler.LOG.debug("Got SRS, identifier:" + rss.getIdentifier() + " karma:" + rss.getKarmaString() + " groups: " + rss.groups.size() + " node: " + rss.getNodeIdentifier());

		Configuration.instance.remoteServices.add(rss);

		this.updateRemoteServiceGroups(rss);

		return Response.status(Status.OK).entity("Service updated").build();
	}

	private void updateRemoteServiceGroups(StructureRemoteService rss) {
		String parent = null;

		for (String group : rss.groups) {
			String[] groupParts = group.split("/");
			parent = null;

			LOG.trace("Split groups for: " + group + " is : " + groupParts.length);

			for (String part : groupParts) {
				LOG.trace("Sub group: " + part + " parent: " + parent);

				StructureGroup g = Configuration.instance.groups.get(part);

				if (g == null) {
					g = new StructureGroup();
					g.setName(part);

					if ((parent != null) && !parent.equals("[root]")) {
						g.setParent(parent);
					}

					Configuration.instance.groups.register(g);
				}

				if (!g.hasMember(rss) && part.equals(groupParts[groupParts.length - 1])) {
					g.addMember(rss);
				}

				parent = part;
			}
		}
	}
}
