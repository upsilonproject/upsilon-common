package upsilon.management.jmx;

import java.lang.reflect.Method;

import javax.management.DescriptorKey;
import javax.management.MBeanInfo;
import javax.management.MBeanOperationInfo;
import javax.management.NotCompliantMBeanException;
import javax.management.StandardMBean;

import upsilon.Configuration;
import upsilon.Database;
import upsilon.Main;
import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.StructureService;

public class MainMBeanImpl extends StandardMBean implements MainMBean {
    public MainMBeanImpl() throws NotCompliantMBeanException {
        super(MainMBean.class);
    }

    @Override
    public void databaseUpdate() {
        Database.instance.update();
    }

    @Override
    protected String getDescription(final MBeanInfo info) {
        return "The main MBean";
    }

    @Override
    protected String getDescription(final MBeanOperationInfo info) {
        try {
            final Method m = this.getClass().getMethod(info.getName(), (Class<?>[]) null);
            final DescriptorKey dk = m.getAnnotation(DescriptorKey.class);

            if (dk != null) {
                return dk.value();
            }
        } catch (NoSuchMethodException | SecurityException e) {
            e.printStackTrace();
        }

        return "nodesc";
    }

    @Override
    public int getMagicNumber() {
        return 1337;
    }

    @Override
    public int getServiceCount() {
        return Configuration.instance.services.size();
    }

    @Override
    public CollectionOfStructures<StructureService> getServices() {
        return Configuration.instance.services;
    }

    @Override
    public String guessNodeType() {
        return Main.instance.guessNodeType();
    }

    @Override
    public void reparseConfig() {
        Main.instance.getXmlConfigurationLoader().reparse();
    }

    /**
     * public void queueService(String serviceIdentifier) { StructureService s =
     * Configuration.instance.services.get(serviceIdentifier);
     * 
     * Main.instance.queueMaintainer.queueUrgent(s); }
     */

    @Override
    public void runServiceBlitz() {
        for (final StructureService s : Configuration.instance.services) {
            Main.instance.queueMaintainer.queueUrgent(s);
        }
    }

    @Override
    @DescriptorKey("Shutdown Upsilon")
    public void shutdown() {
        Main.instance.shutdown();
    }
}
