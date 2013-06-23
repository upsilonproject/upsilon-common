package upsilon.util;

import java.util.Calendar;
import java.util.Date;

import org.joda.time.Duration;
import org.joda.time.Instant;

public class MutableFlexiTimer extends FlexiTimer {

	public MutableFlexiTimer(Duration sleepMin, Duration sleepMax, Duration sleepIncrement, String name) {
		this.setMin(sleepMin);
		this.setMax(sleepMax);
		this.setInc(sleepIncrement);

		this.recalculateDelay();

		this.setName(name);
	}

	private void recalculateDelay() {
		this.currentDelay = this.sleepMin.plus((this.inc.getMillis() * this.goodCount));
		this.currentDelay = FlexiTimer.getPeriodWithinBounds(this.currentDelay, this.sleepMin, this.sleepMax);
	}
 
	public void setAbrupt(boolean isAbrupt) {
		this.isAbrupt = isAbrupt;
	}

	public void setGoodCount(int count) {
		this.goodCount = count;
	}

	public final void setInc(Duration inc) {
		this.inc = inc;
		this.recalculateDelay();
	}

	public final void setMax(Duration max) {
		this.sleepMax = max;
		this.recalculateDelay();
	}

	public final void setMin(Duration min) { 
		if (min.isShorterThan(GlobalConstants.MIN_SERVICE_EXECUTION_DELAY)) {
			throw new IllegalArgumentException("Flexitimer minimum is below the threshold of: " + GlobalConstants.MIN_SERVICE_EXECUTION_DELAY);
		} 

		this.sleepMin = min;
		this.recalculateDelay();
	}

	public final void setName(String name) {
		if ((name == null) || name.isEmpty()) {
			this.name = "untitled timer";
		} else {
			this.name = name;
		}

		this.name = name;
	}

	public void submitResult(boolean result) {
		this.submitResult(result, 1);
	}

	public void submitResult(boolean result, int multiplier) {
		if (result) {
			this.goodCount += multiplier;
		} else {
			if (this.isAbrupt) {
				this.goodCount = 0;
			} else {
				this.goodCount--;
			}
		}

		this.recalculateDelay();
	}

	public void touch() {
		this.touch(Calendar.getInstance().getTime());
	}

	public void touch(Date touchDate) {
		this.lastTouched = new Instant(touchDate.getTime());
	}

}
