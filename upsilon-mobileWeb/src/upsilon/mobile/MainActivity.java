package upsilon.mobile;

import android.app.ActionBar;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.content.pm.ActivityInfo;
import android.net.http.SslError;
import android.os.Bundle;
import android.support.v4.app.FragmentActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.webkit.SslErrorHandler;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.TextView;

public class MainActivity extends FragmentActivity implements ActionBar.OnNavigationListener {
	protected SharedPreferences getPrefs() {
		return getSharedPreferences("upsilon", 0);
	}
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		
		setContentView(R.layout.activity_main);

		setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_NOSENSOR);

		final ActionBar actionBar = getActionBar();
		actionBar.setTitle(R.string.app_name); 
		actionBar.setDisplayShowTitleEnabled(true);
		
		this.url = getPrefs().getString("url", "");
		 
		if (url.isEmpty()) {    
			promptForUrl("URL");
		}
		
		this.web = (WebView) findViewById(R.id.webView1);
		this.web.getSettings().setAppCacheEnabled(false);
		this.web.getSettings().setCacheMode(WebSettings.LOAD_NO_CACHE);
		this.web.setWebViewClient(new WebViewClient() {
			@Override
			public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
				Log.v("sslError", error.toString());
				handler.proceed();
			}
		});

		refresh();
	}
	 
	private void promptForUrl(String q) {
		final EditText input = new EditText(this);
		input.setMaxLines(1); 
		input.setText(getPrefs().getString("url", ""));
		
		AlertDialog.Builder builder = new AlertDialog.Builder(this);
		builder.setTitle("upsilon-web URL?"); 
		builder.setView(input);  
		
		builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
			@Override
			public void onClick(DialogInterface dialog, int which) {
				String res = input.getText().toString();
				urlChanged(res);
			}
		});  
		
		builder.show();  
	}
	
	private String url; 
	 
	private void urlChanged(String url) {
		this.url = url; 
		
		SharedPreferences.Editor editor = getPrefs().edit();
		editor.putString("url", url);
		editor.commit();  
		 
		alert("", "URL set!\n\nPlease click the refresh button. ");
	}
  
	private WebView web;

	public void onClearCache(MenuItem mniClear) {
		this.web.clearCache(true);

		AlertDialog alertClear = new AlertDialog.Builder(this).create();
		// alertClear.setTitle("Cache Cleared");
		alertClear.setMessage("Cache Cleared!");
		alertClear.show();

		refresh(); 
	}

	public void refresh() {
		setStatusText("Waiting...");

		web.loadUrl(this.url);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		setText(item.toString());
		return super.onOptionsItemSelected(item);
	}

	public void onMniAboutClicked(MenuItem about) {
		String version;
		try {
			version = getPackageManager().getPackageInfo(getPackageName(), 0).versionName;
		} catch (Exception e) {
			version = "???";
		}
 
		alert("About", "Version: " + version);
	}
	 
	public void onMniSetUrlClicked(MenuItem mni) {
		promptForUrl("Set URL");
	}
	 
	private void alert(String title, String content) {
		AlertDialog alertAbout = new AlertDialog.Builder(this).create();
		alertAbout.setTitle(title);
		alertAbout.setMessage(content);
		alertAbout.show();
	}

	@Override
	public void onRestoreInstanceState(Bundle savedInstanceState) {
	}

	public void onClickRefresh(MenuItem mnu) {
		refresh();
	}

	@Override
	public void onSaveInstanceState(Bundle outState) {
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.main, menu);
		return true;
	}

	public void setStatusText(String result) {
		TextView et = (TextView) findViewById(R.id.TextView1);
		et.setText(result);
	}

	public void setText(String result) {
		WebView web = (WebView) findViewById(R.id.webView1);
		web.loadData(result, "text/html", null);
	}

	@Override
	public boolean onNavigationItemSelected(int itemPosition, long itemId) {
		return false;
	}

}
