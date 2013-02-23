package upsilon.management.jmx;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public interface MainMBean {
	public void databaseUpdate();

	public int getMagicNumber();

	public int getServiceCount();

	public CollectionOfStructures<StructureService> getServices();

	public String guessNodeType();

	public void reparseConfig();

	public void runServiceBlitz();

	public void shutdown();
}
