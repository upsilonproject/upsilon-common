package upsilon.dataStructures;

import java.util.Vector;

import javax.xml.bind.annotation.XmlRootElement;

import org.joda.time.Instant;

@XmlRootElement
public class StructureRemoteService implements AbstractService {
    private String karma = "karma";
    private String description = "desc";
    private String output = "output";

    private String id;

    private Instant lastUpdated;
    private Instant lastChanged;

    private String executable;

    private String cmdline;
    private Instant estimatedNextCheck;
    private int consecutiveCount;

    private String nodeIdentifier = "???";

    @Override
    public Vector<String> getArguments() { 
        return new Vector<String>();
    }

    @Override
    public String getDescription() {
        return this.description;
    }

    @Override
    public Instant getEstimatedNextCheck() {
        return this.estimatedNextCheck;
    }

    @Override
    public String getExecutable() {
        return this.executable;
    }

    @Override
    public String getFinalCommandLine(final AbstractService s) {
        return this.cmdline;
    }

    @Override
    public String getIdentifier() {
        return this.id;
    }

    @Override
    public String getKarmaString() {
        return this.karma;
    }

    @Override
    public Instant getLastUpdated() {
        return this.lastUpdated;
    }

    @Override
    public String getNodeIdentifier() {
        return this.nodeIdentifier;
    }

    @Override
    public String getOutput() {
        return this.output;
    }

    @Override
    public int getResultConsequtiveCount() {
        return this.consecutiveCount;
    }
     
    public Instant getLastChanged() {
    	return this.lastChanged;
    }

    @Override
    public long getSecondsRemaining() {
        return 0;
    }

    @Override
    public boolean isDatabaseUpdateRequired() {
        return true;
    }

    @Override
    public boolean isLocal() {
        return false;
    }

    @Override
    public boolean isRegistered() {
        return true;
    }

    @Override
    public void setDatabaseUpdateRequired(final boolean b) {
        // always required
    }

    public void setDescription(final String description2) {
        this.description = description2;
    }
 
    public void setEstimatedNextCheck(final Instant estimatedNextCheck) {
        this.estimatedNextCheck = estimatedNextCheck;
    }

    public void setExecutable(final String executable) {
        this.executable = executable;
    }

    public void setFinalCommandLine(final String finalCommandLine) {
        this.cmdline = finalCommandLine;
    }

    public void setIdentifier(final String id) {
        this.id = id;
    }

    public void setKarmaString(final String karma) {
        this.karma = karma;
    }

    public void setLastUpdated(final Instant lastUpdated) {
        this.lastUpdated = lastUpdated;
    }
    
    public void setLastChanged(final Instant lastChanged) {
    	this.lastChanged = lastChanged;     
    }

    public void setNodeIdentifier(final String nodeIdentifier) {
        this.nodeIdentifier = nodeIdentifier;
    }

    public void setOutput(final String output2) {
        this.output = output2;
    }

    public void setResultConsequtiveCount(final int count) {
        this.consecutiveCount = count; 
    }
}
