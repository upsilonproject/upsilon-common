package upsilon.management.rest.server;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.lang.management.ManagementFactory;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.NoSuchAlgorithmException;
import java.security.cert.Certificate;
import java.security.cert.CertificateException;
import java.util.Collections;
import java.util.Date;
import java.util.Scanner;
import java.util.Vector;

import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import javax.ws.rs.core.Response.Status;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;
import javax.xml.bind.annotation.XmlRootElement;

import upsilon.Configuration;
import upsilon.Daemon;
import upsilon.Database;
import upsilon.Main;
import upsilon.configuration.XmlConfigurationLoader.ConfigStatus;
import upsilon.util.ResourceResolver;

@Path("/")
public class Index {
	@XmlRootElement
	public static class InternalStatus {
		@XmlElement
		public String getClasspath() {
			return ManagementFactory.getRuntimeMXBean().getClassPath();
		}

		@XmlElement(name = "configStatuses")
		@XmlElementWrapper
		public Vector<ConfigStatus> getConfigStatus() {
			return Main.instance.getXmlConfigurationLoader().getStatuses();
		}

		@XmlElement
		public String getConfigurationOveridePath() {
			if (Main.getConfigurationOverridePath() == null) {
				return "";
			} else {
				return Main.getConfigurationOverridePath().getAbsolutePath();
			}
		}

		@XmlElement
		public boolean getCrypto() {
			return Configuration.instance.isCryptoEnabled;
		}

		@XmlElement(name = "daemon")
		@XmlElementWrapper
		public Vector<Daemon> getDaemons() {
			return Main.instance.getDaemons();
		}

		@XmlElement(name = "database", nillable = true, required = false)
		public String getDb() {
			if (Database.instance != null) {
				return Database.instance.toString();
			} else {
				return null;
			}
		}

		@XmlElement
		public String getPid() {
			return ManagementFactory.getRuntimeMXBean().getName();
		}

		@XmlElement
		public Date getStartTime() {
			return new Date(ManagementFactory.getRuntimeMXBean().getStartTime());
		}

		@XmlElement(name = "thread")
		@XmlElementWrapper
		public Vector<String> getThreads() {
			final Vector<String> threads = new Vector<String>();

			for (final Thread t : Thread.getAllStackTraces().keySet()) {
				threads.add(t.getName());
			}

			Collections.sort(threads);

			return threads;
		}

		@XmlElement(name = "version")
		public String getVersion() {
			return Main.getVersion();
		}

		@XmlElement
		public String getVm() {
			return ManagementFactory.getRuntimeMXBean().getVmName() + " " + ManagementFactory.getRuntimeMXBean().getVmVersion();
		}
	}

	@Path("/sslCert")
	@GET
	@Produces(MediaType.APPLICATION_OCTET_STREAM)
	public Response getSslCert() {
		final File keystore = new File(ResourceResolver.getInstance().getConfigDir(), "keyStore.jks");

		try {
			final FileInputStream fis = new FileInputStream(keystore);
			final KeyStore ks = KeyStore.getInstance(KeyStore.getDefaultType());
			ks.load(fis, Configuration.instance.passwordKeystore.toCharArray());

			final Certificate c = ks.getCertificate("upsilon.teratan.net");

			final String base64cert = new String(java.util.Base64.getEncoder().encode(c.getEncoded()));
			System.out.println(base64cert);

			fis.close();
			return Response.status(Status.OK).entity(c.getEncoded()).header("Content-Disposition", "attachment; filename=upsilon.crt").build();
		} catch (NoSuchAlgorithmException | CertificateException | IOException | KeyStoreException e) {
			e.printStackTrace();
		}

		return null;
	}

	@Path("/internalStatus")
	@GET
	@Produces(MediaType.APPLICATION_XML)
	public Response getStatus() {
		return Response.status(Status.OK).entity(new InternalStatus()).build();
	}

	@GET
	@Produces(MediaType.TEXT_HTML)
	public Response getWelcomePage() throws Exception {
		final InputStream is = Index.class.getResourceAsStream("/index.xhtml");
		final Scanner s = new Scanner(is);
		s.useDelimiter("\\A");
		String htmlFile = s.next();

		s.close();
		is.close();

		htmlFile = htmlFile.replace("$version", Main.getVersion());

		return Response.status(Status.OK).entity(htmlFile).build();
	}
}
