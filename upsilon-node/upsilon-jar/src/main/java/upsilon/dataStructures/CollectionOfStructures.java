package upsilon.dataStructures;

import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map.Entry;
import java.util.Vector;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.configuration.CollectionAlterationTransaction;
import upsilon.configuration.XmlNodeHelper;

public class CollectionOfStructures<T extends ConfigStructure> implements Iterable<T> {
    private final Vector<T> collection = new Vector<T>();

    private static transient final Logger LOG = LoggerFactory.getLogger(CollectionOfStructures.class);
    private final String title;

    public CollectionOfStructures(final String title) {
        this.title = title;
    }

    private T constructElement(final XmlNodeHelper newElement) throws Exception {
        ConfigStructure s;

        switch (newElement.getNodeName()) {
        case "service":
            s = new StructureService();
            s.update(newElement);
            break;
        case "command":
            s = new StructureCommand();
            s.update(newElement);
            break;
        case "peer":
            s = new StructurePeer();
            s.update(newElement);
            break;
        default:
            throw new IllegalArgumentException("Cant construct structure with element name: " + newElement.getNodeName());
        }

        return (T) s;
    }

    public synchronized boolean contains(final T search) {
        return this.collection.contains(search);
    }

    public boolean containsId(final String id) {
        final Iterator<T> it = this.iterator();

        while (it.hasNext()) {
            if (it.next().getIdentifier().equals(id)) {
                return true;
            }
        }

        return false;
    }

    private void debugPrint() {
        CollectionOfStructures.LOG.trace("Collection (of type " + this.getTitle() + "): ");

        for (int i = 0; i < this.collection.size(); i++) {
            CollectionOfStructures.LOG.trace("Item {}: {}", new Object[] { i, this.collection.get(i).getIdentifier() });
        }
    }

    public T get(final String identifier) {
        for (final T cs : this.collection) {
            if (cs.getIdentifier().equals(identifier)) {
                return cs;
            }
        }

        return null;
    }

    public T getById(final String id) {
        for (final T struct : this) {
            if (struct.getIdentifier().equals(id)) {
                return struct;
            }
        }

        throw new NullPointerException("Structure with ID does not exist:" + id);
    }

    public Vector<String> getIds() {
        final Vector<String> ids = new Vector<String>();

        final Iterator<T> it = this.iterator();

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

    public String getTitle() {
        return this.title;
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

    public void processTransaction(final CollectionAlterationTransaction<?> cat) {
        synchronized (this.collection) {
            CollectionOfStructures.LOG.warn(cat + " Started (new: {}, old: {}, upd: {})", new Object[] { cat.getNew().size(), cat.getOld().size(), cat.getUpdated().size() });

            for (final String structureId : cat.getOldIds()) {
                CollectionOfStructures.LOG.warn(cat + " Removing: " + structureId);
                this.collection.remove(this.getById(structureId));
            }

            for (final Entry<String, XmlNodeHelper> newStructure : cat.getNew().entrySet()) {
                CollectionOfStructures.LOG.warn(cat + " Adding: " + newStructure.getKey());

                try {
                    final T s = this.constructElement(newStructure.getValue());

                    this.collection.add(s);
                } catch (final Exception e) {
                    CollectionOfStructures.LOG.error("Could not construct element" + e.getMessage(), e);
                    continue;
                }
            }

            for (final Entry<String, XmlNodeHelper> updStructure : cat.getUpdated().entrySet()) {
                CollectionOfStructures.LOG.warn(cat + " Updating:" + updStructure.getKey());

                final T existingStructure = this.getById(updStructure.getKey());
                existingStructure.update(updStructure.getValue());
            }

            CollectionOfStructures.LOG.warn(cat + " Finished");

            this.debugPrint();
        }
    }

    public synchronized void register(final T item) {
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
