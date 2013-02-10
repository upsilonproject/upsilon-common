package upsilon.dataStructures;

import java.net.InetAddress;
import java.net.UnknownHostException;

import javax.xml.bind.annotation.XmlAttribute;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;

import upsilon.Configuration;
import upsilon.Main;

@XmlRootElement
public class StructureNode extends ConfigStructure {
	private String type = "???";
	private int serviceCount;
	private String identifier = "unidentifiedNode";
	private String instanceApplicationVersion = "???";

	@Override
	@XmlElement
	public String getIdentifier() {
		return this.identifier;
	}

	@XmlAttribute
	public String getInstanceApplicationVersion() {
		return this.instanceApplicationVersion;
	}

	@XmlAttribute
	public int getServiceCount() {
		return this.serviceCount;
	}

	@XmlAttribute
	public String getType() {
		return this.type;
	}

	@Override
	public boolean isPeerUpdateRequired() {
		return super.isPeerUpdateRequired();
	}

	public void refresh() {
		this.refreshType();
		this.refreshIdentifier();
		this.instanceApplicationVersion = Main.getVersion();
		this.setServiceCount(Configuration.instance.services.size());
	}

	private void refreshIdentifier() {
		String newIdentifier;
		try {
			newIdentifier = InetAddress.getLocalHost().getHostName();
		} catch (UnknownHostException e) {
			newIdentifier = "unknownHostname";
		}

		if (!this.identifier.equals(newIdentifier)) {
			this.setDatabaseUpdateRequired(true);
			this.identifier = newIdentifier;
		}
	}

	private void refreshType() {
		String newType = Main.instance.guessNodeType();

		if (this.type.equals(newType)) {
			return;
		} else {
			this.type = newType;
			this.setPeerUpdateRequired(true);
		}
	}

	public void setIdentifier(String identifier) {
		this.identifier = identifier;
	}

	public void setInstanceApplicationVersion(String versionString) {
		this.instanceApplicationVersion = versionString;
	}

	@Override
	public void setPeerUpdateRequired(boolean peerUpdateRequired) {
		super.setPeerUpdateRequired(peerUpdateRequired);
	}

	public void setServiceCount(int serviceCount) {
		if (this.serviceCount != serviceCount) {
			this.serviceCount = serviceCount;

			this.setDatabaseUpdateRequired(true);
			this.setPeerUpdateRequired(true);
		}
	}

	public void setType(String type) {
		this.type = type;
	}

	@Override
	public String toString() {
		return String.format("%s = {identifier: %s, type: %s, service count: %s}", this.getClass().getSimpleName(), this.getIdentifier(), this.type, this.getServiceCount());
	}
}
