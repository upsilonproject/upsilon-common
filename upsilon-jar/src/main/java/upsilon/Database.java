package upsilon;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Iterator;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.AbstractService;
import upsilon.dataStructures.StructureNode;
import upsilon.dataStructures.StructureRemoteService;
import upsilon.dataStructures.StructureService;

public class Database {
    public static void updateAll() {
        if (Database.instance != null) {
            Database.instance.update();
        }
    }

    private Connection conn;
    private final Logger log = LoggerFactory.getLogger(Database.class);
    private final String hostname;
    private final String username, password;
    private final int port;

    private final String dbname;

    public static Database instance;

    public Database(final String hostname, final String username, final String password, final int port, final String dbname) {
        this.hostname = hostname;
        this.username = username;
        this.password = password;
        this.port = port;
        this.dbname = dbname;
    }

    public void connect() throws Exception {
        Class.forName("com.mysql.jdbc.Driver");
        this.conn = DriverManager.getConnection("jdbc:mysql://" + this.hostname + ":" + this.port + "/" + this.dbname, this.username, this.password);

        this.log.debug("Connected to DB server: " + this.conn.getMetaData().getURL());
    }

    @Override
    public boolean equals(final Object obj) {
        if (obj instanceof Database) {
            final Database comp = (Database) obj;

            if (comp.hostname.equals(this.hostname) && (comp.port == this.port)) {
                return true;
            }
        }

        return super.equals(obj);
    }

    public int getInt(final String field, final String table, final String fieldEq, final String equals) {
        int ret = 0;
        final String sql = "SELECT " + field + " FROM " + table + " WHERE " + fieldEq + " = ?";
        ResultSet rs;

        try {
            final PreparedStatement stmt = this.conn.prepareStatement(sql);
            stmt.setString(1, equals);
            rs = stmt.executeQuery();
            rs.beforeFirst();

            if (!rs.first()) {
                throw new RuntimeException("Cannot get int, there where 0 rows");
            }

            ret = rs.getInt(1);
            stmt.close();
        } catch (final SQLException e) {
            e.printStackTrace();
        }

        return ret;
    }

    public HashMap<String, String> getRow(final String table, final String fieldEq, final String equals, final String... fields) {
        final StringBuilder fieldList = new StringBuilder();

        for (final String s : fields) {
            fieldList.append(s);
            fieldList.append(',');
        }

        final String sql = "SELECT " + fieldList.toString() + " id FROM " + table + " WHERE " + fieldEq + " = ?";

        try {
            final PreparedStatement stmt = this.conn.prepareStatement(sql);
            stmt.setString(1, equals);
            final ResultSet rs = stmt.executeQuery();
            rs.beforeFirst();

            final HashMap<String, String> row = new HashMap<>();

            if (rs.next()) {
                for (int i = 0; i < fields.length; i++) {
                    row.put(fields[i], rs.getString(i + 1));
                }
            }

            stmt.close();
            rs.close();

            return row;
        } catch (final SQLException e) {
            e.printStackTrace();
        }

        return null;
    }

    private boolean getValidConnection() {
        if (this.conn == null) {
            return false;
        }

        try {
            if (this.conn.isClosed()) {
                this.connect();

                if (this.conn.isClosed()) {
                    return false;
                }
            }
        } catch (final Exception e) {
            this.log.error("SQL Exception while checking connection validity", e);
            return false;
        }

        return true;
    }

    @Override
    public String toString() {
        return String.format("host: %s, user: %s, port: %d, dbname: %s", this.hostname, this.username, this.port, this.dbname);
    }

    public void update() {
        if (this.getValidConnection()) {
            Main.instance.node.refresh();
            this.updateNode(Main.instance.node);

            synchronized (Configuration.instance.remoteNodes) {
                final Iterator<StructureNode> itNodes = Configuration.instance.remoteNodes.iterator();

                this.log.trace("Items in remote node list: " + Configuration.instance.remoteNodes.size());

                while (itNodes.hasNext()) {
                    this.updateNode(itNodes.next());
                    itNodes.remove();
                }
            }

            // insert services from our local executer
            synchronized (Configuration.instance.services) {
                for (final StructureService s : upsilon.Configuration.instance.services) {
                    this.updateService(s);
                }
            }

            // insert services sent remotely
            synchronized (Configuration.instance.remoteServices) {
                final Iterator<StructureRemoteService> it = upsilon.Configuration.instance.remoteServices.iterator();

                while (it.hasNext()) {
                    this.updateService(it.next());
                    it.remove();
                }
            }
        } else {
            this.log.error("Connection to DB is invalid, cannot update.");
        }
    }

    private void updateNode(final StructureNode n) {
        if (!n.isDatabaseUpdateRequired()) {
            this.log.trace("Remote node update not required for: " + n.toString());
            return;
        }

        this.log.debug("Updating node: " + n.getIdentifier() + "(type: " + n.getType() + ")");

        int pindex = 0;
        final String sql = "INSERT INTO nodes (identifier, serviceType, lastUpdated) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lastUpdated = ?, serviceCount = ?, instanceApplicationVersion = ? ";

        try {
            final PreparedStatement pstmt = this.conn.prepareStatement(sql);
            pstmt.setString(++pindex, n.getIdentifier());
            pstmt.setString(++pindex, n.getType());
            pstmt.setTimestamp(++pindex, new Timestamp(Calendar.getInstance().getTime().getTime()));
            pstmt.setTimestamp(++pindex, new Timestamp(Calendar.getInstance().getTime().getTime()));
            pstmt.setInt(++pindex, n.getServiceCount());
            pstmt.setString(++pindex, n.getInstanceApplicationVersion());
            pstmt.execute();
            pstmt.close();
        } catch (final Exception e) {
            this.log.error(e.getMessage());
        }

        n.setDatabaseUpdateRequired(false);
    }

    private void updateService(final AbstractService s) {
        if (!s.isDatabaseUpdateRequired()) {
            return;
        }

        if (!s.isRegistered()) {
            return;
        }

        this.log.debug("updating service:" + s.getIdentifier());

        String sql = "INSERT INTO services (identifier, description, executable, karma) VALUES (?, ?, ?, '') ON DUPLICATE KEY UPDATE karma = ?, secondsRemaining = ?, output = ?, commandLine = ?, lastUpdated = ?, goodCount = ?, estimatedNextCheck = ?, isLocal = ?, node = ?";

        try {
            int paramIndex = 1;
            final PreparedStatement stmt = this.conn.prepareStatement(sql);
            stmt.setString(paramIndex++, s.getIdentifier());
            stmt.setString(paramIndex++, s.getDescription());
            stmt.setString(paramIndex++, s.getExecutable());

            stmt.setString(paramIndex++, s.getKarmaString());
            stmt.setLong(paramIndex++, s.getSecondsRemaining());
            stmt.setString(paramIndex++, s.getOutput());
            stmt.setString(paramIndex++, s.getFinalCommandLine(s));
            stmt.setTimestamp(paramIndex++, new java.sql.Timestamp(s.getLastUpdated().toDate().getTime()));
            stmt.setLong(paramIndex++, s.getResultConsequtiveCount());
            stmt.setTimestamp(paramIndex++, new java.sql.Timestamp(s.getEstimatedNextCheck().toDate().getTime()));
            stmt.setBoolean(paramIndex++, s.isLocal());
            stmt.setString(paramIndex++, s.getNodeIdentifier());

            stmt.execute();
            stmt.close();
        } catch (final Exception e) {
            this.log.error("Insert new/update service: " + s.getDescription(), e);
        }

        sql = "INSERT INTO service_check_results (service, checked, karma, output) VALUES (?, ?, ?, ?) ";

        try {
            final PreparedStatement stmt = this.conn.prepareStatement(sql);
            stmt.setString(1, s.getIdentifier());
            stmt.setTimestamp(2, new java.sql.Timestamp(s.getLastUpdated().toDate().getTime()));
            stmt.setString(3, s.getKarmaString());
            stmt.setString(4, s.getOutput());
            stmt.execute();
            stmt.close();
        } catch (final Exception e) {
            this.log.error("Cannot insert service check result: " + e);
        }

        s.setDatabaseUpdateRequired(false);
    }
}
