package upsilon.dataStructures;

public enum ResultKarma {
    GOOD, WARNING, BAD, UNKNOWN, TIMEOUT, SKIPPED;

    public static ResultKarma valueOfOrUnknown(final String value) {
        try {
            return ResultKarma.valueOf(value);
        } catch (final IllegalArgumentException e) {
            return ResultKarma.UNKNOWN;
        }
    }
}
