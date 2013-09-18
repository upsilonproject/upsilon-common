package upsilon.management.rest.server.util;

import javax.xml.bind.annotation.adapters.XmlAdapter;

import org.joda.time.Duration;

public class DurationAdapter extends XmlAdapter<String, Duration> {

    @Override
    public String marshal(final Duration arg0) throws Exception {
        return arg0.toString();
    }

    @Override
    public Duration unmarshal(final String arg0) throws Exception {
        return Duration.parse(arg0);
    }
}
