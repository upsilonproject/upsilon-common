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

import upsilon.Configuration;
import upsilon.Main;
import upsilon.configuration.XmlNodeHelper;
import upsilon.management.rest.server.util.DurationAdaptor;
import upsilon.util.FlexiTimer;
import upsilon.util.GlobalConstants;
import upsilon.util.MutableFlexiTimer;
import upsilon.util.Util;

@XmlRootElement
public class StructureService extends ConfigStructure implements AbstractService {
    private String identifier;

    private StructureCommand command;
    @XmlElement
    private ResultKarma karma;
    private final MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(60), Duration.standardSeconds(5), "service timer");
    private boolean register = true;
    private String output = "(not yet executed)";
    private Vector<String> arguments = new Vector<String>();
    private transient Duration timeoutSeconds = GlobalConstants.DEF_TIMEOUT;

    private StructureService dependsOn;

    public void addResult(final ResultKarma karma, final Date whenChecked) {
        this.karma = karma;
        this.ft.touch(whenChecked);

        if (this.karma == ResultKarma.GOOD) {
            this.ft.submitResult(true);
        } else {
            this.ft.submitResult(false);
        }
    }

    public void addResult(final ResultKarma karma, final int count, final String message) {
        this.addResult(karma, message);
        this.ft.setGoodCount(count);
    }

    public void addResult(final ResultKarma karma, final String output) {
        this.addResult(karma, Calendar.getInstance().getTime());
        this.output = output;
        this.setDatabaseUpdateRequired(true);
    }

    @Override
    @XmlElement
    public Vector<String> getArguments() {
        return this.arguments;
    }

    @XmlElement(required = false)
    public StructureCommand getCommand() throws IllegalArgumentException {
        return this.command;
    }

    public StructureService getDependancy() {
        return this.dependsOn;
    }

    @Override
    public String getDescription() {
        return this.identifier;
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
    public String getFinalCommandLine(final AbstractService s) {
        return Util.implode(this.getCommand().getFinalCommandLinePieces(this));
    }

    @XmlElement
    public FlexiTimer getFlexiTimer() {
        return this.ft;
    }

    @Override
    @XmlElement
    public String getIdentifier() {
        return this.identifier;
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

    public void setCommand(final StructureCommand command, final String... arguments) {
        final Vector<String> vectorArgs = new Vector<String>();

        for (final String a : arguments) {
            vectorArgs.add(a);
        }

        this.setCommand(command, vectorArgs);
    }

    public void setCommand(final StructureCommand command, final Vector<String> arguments) {
        this.command = command;
        this.arguments = new Vector<String>();
        this.arguments.addAll(arguments);
    }

    public void setDependsOn(final StructureService dependsOn) {
        this.dependsOn = dependsOn;
    }

    public void setIdentifier(final String description) {
        this.ft.setName("ft for " + description);
        this.identifier = description.trim();
    }

    public void setRegistered(final boolean registered) {
        this.register = registered;
    }

    public void setTimeout(final Duration timeout) {
        this.timeoutSeconds = timeout;
    }

    public void setTimerMax(final Duration parameterValueInt) {
        this.ft.setMax(parameterValueInt);
    }

    public void setTimerMin(final Duration parameterValueInt) {
        this.ft.setMin(parameterValueInt);
    }

    public void setUpdateIncrement(final Duration increment) {
        this.ft.setInc(increment);
    }

    @Override
    public void update(final XmlNodeHelper el) {
        this.identifier = el.getAttributeValueUnchecked("id");
        this.setTimeout(el.getAttributeValue("timeout", GlobalConstants.DEF_TIMEOUT));
        this.ft.setMin(el.getAttributeValue("minDelay", GlobalConstants.MIN_SERVICE_SLEEP));
        this.ft.setMax(el.getAttributeValue("maxDelay", GlobalConstants.MAX_SERVICE_SLEEP));

        if (el.hasAttribute("commandRef")) {
            final String commandIdentifier = el.getAttributeValueUnchecked("commandRef");

            final StructureCommand cmd = Configuration.instance.commands.getById(commandIdentifier);

            final Vector<String> arguments = new Vector<>();

            for (final XmlNodeHelper child : el.getChildElements("argument")) {
                arguments.add(child.getNodeValue());
            }

            this.setCommand(cmd, arguments);
        } else {
            this.setRegistered(false);
        }
    }
}
