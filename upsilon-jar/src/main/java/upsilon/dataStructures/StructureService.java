package upsilon.dataStructures;

import java.util.Calendar;
import java.util.Date;
import java.util.Vector;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;
import javax.xml.bind.annotation.adapters.XmlJavaTypeAdapter;

import org.joda.time.Duration;
import org.joda.time.Instant;
import org.w3c.dom.Node;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.management.rest.server.util.DurationAdaptor;
import upsilon.util.FlexiTimer;
import upsilon.util.GlobalConstants;
import upsilon.util.MutableFlexiTimer;

@XmlRootElement
public class StructureService extends ConfigStructure implements AbstractService {
    private String description;

    private StructureCommand command;
    @XmlElement
    private ResultKarma karma;
    private final MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(60), Duration.standardSeconds(5), "service timer");
    private String hostname;
    private boolean register = true;
    private String output = "(not yet executed)";
    private String callCommand = "";
    private transient Duration timeoutSeconds = GlobalConstants.DEF_TIMEOUT;

    private StructureService dependsOn;

    public void addResult(ResultKarma karma, Date whenChecked) {
        this.karma = karma;
        this.ft.touch(whenChecked);

        if (this.karma == ResultKarma.GOOD) {
            this.ft.submitResult(true);
        } else {
            this.ft.submitResult(false);
        }
    }

    public void addResult(ResultKarma karma, int count, String message) {
        this.addResult(karma, message);
        this.ft.setGoodCount(count);
    }

    public void addResult(ResultKarma karma, String output) {
        this.addResult(karma, Calendar.getInstance().getTime());
        this.output = output;
        this.setDatabaseUpdateRequired(true);
    }

    @Override
    @XmlElement
    public String getCallCommand() {
        return this.callCommand;
    }

    @XmlElement(required = true)
    public StructureCommand getCommand() throws IllegalArgumentException {
        if (this.command == null) {
            throw new IllegalArgumentException("service does not have an associated command, for service: " + this.description);
        }

        return this.command;
    }

    public StructureService getDependancy() {
        return this.dependsOn;
    }

    @Override
    public String getDescription() {
        return this.description;
    }

    @Override
    public Instant getEstimatedNextCheck() {
        return this.getFlexiTimer().getEstimatedFireDate();
    }

    @Override
    public String getExecutable() {
        return this.getCommand().getExecutable();
    }

    @Override
    public String getFinalCommandLine(AbstractService s) {
        return this.getCommand().getFinalCommandLine(s);
    }

    @XmlElement
    public FlexiTimer getFlexiTimer() {
        return this.ft;
    }

    @Override
    public String getHostname() {
        return this.hostname;
    }

    @Override
    @XmlElement
    public String getIdentifier() {
        return this.getDescription() + ":" + StructureCommand.parseCallCommandExecutable(this.callCommand);
    }

    @Override
    public String getKarmaString() {
        if (this.getResult() == null) {
            return ResultKarma.UNKNOWN.toString();
        } else {
            return this.getResult().toString();
        }
    }

    @Override
    public Instant getLastUpdated() {
        return this.ft.getLastTouched();
    }

    @Override
    public Vector<String> getMemberships() {
        Vector<String> ret = new Vector<String>();

        for (StructureGroup g : Configuration.instance.groups) {
            if (g.hasMember(this)) {
                ret.add(g.getFullyQualifiedIdentifier());
            }
        }

        return ret;
    }

    @Override
    public String getNodeIdentifier() {
        return Main.instance.node.getIdentifier();
    }

    @Override
    public String getOutput() {
        return this.output;
    }

    public ResultKarma getResult() {
        return this.karma;
    }

    @Override
    @XmlElement
    public int getResultConsequtiveCount() {
        return this.ft.getGoodCount();
    }

    @Override
    @XmlElement
    public long getSecondsRemaining() {
        return this.ft.getSecondsRemaining();
    }

    @XmlJavaTypeAdapter(DurationAdaptor.class)
    public Duration getTimeout() {
        return this.timeoutSeconds;
    }

    @Override
    @XmlTransient
    public boolean isDatabaseUpdateRequired() {
        if (this.karma == null) {
            return false;
        }

        return super.isDatabaseUpdateRequired();
    }

    @Override
    public boolean isLocal() {
        return true;
    }

    public boolean isReadyToBeChecked() {
        if (this.register) {
            return this.ft.getSecondsRemaining() <= 0;
        } else {
            return false;
        }
    }

    @Override
    public boolean isRegistered() {
        return this.register;
    }

    public void setCommand(StructureCommand command, String cmdLine) {
        this.command = command;
        this.callCommand = cmdLine;
    }

    public void setDependsOn(StructureService dependsOn) {
        this.dependsOn = dependsOn;
    }

    public void setDescription(String description) {
        this.ft.setName("ft for " + description);
        this.description = description.trim();
    }

    public void setHostname(String hostname) {
        this.hostname = hostname.trim();
    }

    public void setRegistered(String registerd) {
        this.register = Boolean.parseBoolean(registerd);
    }

    public void setTimeout(Duration timeout) {
        this.timeoutSeconds = timeout;
    }

    public void setTimerMax(Duration parameterValueInt) {
        this.ft.setMax(parameterValueInt);
    }

    public void setTimerMin(Duration parameterValueInt) {
        this.ft.setMin(parameterValueInt);
    }

    public void setUpdateIncrement(Duration increment) {
        this.ft.setInc(increment);
    }

    @Override
    public void update(Node el) {
        this.description = el.getNodeValue();
    }
}
