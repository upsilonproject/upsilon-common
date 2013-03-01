package upsilon.configuration;

import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.Vector;

import org.jdom2.Element;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.ConfigStructure;

public class CollectionAlterationTransaction<T extends ConfigStructure> {
    private final static transient Logger LOG = LoggerFactory.getLogger(CollectionAlterationTransaction.class);
    private final CollectionOfStructures<T> list;
    private final HashMap<String, Element> newList = new HashMap<>();
    private Vector<String> oldList = new Vector<String>();
    private final HashMap<String, Element> updList = new HashMap<>();

    public CollectionAlterationTransaction(CollectionOfStructures<T> list) {
        this.list = list;
        this.oldList = list.getIds();
    }

    public void considerFromConfig(Element el) {
        String idFromConfig = el.getAttribute("id").getValue();

        if (this.list.containsId(idFromConfig)) {
            this.updList.put(idFromConfig, el);
            this.oldList.remove(idFromConfig);
        } else {
            this.newList.put(idFromConfig, el);
        }
    }

    public Map<String, Element> getNew() {
        return this.newList;
    }

    public Set<String> getNewIds() {
        return this.newList.keySet();
    }

    public Vector<String> getOld() {
        return this.oldList;
    }

    public List<String> getOldIds() {
        return this.oldList;
    }

    public Map<String, Element> getUpdated() {
        return this.updList;
    }

    public Set<String> getUpdatedIds() {
        return this.updList.keySet();
    }

    public void print() {
        this.printList("new", this.getNewIds());
        this.printList("old", this.getOldIds());
        this.printList("upd", this.getUpdatedIds());

        LOG.info("End of transactions");
    }

    public void printList(String name, Collection<String> col) {
        if (col.isEmpty()) {
            return;
        }

        LOG.info(name);
        LOG.info("---------");

        for (String s : col) {
            LOG.info("* " + s);
        }

        LOG.info("");
    }
}
