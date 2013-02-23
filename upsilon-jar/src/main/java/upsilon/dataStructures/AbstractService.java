package upsilon.dataStructures;

import org.joda.time.Instant;

public interface AbstractService {
	public abstract String getCallCommand();

	public String getDescription();

	public Instant getEstimatedNextCheck();

	public abstract String getExecutable();

	public abstract String getFinalCommandLine(AbstractService s);

	public abstract String getHostname();

	public String getIdentifier();

	public abstract String getKarmaString();

	public Instant getLastUpdated();

	public abstract String getNodeIdentifier();

	public abstract String getOutput();

	public abstract int getResultConsequtiveCount();

	public abstract long getSecondsRemaining();

	public abstract boolean isDatabaseUpdateRequired();

	public boolean isLocal();

	public abstract boolean isRegistered();

	public abstract void setDatabaseUpdateRequired(boolean b);
}
