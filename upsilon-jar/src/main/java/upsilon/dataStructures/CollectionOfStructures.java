package upsilon.dataStructures;

import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map.Entry;
import java.util.Vector;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import org.jdom2.Element;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.configuration.CollectionAlterationTransaction;

public class CollectionOfStructures<T extends ConfigStructure> implements Iterable<T> {
    private final Vector<T> collection = new Vector<T>();

    private static transient final Logger LOG = LoggerFactory.getLogger(CollectionOfStructures.class);

    private T constructElement(Element newElement) {
        ConfigStructure s;

        switch (newElement.getName()) {
        case "service":
            s = new StructureService();
            s.update(newElement);
            break;
        default:
            throw new IllegalArgumentException("Cant construct structure with element name: " + newElement.getName());
        }

        return (T) s;
    }

    public synchronized boolean contains(T search) {
        return this.collection.contains(search);
    }

    public boolean containsId(String id) {
        Iterator<T> it = this.iterator();

        while (it.hasNext()) {
            if (it.next().getIdentifier().equals(id)) {
                return true;
            }
        }

        return false;
    }

    private void debugPrint() {
        for (T t : this) {
            LOG.debug("ID: " + t.getIdentifier());
        }
    }

    public synchronized T get(String identifier) {
        for (T cs : this.collection) {
            if (cs.getIdentifier().equals(identifier)) {
                return cs;
            }
        }

        return null;
    }

    public T getById(String id) {
        for (T struct : this) {
            if (struct.getIdentifier().equals(id)) {
                return struct;
            }
        }

        throw new NullPointerException("Structure with ID does not exist:" + id);
    }

    public Vector<String> getIds() {
        Vector<String> ids = new Vector<String>();

        Iterator<T> it = this.iterator();

        while (it.hasNext()) {
            ids.add(it.next().getIdentifier());
        }

        return ids;
    }

    @XmlElement
    @XmlElementWrapper
    public List<T> getImmutable() {
        return Collections.unmodifiableList(this.collection);
    }

    public synchronized boolean isEmpty() {
        return this.size() == 0;
    }

    @Override
    public Iterator<T> iterator() {
        return this.collection.iterator();
    }

    public CollectionAlterationTransaction<T> newTransaction() {
        return new CollectionAlterationTransaction<T>(this);
    }

    public void processTransaction(CollectionAlterationTransaction<?> cat) {
        synchronized (this) {
            for (String structureId : cat.getOldIds()) {
                this.collection.remove(this.getById(structureId));
            }

            for (Entry<String, Element> newStructure : cat.getNew().entrySet()) {
                T s = this.constructElement(newStructure.getValue());

                this.collection.add(s);
            }

            for (Entry<String, Element> updStructure : cat.getUpdated().entrySet()) {
                T existingStructure = this.getById(updStructure.getKey());
                existingStructure.update(updStructure.getValue());
            }

            this.debugPrint();
        }
    }

    public synchronized void register(T item) {
        boolean added;

        if (this.collection.contains(item)) {
            added = false;
        } else {
            this.collection.add(item);
            added = true;
        }

        item.setDatabaseUpdateRequired(true);

        if (added) {
            CollectionOfStructures.LOG.trace("Registered structure: " + item.getClassAndIdentifier());
        } else {
            CollectionOfStructures.LOG.warn("Not registering duplicate: " + item.getClassAndIdentifier());
        }
    }

    public synchronized int size() {
        return this.collection.size();
    }
}
