package upsilon.util;

import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.Configuration;
import upsilon.management.rest.client.RestClient;

public class SslUtil {
	private static SSLContext context;
	
   public static HostnameVerifier getInsecureHostnameVerifier() {
        return new HostnameVerifier() {
            @Override
            public boolean verify(final String arg0, final SSLSession arg1) {
                return true;
            }
        };
    } 

    public static TrustManager[] getInsecureTrustManager() {
        return new TrustManager[] { new X509TrustManager() {
        	
            @Override
            public void checkClientTrusted(final X509Certificate[] arg0, final String arg1) throws CertificateException {
            }

            @Override
            public void checkServerTrusted(final X509Certificate[] certs, final String arg1) throws CertificateException {
                MessageDigest md;

                try {
                    md = MessageDigest.getInstance("SHA1");
                } catch (final NoSuchAlgorithmException e) {
                    throw new CertificateException("No such alg: SHA1");
                }

                for (final X509Certificate crt : certs) {
                    final byte[] digest = md.digest(crt.getEncoded());
                    final String fingerprint = new BigInteger(digest).toString(16);

                    if (!Configuration.instance.trustedCertificates.contains(fingerprint)) {
                        LOG.warn("Server certificate fingerprint is not trusted: " + fingerprint);
                        throw new CertificateException("Server certificiate fingerprint is not trusted: " + fingerprint);
                    }
                }
            } 

            @Override
            public X509Certificate[] getAcceptedIssuers() {
                return new X509Certificate[] {};
            }
        } };
    }
     
    private static final Logger LOG = LoggerFactory.getLogger(SslUtil.class);

	public static SSLContext getContext() {  
		 return context; 
	}
 
	public static void init() throws Exception {
        context = SSLContext.getInstance("SSL");
        context.init(null, SslUtil.getInsecureTrustManager(), null);
         
        HttpsURLConnection.setDefaultHostnameVerifier(SslUtil.getInsecureHostnameVerifier());
        HttpsURLConnection.setDefaultSSLSocketFactory(context.getSocketFactory()); 
	}
}
