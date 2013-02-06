package upsilon.util;

import org.joda.time.Duration;

public class Util {
    public static void lazySleep(Duration howLong) {
        try {     
            Thread.sleep(howLong.getMillis());  
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
} 
