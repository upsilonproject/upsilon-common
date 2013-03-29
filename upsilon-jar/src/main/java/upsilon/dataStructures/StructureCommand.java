package upsilon.dataStructures;

import java.util.List;
import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;
import javax.xml.bind.annotation.XmlRootElement;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.configuration.XmlNodeHelper;

@XmlRootElement
public class StructureCommand extends ConfigStructure {
    private static transient final Logger LOG = LoggerFactory.getLogger(StructureCommand.class);

    public static String parseCallCommandExecutable(final String parameterValue) {
        if (parameterValue.contains("!")) {
            final String components[] = parameterValue.split("!");

            return components[0];
        } else {
            return parameterValue;
        }
    }

    public static List<String> parseCommandArguments(final String fullOriginalCommandLine) {
        final String fullParsedCommandLine = fullOriginalCommandLine.replace(StructureCommand.parseCommandExecutable(fullOriginalCommandLine), "");

        if (fullParsedCommandLine.contains("!")) {
            throw new IllegalArgumentException("Command definition for arguments cannot contain !, is this a service command line?");
        }

        final Vector<String> args = new Vector<String>();

        for (String arg : fullParsedCommandLine.split(" ")) {
            arg = arg.trim();

            if (!arg.isEmpty()) {
                args.add(arg);
            }
        }

        return args;
    }

    private static String parseCommandArgumentVariable(String originalVariable, final List<String> callingArguments) {
        for (int i = 0; i < callingArguments.size(); i++) {
            originalVariable = originalVariable.replace("$ARG" + (i + 1), callingArguments.get(i));
        }

        return originalVariable.trim();
    }

    public static String parseCommandExecutable(final String fullCommandLine) {
        final Pattern patCommandLineExecutable = Pattern.compile("([\\/\\w\\.\\_]+)");
        final Matcher matCommandLineExecutable = patCommandLineExecutable.matcher(fullCommandLine);
        matCommandLineExecutable.find();

        if (matCommandLineExecutable.groupCount() == 0) {
            StructureCommand.LOG.warn("Parsing executable: " + fullCommandLine + ", group count was: " + matCommandLineExecutable.groupCount());
            return "";
        } else {
            return matCommandLineExecutable.group(1).trim();
        }
    }

    private String executable = "";

    private List<String> definedArguments = new Vector<String>();

    private String identifier;

    @XmlElement(name = "argument")
    @XmlElementWrapper(name = "arguments")
    public List<String> getArguments() {
        return this.definedArguments;
    }

    @XmlElement
    public String getExecutable() {
        return this.executable;
    }

    public String[] getFinalCommandLinePieces(final StructureService service) {
        final Vector<String> pieces = new Vector<String>();
        pieces.add(this.getExecutable());

        final Vector<String> callingArguments = service.getArguments();

        for (final String arg : this.definedArguments) {
            pieces.add(StructureCommand.parseCommandArgumentVariable(arg, callingArguments));
        }

        return pieces.toArray(new String[] {});
    }

    @Override
    @XmlElement
    public String getIdentifier() {
        return this.identifier;
    }

    public void setCommandLine(final String fullCommandLine) {
        this.executable = StructureCommand.parseCommandExecutable(fullCommandLine);
        this.definedArguments = StructureCommand.parseCommandArguments(fullCommandLine);
    }

    public void setIdentifier(final String identifier) {
        this.identifier = identifier;
    }

    @Override
    public String toString() {
        return "Command, executable: " + this.getExecutable();
    }

    @Override
    public void update(final XmlNodeHelper el) {
        this.setIdentifier(el.getAttributeValueUnchecked("id"));
        this.setCommandLine(el.getAttributeValueUnchecked("exec"));
    }
}
