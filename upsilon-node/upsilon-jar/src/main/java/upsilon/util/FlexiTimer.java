package upsilon.util;

import java.util.Calendar;
import java.util.Date;
import java.util.Random;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;

import org.joda.time.Duration;
import org.joda.time.Instant;
import org.joda.time.Period;

@XmlRootElement
public class FlexiTimer {
	public static long getIntWithinBounds(long test, long min, long max) {
		if (test < min) {
			return min;
		} else if (test > max) {
			return max;
		} else {
			return test;
		}
	}

	public static Duration getPeriodWithinBounds(Duration test, Duration min, Duration max) {
		return Duration.standardSeconds(getIntWithinBounds(test.getStandardSeconds(), min.getStandardSeconds(), max.getStandardSeconds()));
	}

	protected Duration sleepMin = GlobalConstants.MIN_SERVICE_SLEEP;
	protected Duration sleepMax = GlobalConstants.MAX_SERVICE_SLEEP;

	protected Duration inc = GlobalConstants.DEF_INC_SERVICE_UPDATE;
	protected Duration currentDelay = GlobalConstants.DEF_INC_SERVICE_UPDATE;
	protected String name = "untitled timer";
	protected int goodCount = 0;

	protected Instant lastTouched;
	protected boolean isAbrupt = true;

	protected static final Random RANDOM_TIMER = new Random();

	public FlexiTimer() {
		lastTouched = Instant.now();
	}

	@XmlElement
	public Duration getCurrentDelay() {
		return this.currentDelay;
	}

	@XmlElement
	public Instant getEstimatedFireDate() {
		return lastTouched.plus(currentDelay);
	} 

	public int getGoodCount() {
		return this.goodCount;
	}

	@XmlElement
	public Duration getIncrement() {
		return this.inc;
	}

	@XmlElement
	public Instant getLastTouched() {
		return this.lastTouched;
	}

	@XmlElement
	public Duration getMaximumDelay() {
		return this.sleepMax;
	}

	@XmlElement
	public Duration getMinimumDelay() {
		return this.sleepMin;
	}

	@XmlElement
	public String getName() {
		return this.name;
	}

	public long getSecondsRemaining() {
		return this.getSecondsRemaining(Calendar.getInstance().getTime());
	}

	public long getSecondsRemaining(Date from) {
		Instant fromTime = new Instant(from.getTime());
		Instant nextDue = lastTouched.plus(currentDelay);

		return new Period(fromTime, nextDue).getSeconds();

		// return lastTouched.plus(currentDelay).minus(from.getTime())
		// return
		// currentDelay.plus(lastTouched.getTime()).minus(from.getTime()).getStandardSeconds();

		// long secondsRemaining = ((this.lastTouched.getTime() +
		// this.currentDelay.getMillis()) - from.getTime()) / 1000;

		// return secondsRemaining;
	}

	public boolean isTouchedPassed() {
		return this.isTouchedPassed(Calendar.getInstance().getTime());
	}

	public boolean isTouchedPassed(Date from) {
		return this.getSecondsRemaining(from) <= 0;
	}

	@Override
	public String toString() {
		return this.getClass().getSimpleName() + " {currentDelay: " + this.getCurrentDelay() + ", min:" + this.getMinimumDelay() + ", max:" + this.getMaximumDelay() + "}";
	}
}
