package upsilon.dataStructures;

import javax.xml.bind.annotation.XmlTransient;

import org.w3c.dom.Node;

public abstract class ConfigStructure {
    private boolean databaseUpdateRequired = true;

    private boolean peerUpdateRequired = true;

    public StructureService construct() {
        // TODO Auto-generated method stub
        return null;
    }

    @Override
    public final boolean equals(final Object obj) {
        if (obj == null) {
            return false;
        }

        if (obj instanceof ConfigStructure) {
            if ((this.getIdentifier() == null) || (((ConfigStructure) obj).getIdentifier() == null)) {
                return false;
            } else {
                return (((ConfigStructure) obj).getIdentifier().equals(this.getIdentifier()));
            }
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

    public void setDatabaseUpdateRequired(final boolean isChanged) {
        this.databaseUpdateRequired = isChanged;
        this.setPeerUpdateRequired(true);
    }

    public void setPeerUpdateRequired(final boolean peerUpdateRequired) {
        this.peerUpdateRequired = peerUpdateRequired;
    }

    public void update(final Node el) {

    }

}
