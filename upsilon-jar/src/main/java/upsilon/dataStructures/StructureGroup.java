package upsilon.dataStructures;

import java.util.Collections;
import java.util.Vector;

import javax.xml.bind.annotation.XmlRootElement;

import upsilon.Configuration;
 
@XmlRootElement
public class StructureGroup extends ConfigStructure {
	private String name = "untitled group";
	private String description = "undescribed group";

	private final Vector<AbstractService> memberServices = new Vector<AbstractService>();

	private String parent = "";

	public void addMember(AbstractService service) {
		if (!this.memberServices.contains(service)) {
			this.memberServices.add(service);
		}
	}

	public String getDescription() {
		return this.description;
	}
  
	public String getFullyQualifiedIdentifier() {
		StructureGroup parent = null;
		StructureGroup current = this;
		StringBuilder path = new StringBuilder("[root]");

		Vector<String> heirarchy = new Vector<String>();
		heirarchy.add(this.getIdentifier());

		while (true) {
			parent = Configuration.instance.groups.get(current.getParent());

			if (parent == null) {
				break;
			} else {
				heirarchy.add(parent.getIdentifier());
				current = parent;
			}
		}

		Collections.reverse(heirarchy);

		for (String s : heirarchy) {
			path.append("/");
			path.append(s);
		}

		return path.toString();
	}

	@Override
	public String getIdentifier() {
		return this.getName();
	}

	public String getName() {
		return this.name;
	}

	public String getParent() {
		return this.parent;
	}

	public Vector<AbstractService> getServices() {
		return this.memberServices;
	}

	public boolean hasMember(AbstractService structureService) {
		for (AbstractService c : this.memberServices) {
			if (c.getIdentifier().equals(structureService.getIdentifier())) {
				return true;
			}
		}

		return false;
	}

	public void setDescription(String description) {
		if (description != null) {
			this.description = description;
		}
	}

	public void setName(String name) {
		this.name = name;
	}

	public void setParent(String parent) {
		this.parent = parent;
	}
}
