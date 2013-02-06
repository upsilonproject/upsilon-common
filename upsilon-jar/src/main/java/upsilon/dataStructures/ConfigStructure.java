package upsilon.dataStructures;

import javax.xml.bind.annotation.XmlTransient;

public abstract class ConfigStructure {
	private boolean databaseUpdateRequired = true;
	private boolean peerUpdateRequired = true;

	@Override
	public final boolean equals(Object obj) {
		if (obj instanceof ConfigStructure) {
			return (((ConfigStructure) obj).getIdentifier().equals(this.getIdentifier()));
		} else {
			return false;
		}
	}

	public String getClassAndIdentifier() {
		return "[" + this.getClass().getSimpleName() + "]:" + this.getIdentifier();
	}

	public abstract String getIdentifier();

	@Override
	public int hashCode() {
		return this.getClassAndIdentifier().hashCode();
	}

	@XmlTransient
	public boolean isDatabaseUpdateRequired() {
		return this.databaseUpdateRequired;
	}

	@XmlTransient
	public boolean isPeerUpdateRequired() {
		return this.peerUpdateRequired;
	}

	public void setDatabaseUpdateRequired(boolean isChanged) {
		this.databaseUpdateRequired = isChanged;
		this.setPeerUpdateRequired(true);
	}

	public void setPeerUpdateRequired(boolean peerUpdateRequired) {
		this.peerUpdateRequired = peerUpdateRequired;
	}

}
