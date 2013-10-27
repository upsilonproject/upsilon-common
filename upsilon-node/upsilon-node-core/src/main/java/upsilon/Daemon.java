package upsilon;

import javax.xml.bind.annotation.XmlAttribute;
import javax.xml.bind.annotation.XmlRootElement;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@XmlRootElement
public abstract class Daemon implements Runnable {
    private static final Logger LOG = LoggerFactory.getLogger(Daemon.class);

    private String status = "unknown";

    @XmlAttribute
    public String getIdentifier() {
        return this.getClass().getSimpleName();
    }

    @XmlAttribute
    public final String getStatus() {
        return this.status;
    }

    protected final void setStatus(final String status) {
        Daemon.LOG.trace("Daemon status for " + this.getIdentifier() + "changed to: " + status);
        this.status = status;
    }

    public abstract void stop();
}
