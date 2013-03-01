package upsilon;

import java.io.File;
import java.io.IOException;
import java.lang.management.ManagementFactory;
import java.util.Properties;
import java.util.Vector;
import java.util.logging.Level;
import java.util.logging.LogManager;

import javax.management.InstanceAlreadyExistsException;
import javax.management.MBeanRegistrationException;
import javax.management.MBeanServer;
import javax.management.MalformedObjectNameException;
import javax.management.NotCompliantMBeanException;
import javax.management.ObjectName;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.bridge.SLF4JBridgeHandler;

import upsilon.configuration.XmlConfigurationLoader;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructurePeer;
import upsilon.management.jmx.MainMBeanImpl;
import upsilon.util.ResourceResolver;
import ch.qos.logback.classic.LoggerContext;
import ch.qos.logback.classic.joran.JoranConfigurator;

public class Main {
    public static final Main instance = new Main();
    private static File configurationOverridePath;
    private static String releaseVersion;

    public static File getConfigurationOverridePath() {
        return configurationOverridePath;
    }

    public static String getVersion() {
        if (releaseVersion == null) {
            releaseVersion = Main.class.getPackage().getImplementationVersion();

            try {
                Properties props = new Properties();
                props.load(Main.class.getResourceAsStream("/releaseVersion.properties"));
                releaseVersion = props.getProperty("releaseVersion");
            } catch (IOException | NullPointerException e) {
                LOG.warn("Could not get release version from jar.", e);
            }

            if ((releaseVersion == null) || releaseVersion.isEmpty()) {
                releaseVersion = "?";
            }
        }

        return releaseVersion;
    }

    public static void main(String[] args) {
        if (args.length > 0) {
            Main.configurationOverridePath = new File(args[0]);
        }

        XmlConfigurationLoader xmlLoader = new XmlConfigurationLoader();
        xmlLoader.load(new File("src/test/resources/config.xml"));

        Main.instance.startup();
    }

    public final StructureNode node = new StructureNode();
    private static transient final Logger LOG = LoggerFactory.getLogger(Main.class);

    private static void setupLogging() {
        LogManager.getLogManager().getLogger("").setLevel(Level.FINEST);
        SLF4JBridgeHandler.removeHandlersForRootLogger();
        SLF4JBridgeHandler.install();

        File loggingConfiguration = new File(ResourceResolver.getInstance().getConfigDir(), "logging.xml");

        try {
            if (loggingConfiguration.exists()) {
                LOG.info("Logging override configuration exists, parsing: " + loggingConfiguration.getAbsolutePath());

                JoranConfigurator loggerConfigurator = new JoranConfigurator();
                loggerConfigurator.setContext((LoggerContext) LoggerFactory.getILoggerFactory());
                loggerConfigurator.doConfigure(loggingConfiguration);
            }
        } catch (Exception e) {
            LOG.warn("Could not set up logging config.", e);
        }
    }

    private final Vector<Daemon> daemons = new Vector<Daemon>();

    public DaemonScheduler queueMaintainer;

    public Vector<Daemon> getDaemons() {
        return this.daemons;
    }

    public String guessNodeType() {
        return this.guessNodeType(Database.instance, Configuration.instance.peers);
    }

    public String guessNodeType(Database db, CollectionOfStructures<StructurePeer> peers) {
        if ((db == null) && !peers.isEmpty()) {
            return "service-node";
        }

        if ((db != null) && peers.isEmpty()) {
            return "super-node";
        }

        if ((db == null) && peers.isEmpty()) {
            return "useless-testing-node";
        }

        return "non-standard-node";
    }

    private void setupMbeans() {
        MBeanServer srv = ManagementFactory.getPlatformMBeanServer();

        try {
            srv.registerMBean(new MainMBeanImpl(), new ObjectName("upsilon.mbeansImpl:type=MainMBeanImpl"));
        } catch (InstanceAlreadyExistsException | MBeanRegistrationException | NotCompliantMBeanException | MalformedObjectNameException e) {
            LOG.error("Could not register MBean", e);
        }
    }

    public void shutdown() {
        for (Daemon t : this.daemons) {
            t.stop();
        }

        LOG.warn("All daemons have been requested to stop. Main application should now shutdown.");
    }

    private void startDaemon(Daemon r) {
        Thread t = new Thread(r, r.getClass().getSimpleName());
        this.daemons.add(r);

        t.start();
    }

    private void startup() {
        Main.setupLogging();

        LOG.info("Upsilon " + Main.getVersion());
        LOG.info("----------");
        LOG.debug("CP: " + System.getProperty("java.class.path"));
        LOG.trace("OS: " + System.getProperty("os.name"));

        Configuration.instance.reparse();

        this.setupMbeans();

        this.startDaemon(new DaemonRest());
        this.startDaemon(new DaemonScheduler());

        LOG.debug("Best guess at node type: " + this.guessNodeType());
    }
}
