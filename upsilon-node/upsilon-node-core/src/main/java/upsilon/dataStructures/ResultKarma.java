package upsilon.dataStructures;


public enum ResultKarma {
	GOOD, WARNING, BAD, UNKNOWN, TIMEOUT, SKIPPED;

	public static ResultKarma fromProcessExitCode(int code) {
		switch (code) {
		case 0:
			return ResultKarma.GOOD;
		case 1:
			return ResultKarma.WARNING;
		case 2:
			return ResultKarma.BAD;
		default:
			return ResultKarma.UNKNOWN;
		}
	}

	public static ResultKarma valueOfOrUnknown(final String value) {
		try {
			return ResultKarma.valueOf(value);
		} catch (final IllegalArgumentException e) {
			return ResultKarma.UNKNOWN;
		}
	}
}
