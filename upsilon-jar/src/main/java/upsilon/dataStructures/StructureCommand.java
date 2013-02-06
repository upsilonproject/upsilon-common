package upsilon.dataStructures;

import java.util.Arrays;
import java.util.Iterator;
import java.util.List;
import java.util.Vector;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;
import javax.xml.bind.annotation.XmlRootElement;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@XmlRootElement
public class StructureCommand extends ConfigStructure {
    private static transient final Logger LOG = LoggerFactory.getLogger(StructureCommand.class);
 
    public static Vector<String> parseCallCommandArguments(String fullCommandLine) {
        String components[] = fullCommandLine.split("!");
        Vector<String> v = new Vector<String>(Arrays.asList(components));

        Iterator<String> it = v.iterator();
 
        while (it.hasNext()) {
            if (it.next().isEmpty()) {
                it.remove();
            }
        }

        return v;
    }

    public static String parseCallCommandExecutable(String parameterValue) {
        if (parameterValue.contains("!")) {
            String components[] = parameterValue.split("!");

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

        Vector<String> args = new Vector<String>();

        for (String arg : fullParsedCommandLine.split(" ")) {
            arg = arg.trim();

            if (!arg.isEmpty()) {
                args.add(arg);
            }
        }

        return args;
    }
  
    private static String parseCommandArgumentVariable(final String originalVariable, final AbstractService service, final List<String> callingArguments) {
        String parsedVariable = originalVariable.replace("'$HOSTADDRESS$'", service.getHostname()).trim();

        for (int i = 0; i < callingArguments.size(); i++) {
            parsedVariable = parsedVariable.replace("'$ARG" + i + "$'", callingArguments.get(i));
        }

        return parsedVariable;
    }

    public static String parseCommandExecutable(final String fullCommandLine) {
        Pattern patCommandLineExecutable = Pattern.compile("([\\/\\w\\.\\_]+)");
        Matcher matCommandLineExecutable = patCommandLineExecutable.matcher(fullCommandLine);
        matCommandLineExecutable.find();

        if (matCommandLineExecutable.groupCount() == 0) {
            StructureCommand.LOG.warn("Parsing executable: " + fullCommandLine + ", group count was: " + matCommandLineExecutable.groupCount());
            return "";
        } else {
            return matCommandLineExecutable.group(1).trim();
        }
    }

    private String executable = "???";

    private String name;
    private List<String> definedArguments = new Vector<String>();
  
    @XmlElement
    public String getExecutable() {
        return this.executable;
    }
    
    @XmlElement(name="argument")
    @XmlElementWrapper(name="arguments")
    public List<String> getArguments() {
    	return this.definedArguments;
    }

    public String getFinalCommandLine(AbstractService service) {
        StringBuilder sb = new StringBuilder();

        sb.append(this.getExecutable());
        sb.append(' ');

        Vector<String> callingArguments = StructureCommand.parseCallCommandArguments(service.getCallCommand());

        for (String arg : this.definedArguments) {
            sb.append(StructureCommand.parseCommandArgumentVariable(arg, service, callingArguments));
            sb.append(' ');
        }

        return sb.toString().trim();
    }

    public String[] getFinalCommandLinePieces(StructureService service) {
        Vector<String> pieces = new Vector<String>();
        pieces.add(this.getExecutable());

        Vector<String> callingArguments = StructureCommand.parseCallCommandArguments(service.getCallCommand());

        for (String arg : this.definedArguments) {
            pieces.add(StructureCommand.parseCommandArgumentVariable(arg, service, callingArguments));
        }

        return pieces.toArray(new String[] {});
    }

    @Override
    @XmlElement
    public String getIdentifier() {
        return this.getName();
    }

    @XmlElement
    public String getName() {
        return this.name;
    }

    public void setCommandLine(String fullCommandLine) {
        this.executable = StructureCommand.parseCommandExecutable(fullCommandLine);
        this.definedArguments = StructureCommand.parseCommandArguments(fullCommandLine);
    }

    public void setName(String name) {
        this.name = name;
    }

    @Override
    public String toString() {
        return "Command, executable: " + this.getExecutable();
    }
}
