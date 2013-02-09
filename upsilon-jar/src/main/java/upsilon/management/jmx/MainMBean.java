package upsilon.management.jmx;

import javax.management.DescriptorKey;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public interface MainMBean {
	@DescriptorKey("A count of group structures")
	public int getGroupCount();

	public int getMagicNumber();

	public int getServiceCount();

	public CollectionOfStructures<StructureService> getServices();

	public void reparseConfig();

	public void shutdown();
	public void databaseUpdate(); 
	public String guessNodeType();  
	public void runServiceBlitz();  
}
