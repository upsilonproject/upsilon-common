package upsilon.dataStructures;

public enum ResultKarma {
    GOOD, WARNING, BAD, UNKNOWN, TIMEOUT, SKIPPED;
     
    public static ResultKarma valueOfOrUnknown(String value) {
        try { 
            return ResultKarma.valueOf(value);
        } catch (IllegalArgumentException e) {
            return ResultKarma.UNKNOWN;
        } 
    }
}
