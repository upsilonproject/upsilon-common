package upsilon.management.rest.server.util;

import javax.xml.bind.annotation.adapters.XmlAdapter;

import org.joda.time.Instant;

public class InstantAdapter extends XmlAdapter<String, Instant> {

	@Override
	public String marshal(Instant arg0) throws Exception {
		return arg0.toString();
	}

	@Override
	public Instant unmarshal(String arg0) throws Exception {
		return Instant.parse(arg0);
	}
}
