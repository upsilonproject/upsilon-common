package upsilon.management.rest.server.util;

import javax.xml.bind.annotation.XmlAttribute;

import org.joda.time.Duration;

public class AdaptedDuration {
	private Duration d;
	 
	@Override
	@XmlAttribute 
	public String toString() {
		return d.toString();
	}
	
	public Duration getDuration() {
		return d;  
	}   
	
	public void setDuration(Duration d) {
		this.d = d;
	}
}
