package upTests;

import static org.hamcrest.Matchers.equalTo;
import static org.hamcrest.Matchers.hasSize;
import static org.hamcrest.Matchers.not;
import static org.hamcrest.Matchers.notNullValue;

import org.junit.AfterClass;
import org.junit.Assert;
import org.junit.BeforeClass;
import org.junit.Test;

import upsilon.Configuration;
import upsilon.Main;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;

public class StructureEqualityTest {
	@BeforeClass
	@AfterClass
	public static void setupConfig() {
		Configuration.instance.clear();
	}

	@Test
	public void testEqualCommands() {
		final CollectionOfStructures<StructureCommand> col = new CollectionOfStructures<>("testingStructure");

		final StructureCommand c1 = new StructureCommand();
		final StructureCommand c2 = new StructureCommand();

		c1.setIdentifier("one");
		c2.setIdentifier("two");
		Assert.assertThat(c1, not(c2));

		c1.setIdentifier("pie");
		c2.setIdentifier("pie");
		Assert.assertThat(c1, equalTo(c2));

		col.register(c1);
		Assert.assertTrue(col.contains(c2));

		col.register(c1);
		col.register(c2);
		col.register(c1);

		Assert.assertEquals(1, col.size());
	}

	@Test
	public void testEqualServices() {
		final StructureCommand check_command = new StructureCommand();

		final StructureService s1 = new StructureService();
		final StructureService s2 = new StructureService();

		s1.setIdentifier("one");
		s1.setCommandWithOnlyPositionalArgs(check_command, "check_pie!foo");
		s2.setIdentifier("two");
		s1.setCommandWithOnlyPositionalArgs(check_command, "check_pie!bar");
		Assert.assertThat(s1, notNullValue());
		Assert.assertThat(s1, not(s2));

		s1.setIdentifier("pie");
		s2.setIdentifier("pie");
		Assert.assertThat(s1, equalTo(s2));

		s1.setCommandWithOnlyPositionalArgs(check_command, "check_pie!foo");
		s2.setCommandWithOnlyPositionalArgs(check_command, "check_pie!bar");
		Assert.assertThat(s1, equalTo(s2));

		Assert.assertThat(s1, not(new Object()));
	}

	@Test
	public void testEssentialSrsAttrs() {
		StructureRemoteService srs = new StructureRemoteService();
		Assert.assertFalse(srs.isLocal());
		Assert.assertTrue(srs.isRegistered());
		Assert.assertThat(srs.getArguments().keySet(), hasSize(0));
	}

	@Test
	public void testLocalNode() {
		StructureNode n = new StructureNode();
		n.refresh();

		Assert.assertEquals(Main.getVersion(), n.getInstanceApplicationVersion());
		Assert.assertEquals(0, n.getServiceCount());
	}
}
