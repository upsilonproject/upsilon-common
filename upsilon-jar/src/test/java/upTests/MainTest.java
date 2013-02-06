package upTests;

import org.junit.Assert;
import org.junit.Test;

import upTests.customMatchers.RegexMatcher;
import upsilon.Database;
import upsilon.Main;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructurePeer;

public class MainTest {
	@Test
	public void testGetters() {
		Assert.assertNotNull(null, Main.instance.getDaemons());
	}

	@Test
	public void testNodeType() throws Exception {
		CollectionOfStructures<StructurePeer> peers = new CollectionOfStructures<>();

		Assert.assertEquals("useless-testing-node", Main.instance.guessNodeType(null, peers));

		Assert.assertEquals("super-node", Main.instance.guessNodeType(new Database(null, null, null, 0, null), peers));

		peers.register(new StructurePeer("localhost", 100));
		Assert.assertEquals("service-node", Main.instance.guessNodeType(null, peers));

		Assert.assertEquals("non-standard-node", Main.instance.guessNodeType(new Database(null, null, null, 0, null), peers));
	}

	@Test
	public void testVersion() {
		String releaseVersion = Main.getVersion();

		Assert.assertThat(releaseVersion, RegexMatcher.matches("\\d+\\.\\d+\\.\\d+"));
	}
}
