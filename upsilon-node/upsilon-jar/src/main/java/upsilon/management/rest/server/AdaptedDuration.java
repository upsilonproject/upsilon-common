package upsilon.management.rest.server;

import javax.xml.bind.annotation.XmlAttribute;

import org.joda.time.Duration;

public class AdaptedDuration {
	private Duration d;
	 
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
