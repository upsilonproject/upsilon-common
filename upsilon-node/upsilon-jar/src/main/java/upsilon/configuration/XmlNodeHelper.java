package upsilon.configuration;

import java.util.Vector;

import org.joda.time.Duration;
import org.joda.time.Period;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class XmlNodeHelper {
    private final Node node;
    private XmlNodeHelper parent;

    public XmlNodeHelper(final Node node) {
        this.node = node;
    }
    
    public <T> T getAttributeValue(final String key, final T def) {
        return this.getAttributeValueOrParentOrDefault(key, def);
    }

    public <T> T getAttributeValueOrDefault(final String key, final T def) {
        final Node attr = this.node.getAttributes().getNamedItem(key);

        if (attr == null) {
            return def;
        } else {
            final String value = attr.getNodeValue();

            if (def instanceof Integer) {
                return (T) (Integer) Integer.parseInt(value);
            } else if (def instanceof Boolean) {
                return (T) (Boolean) Boolean.parseBoolean(value);
            } else {
                return (T) attr.getNodeValue();
            }
        }
    }

    public <T> T getAttributeValueOrParentOrDefault(final String key, final T def) {
        if (this.hasAttribute(key)) {
            final String val = this.getAttributeValueUnchecked(key);

            if (def instanceof Duration) {
                return (T) Period.parse(val).toStandardDuration();
            } else if (def instanceof Integer) {
                return (T) new Integer(Integer.parseInt(val));
            } else {
                return (T) val;
            }
        } else {
            if (this.parent == null) {
                return def;
            } else {
                return this.parent.getAttributeValueOrParentOrDefault(key, def);
            }
        }
    }

    public String getAttributeValueUnchecked(final String string) {
        return this.node.getAttributes().getNamedItem(string).getNodeValue();
    }

    public Vector<XmlNodeHelper> getChildElements(final String string) {
        final Vector<XmlNodeHelper> xmlHelperChildren = new Vector<XmlNodeHelper>();
        final NodeList children = this.node.getChildNodes();

        for (int i = 0; i < children.getLength(); i++) {
            final Node n = children.item(i);

            if (n.getNodeName().equals(string)) {
                xmlHelperChildren.add(new XmlNodeHelper(n));
            }
        }

        return xmlHelperChildren;
    }

    public XmlNodeHelper getFirstChildElement(final String string) {
        for (int i = 0; i < this.node.getChildNodes().getLength(); i++) {
            final Node n = this.node.getChildNodes().item(i);

            if (n.getNodeName().equals(string)) {
                return new XmlNodeHelper(n);
            }
        }

        return null;
    }

    public String getNodeName() {
        return this.node.getNodeName();
    }

    public String getNodeValue() {
        return this.node.getFirstChild().getNodeValue();
    }

    public XmlNodeHelper getParent() {
        return this.parent;
    }

    public boolean hasAttribute(final String string) {
        return this.node.getAttributes().getNamedItem(string) != null;
    }

    public boolean hasChildElement(final String string) {
        return this.getFirstChildElement(string) != null;
    }

    public void setParent(final XmlNodeHelper search) {
        this.parent = search;
    }

	public String getSource() {
		return source;
	}
	
	private String source = "";
	
	public void setSource(String source) {
		this.source = source;
	}
}
