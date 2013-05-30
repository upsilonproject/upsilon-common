package upTests;

import java.util.Calendar;
import java.util.GregorianCalendar;

import junit.framework.Assert;

import org.joda.time.Duration;
import org.junit.Ignore;
import org.junit.Test;

import upsilon.util.FlexiTimer;
import upsilon.util.MutableFlexiTimer;
import upsilon.util.Util;

public class FlexiTimerTest {
	private static final long SECONDS_IN_A_DAY = 86400;

	@Test
	public void testBadResult() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(100), Duration.standardSeconds(10), "");

		Assert.assertEquals(0, ft.getGoodCount());

		ft.submitResult(false);

		Assert.assertEquals(0, ft.getGoodCount());
	}

	@Test
	@Ignore
	public void testDateBasedTimers() {
		long remaining;
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(100), Duration.standardSeconds(10), "");

		Calendar before = new GregorianCalendar(2012, 1, 2, 6, 1, 1);
		Calendar after = new GregorianCalendar(2012, 1, 3, 6, 1, 1);

		ft.touch(before.getTime());
		remaining = ft.getSecondsRemaining(after.getTime());

		Assert.assertEquals(-FlexiTimerTest.SECONDS_IN_A_DAY, remaining, 1f);
		Assert.assertTrue(ft.isTouchedPassed(after.getTime()));

		ft.touch(after.getTime());
		remaining = ft.getSecondsRemaining(before.getTime());

		Assert.assertEquals(+FlexiTimerTest.SECONDS_IN_A_DAY, remaining, 1f);
		Assert.assertFalse(ft.isTouchedPassed(before.getTime()));
	}

	@Test
	public void testDateTimer() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(1), Duration.standardSeconds(100), Duration.standardSeconds(10), "");

		Assert.assertFalse(ft.isTouchedPassed());
		
		ft.touch();
		Assert.assertEquals(1, ft.getSecondsRemaining());
 
		Util.lazySleep(Duration.millis(10)); 

		Assert.assertTrue(ft.isTouchedPassed());
		 
		Assert.assertNotNull(ft.toString());
	}

	@Test
	public void testLimitingBounds() {
		Assert.assertEquals(50, FlexiTimer.getIntWithinBounds(50, 0, 100));
		Assert.assertEquals(100, FlexiTimer.getIntWithinBounds(100, 0, 100));
		Assert.assertEquals(50, FlexiTimer.getIntWithinBounds(45, 50, 100));
		Assert.assertEquals(100, FlexiTimer.getIntWithinBounds(105, 50, 100)); 
		Assert.assertEquals(75, FlexiTimer.getIntWithinBounds(75, 0, 100));
	} 
 
	@Test
	public void testServiceDelay() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(100), Duration.standardSeconds(10), "");

		Assert.assertEquals(0, ft.getGoodCount());
		Assert.assertEquals(10, ft.getCurrentDelay().getStandardSeconds());

		ft.submitResult(true);

		Assert.assertEquals(1, ft.getGoodCount());
		Assert.assertEquals(20, ft.getCurrentDelay().getStandardSeconds());
	}

	@Test
	public void testTimerDelaysAfterBadResults() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(100), Duration.standardSeconds(10), "test timer delays after bad results");

		Assert.assertEquals(10, ft.getCurrentDelay().getStandardSeconds()); // initial
																			// delay
		ft.submitResult(true);

		Assert.assertEquals(20, ft.getCurrentDelay().getStandardSeconds()); // one
																			// good
																			// result

		ft.submitResult(false);
		Assert.assertEquals(10, ft.getCurrentDelay().getStandardSeconds()); // reset
																			// result
																			// with
																			// bad

		ft.submitResult(true, 7);
		Assert.assertEquals(80, ft.getCurrentDelay().getStandardSeconds()); // 7
																			// good
																			// results
	}

	@Test
	public void testTimerDelaysAfterGoodResults() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(30), Duration.standardSeconds(10), "");

		Assert.assertEquals(10, ft.getCurrentDelay().getStandardSeconds());
		ft.submitResult(true, 999);
		Assert.assertEquals(30, ft.getCurrentDelay().getStandardSeconds());
	}

	@Test
	public void testTimerSetters() {
		MutableFlexiTimer ft = new MutableFlexiTimer(Duration.standardSeconds(10), Duration.standardSeconds(100), Duration.standardSeconds(10), "");

		ft.setMin(Duration.standardSeconds(10));
		Assert.assertEquals(10, ft.getMinimumDelay().getStandardSeconds());

		ft.setMax(Duration.standardSeconds(100));
		Assert.assertEquals(100, ft.getMaximumDelay().getStandardSeconds());

		ft.setName("test1");
		Assert.assertEquals("test1", ft.getName());
	}
}
