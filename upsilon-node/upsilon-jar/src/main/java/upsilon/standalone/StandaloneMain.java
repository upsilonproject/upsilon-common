package upsilon.standalone;

import java.io.File;
import java.util.logging.Level;
import java.util.logging.LogManager;

import org.slf4j.LoggerFactory;
import org.slf4j.bridge.SLF4JBridgeHandler;

import upsilon.Configuration;
import upsilon.util.ResourceResolver;
import ch.qos.logback.classic.Logger;
import ch.qos.logback.classic.LoggerContext;
import ch.qos.logback.classic.joran.JoranConfigurator;

public class StandaloneMain {
	private static final Logger LOG = (Logger) LoggerFactory.getLogger(StandaloneMain.class);

	public static void main(String[] args) throws Exception {
		setupLogging();
		upsilon.Main.main(args);

		if (Configuration.instance.daemonRestEnabled) {
			upsilon.Main.instance.startDaemon(new DaemonRest());
		}
	}

	private static void setupLogging() {
		LogManager.getLogManager().getLogger("").setLevel(Level.FINEST);
		SLF4JBridgeHandler.removeHandlersForRootLogger();
		SLF4JBridgeHandler.install();

		final File loggingConfiguration = new File(ResourceResolver.getInstance().getConfigDir(), "logging.xml");

		try {
			if (loggingConfiguration.exists()) {
				StandaloneMain.LOG.info("Logging override configuration exists, parsing: " + loggingConfiguration.getAbsolutePath());

				for (Logger logger : ((LoggerContext) LoggerFactory.getILoggerFactory()).getLoggerList()) {
					logger.detachAndStopAllAppenders();
				}

				final JoranConfigurator loggerConfigurator = new JoranConfigurator();
				loggerConfigurator.setContext((LoggerContext) LoggerFactory.getILoggerFactory());
				loggerConfigurator.doConfigure(loggingConfiguration);
			}
		} catch (final Exception e) {
			StandaloneMain.LOG.warn("Could not set up logging config.", e);
		}
	}

}
