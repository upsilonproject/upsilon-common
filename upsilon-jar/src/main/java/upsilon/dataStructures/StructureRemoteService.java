package upsilon.dataStructures;

import javax.xml.bind.annotation.XmlRootElement;

import org.joda.time.Instant;

@XmlRootElement
public class StructureRemoteService implements AbstractService {
	private String karma = "karma";
	private String description = "desc";
	private String output = "output";

	private String id;

	private Instant lastUpdated;

	private String executable;

	private String hostname;

	private String cmdline;
	private Instant estimatedNextCheck;
	private int goodCount;

	private String nodeIdentifier = "???";

	@Override
	public String getCallCommand() {
		return "calling command";
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
	public String getFinalCommandLine(AbstractService s) {
		return this.cmdline;
	}

	@Override
	public String getHostname() {
		return this.hostname;
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
		return this.goodCount;
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
	public void setDatabaseUpdateRequired(boolean b) {
		// always required
	}

	public void setDescription(String description2) {
		this.description = description2;
	}

	public void setEstimatedNextCheck(Instant estimatedNextCheck) {
		this.estimatedNextCheck = estimatedNextCheck;
	}

	public void setExecutable(String executable) {
		this.executable = executable;
	}

	public void setFinalCommandLine(String finalCommandLine) {
		this.cmdline = finalCommandLine;
	}

	public void setHostname(String hostname) {
		this.hostname = hostname;
	}

	public void setIdentifier(String id) {
		this.id = id;
	}

	public void setKarmaString(String karma) {
		this.karma = karma;
	}

	public void setLastUpdated(Instant lastUpdated) {
		this.lastUpdated = lastUpdated;
	}

	public void setNodeIdentifier(String nodeIdentifier) {
		this.nodeIdentifier = nodeIdentifier;
	}

	public void setOutput(String output2) {
		this.output = output2;
	}

	public void setResultConsequtiveCount(int goodCount2) {
		this.goodCount = goodCount2;
	}
}
