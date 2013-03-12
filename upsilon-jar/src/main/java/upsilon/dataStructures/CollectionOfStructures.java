package upsilon.dataStructures;

import java.lang.reflect.ParameterizedType;
import java.lang.reflect.Type;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map.Entry;
import java.util.Vector;

import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Node;

import upsilon.configuration.CollectionAlterationTransaction;

public class CollectionOfStructures<T extends ConfigStructure> implements Iterable<T> {
    private final Vector<T> collection = new Vector<T>();

    private static transient final Logger LOG = LoggerFactory.getLogger(CollectionOfStructures.class);

    private T constructElement(final Node newElement) {
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
        CollectionOfStructures.LOG.debug("Collection (of type " + this.getType() + "): ");

        for (final T t : this) {
            CollectionOfStructures.LOG.debug("* ID: " + t.getIdentifier());
        }
    }

    public synchronized T get(final String identifier) {
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

    public Class<T> getType() {
        final Type superClass;
        try {
            CollectionOfStructures.LOG.debug("len " + ((Class<?>) ((ParameterizedType) this.getClass().getMethod("getImmutable").getGenericReturnType()).getActualTypeArguments()[0]).getSimpleName());
        } catch (final SecurityException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (final NoSuchMethodException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        return null;
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
        synchronized (this) {
            CollectionOfStructures.LOG.warn(cat + " Started");

            for (final String structureId : cat.getOldIds()) {
                CollectionOfStructures.LOG.warn(cat + " Removing: " + structureId);
                this.collection.remove(this.getById(structureId));
            }

            for (final Entry<String, Node> newStructure : cat.getNew().entrySet()) {
                CollectionOfStructures.LOG.warn(cat + " Adding: " + newStructure.getKey());

                final T s = this.constructElement(newStructure.getValue());

                this.collection.add(s);
            }

            for (final Entry<String, Node> updStructure : cat.getUpdated().entrySet()) {
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
