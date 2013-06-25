package upTests;

import junit.framework.Assert;

import org.joda.time.Duration;

import org.junit.Test;

import upsilon.management.rest.server.util.AdaptedDuration;

public class DurationAdaptorTest {
	@Test
	public void testDurationAdaption() {
		Duration d = Duration.standardSeconds(10);
		
		AdaptedDuration ad = new AdaptedDuration();
		ad.setDuration(d);
		
		Assert.assertEquals(d, ad.getDuration());
		Assert.assertEquals(ad.toString(), d.toString());
	}
}
