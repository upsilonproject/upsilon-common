package upTests.customMatchers;

import org.hamcrest.BaseMatcher;
import org.hamcrest.Description;

public class RegexMatcher extends BaseMatcher<String> {
    public static RegexMatcher matches(final String regex) {
        return new RegexMatcher(regex);
    }

    private final String regex;

    public RegexMatcher(final String regex) {
        this.regex = regex;
    }

    @Override
    public void describeTo(final Description description) {
        description.appendText("matches regex=" + this.regex);
    }

    @Override
    public boolean matches(final Object o) {
        return ((String) o).matches(this.regex);

    }
}
