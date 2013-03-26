package upsilon.configuration;

import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.Vector;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Node;

import upsilon.dataStructures.CollectionOfStructures;
import upsilon.dataStructures.ConfigStructure;

public class CollectionAlterationTransaction<T extends ConfigStructure> {
    private static int catIndex = 0;

    private final int id;
    private final static transient Logger LOG = LoggerFactory.getLogger(CollectionAlterationTransaction.class);
    private final CollectionOfStructures<T> list;
    private final HashMap<String, Node> newList = new HashMap<>();
    private Vector<String> oldList = new Vector<String>();
    private final HashMap<String, Node> updList = new HashMap<>();

    public CollectionAlterationTransaction(final CollectionOfStructures<T> list) {
        this.list = list;
        this.oldList = list.getIds();
        this.id = ++CollectionAlterationTransaction.catIndex;
    }

    public void considerFromConfig(final Node el) {
        final String idFromConfig = el.getAttributes().getNamedItem("id").getNodeValue();

        if (this.list.containsId(idFromConfig)) {
            this.updList.put(idFromConfig, el);
            this.oldList.remove(idFromConfig);
        } else {
            this.newList.put(idFromConfig, el);
        }
    }

    public Map<String, Node> getNew() {
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

    public Map<String, Node> getUpdated() {
        return this.updList;
    }

    public Set<String> getUpdatedIds() {
        return this.updList.keySet();
    }

    public void print() {
        this.printList("new", this.getNewIds());
        this.printList("old", this.getOldIds());
        this.printList("upd", this.getUpdatedIds());

        CollectionAlterationTransaction.LOG.info("End of transactions");
    }

    public void printList(final String name, final Collection<String> col) {
        if (col.isEmpty()) {
            return;
        }

        CollectionAlterationTransaction.LOG.info(name);
        CollectionAlterationTransaction.LOG.info("---------");

        for (final String s : col) {
            CollectionAlterationTransaction.LOG.info("* " + s);
        }

        CollectionAlterationTransaction.LOG.info("");
    }

    @Override
    public String toString() {
        return "CAT<" + this.list.getTitle() + "> #" + this.id;
    }
}
