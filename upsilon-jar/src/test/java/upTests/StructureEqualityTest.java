package upTests;

import org.hamcrest.CoreMatchers;
import org.junit.Assert;
import org.junit.Test;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureCommand;
import upsilon.dataStructures.StructureService;

public class StructureEqualityTest {
	@Test
	public void testEqualCommands() {
		CollectionOfStructures<StructureCommand> col = new CollectionOfStructures<>();

		StructureCommand c1 = new StructureCommand();
		StructureCommand c2 = new StructureCommand();

		c1.setName("one");
		c2.setName("two");
		Assert.assertThat(c1, CoreMatchers.not(c2));

		c1.setName("pie");
		c2.setName("pie");
		Assert.assertThat(c1, CoreMatchers.equalTo(c2));

		col.register(c1);
		Assert.assertTrue(col.contains(c2));

		col.register(c1);
		col.register(c2);
		col.register(c1);

		Assert.assertEquals(1, col.size());
	}

	@Test
	public void testEqualServices() {
		StructureCommand check_pie = new StructureCommand();

		StructureService s1 = new StructureService();
		StructureService s2 = new StructureService();

		s1.setDescription("one");
		s1.setCommand(check_pie, "check_pie!foo");
		s2.setDescription("two");
		s1.setCommand(check_pie, "check_pie!bar");
		Assert.assertThat(s1, CoreMatchers.not(s2));

		s1.setDescription("pie");
		s2.setDescription("pie");
		Assert.assertThat(s1, CoreMatchers.not(s2));

		s1.setCommand(check_pie, "check_pie!foo");
		s2.setCommand(check_pie, "check_pie!bar");
		Assert.assertThat(s1, CoreMatchers.equalTo(s2));
	}
}
