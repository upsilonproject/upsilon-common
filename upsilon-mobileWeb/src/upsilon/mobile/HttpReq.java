package upsilon.mobile;

import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.SecureRandom;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import android.os.AsyncTask;
import android.util.Log;

public class HttpReq extends AsyncTask<String, String, String> {
	public HttpReq(MainActivity main) {
		this.main = main;
	}

	static {
		setupSsl();
	}

	private final MainActivity main;

	public static void setupSsl() {
		TrustManager[] trustAllCerts = new TrustManager[] { new X509TrustManager() {

			@Override
			public void checkClientTrusted(X509Certificate[] chain, String authType) throws CertificateException {

			}

			@Override
			public void checkServerTrusted(X509Certificate[] chain, String authType) throws CertificateException {

			}

			@Override
			public X509Certificate[] getAcceptedIssuers() {
				return null;
			}

		} };

		try {
			HttpReq.sc = SSLContext.getInstance("SSL");
			HttpReq.sc.init(null, trustAllCerts, new SecureRandom());

			HttpsURLConnection.setDefaultSSLSocketFactory(HttpReq.sc.getSocketFactory());

			HostnameVerifier hnv = new HostnameVerifier() {
				@Override
				public boolean verify(String hostname, SSLSession session) {
					return true;
				}
			};

			HttpsURLConnection.setDefaultHostnameVerifier(hnv);
		} catch (Exception e) {
			Log.e("SSL", e.getMessage());
		}
	}

	private static SSLContext sc;

	@Override
	protected String doInBackground(String... params) {

		HttpURLConnection conn;

		try {
			conn = (HttpURLConnection) new URL("https://upsilon.teratan.net/?login=mobile").openConnection();
			conn.setInstanceFollowRedirects(true);
			conn.setUseCaches(false);
			conn.setRequestProperty("Cache-Control", "no-cache");
			conn.connect();
			InputStream is = conn.getInputStream();
			StringBuilder sb = new StringBuilder();

			int i = 0;
			while ((i = is.read()) != -1) {
				sb.append((char) i);
			}

			is.close();
			conn.disconnect();

			result = sb.toString();
		} catch (Exception e) {
			result = "Exception: " + e.toString();
		}

		return result;
	}

	String result;

	@Override
	protected void onPostExecute(String result) {
		main.setStatusText("Done!");
		main.setText(result);
	}
}
