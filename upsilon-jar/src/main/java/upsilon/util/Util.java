package upsilon.util;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class Util {
	private static final transient Logger LOG = LoggerFactory.getLogger(Util.class);
	
    public static void lazySleep(Duration howLong) {
        try {     
            Thread.sleep(howLong.getMillis());  
        } catch (InterruptedException e) {
        	LOG.warn("Insomnia in thread.", e); 
        } 
    }
} 
