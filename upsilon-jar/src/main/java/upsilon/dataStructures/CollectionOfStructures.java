package upsilon.dataStructures;

import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Vector;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class CollectionOfStructures<T extends ConfigStructure> implements
        Iterable<T> {
    private final Vector<T> collection = new Vector<T>();

    private static transient final Logger LOG = LoggerFactory
            .getLogger(CollectionOfStructures.class);

    public synchronized boolean contains(T search) {
        return this.collection.contains(search);
    }

    public synchronized T get(String identifier) {
        for (T cs : this.collection) {
            if (cs.getIdentifier().equals(identifier)) {
                return cs;
            }
        }

        return null;
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
            CollectionOfStructures.LOG.trace("Registered structure: "
                    + item.getClassAndIdentifier());
        } else {
            CollectionOfStructures.LOG.warn("Not registering duplicate: "
                    + item.getClassAndIdentifier());
        }
    }

    public synchronized int size() {
        return this.collection.size();
    }
}
