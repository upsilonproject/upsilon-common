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
import upsilon.dataStructures.StructureGroup;
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

	public Database(String hostname, String username, String password, int port, String dbname) {
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

		this.init();
	}

	@Override
	public boolean equals(Object obj) {
		if (obj instanceof Database) {
			Database comp = (Database) obj;

			if (comp.hostname.equals(this.hostname) && (comp.port == this.port)) {
				return true;
			}
		}

		return super.equals(obj);
	}

	public int getInt(String field, String table, String fieldEq, String equals) {
		int ret = 0;
		String sql = "SELECT " + field + " FROM " + table + " WHERE " + fieldEq + " = ?";
		ResultSet rs;

		try {
			PreparedStatement stmt = this.conn.prepareStatement(sql);
			stmt.setString(1, equals);
			rs = stmt.executeQuery();
			rs.beforeFirst();

			if (!rs.first()) {
				throw new RuntimeException("Cannot get int, there where 0 rows");
			}

			ret = rs.getInt(1);
			stmt.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}

		return ret;
	}

	public HashMap<String, String> getRow(String table, String fieldEq, String equals, String... fields) {
		StringBuilder fieldList = new StringBuilder();

		for (String s : fields) {
			fieldList.append(s);
			fieldList.append(',');
		}

		String sql = "SELECT " + fieldList.toString() + " id FROM " + table + " WHERE " + fieldEq + " = ?";

		try {
			PreparedStatement stmt = this.conn.prepareStatement(sql);
			stmt.setString(1, equals);
			ResultSet rs = stmt.executeQuery();
			rs.beforeFirst();

			HashMap<String, String> row = new HashMap<>();

			if (rs.next()) {
				for (int i = 0; i < fields.length; i++) {
					row.put(fields[i], rs.getString(i + 1));
				}
			}

			stmt.close();
			rs.close();

			return row;
		} catch (SQLException e) {
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
		} catch (Exception e) {
			this.log.error("SQL Exception while checking connection validity", e);
			return false;
		}

		return true;
	}

	private void init() {
		this.updateGroupsDeleteRedundant();
	}

	public void update() {
		if (this.getValidConnection()) {
			Main.instance.node.refresh();
			this.updateNode(Main.instance.node);

			synchronized (Configuration.instance.remoteNodes) {
				Iterator<StructureNode> itNodes = Configuration.instance.remoteNodes.iterator();

				this.log.trace("Items in remote node list: " + Configuration.instance.remoteNodes.size());

				while (itNodes.hasNext()) {
					this.updateNode(itNodes.next());
					itNodes.remove();
				}
			}

			// insert services from our local executer
			synchronized (Configuration.instance.services) {
				for (StructureService s : upsilon.Configuration.instance.services) {
					this.updateService(s);
				}
			}

			// insert services sent remotely
			synchronized (Configuration.instance.remoteServices) {
				Iterator<StructureRemoteService> it = upsilon.Configuration.instance.remoteServices.iterator();

				while (it.hasNext()) {
					this.updateService(it.next());
					it.remove();
				}
			}

			// update groups
			this.updateGroups();
		} else {
			this.log.error("Connection to DB is invalid, cannot update.");
		}
	}

	private void updateGroups() {
		String sql;

		try {
			this.conn.setAutoCommit(false);

			for (StructureGroup g : upsilon.Configuration.instance.groups) {
				sql = "INSERT INTO groups (name, description, parent) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE description = ?, parent = ?";

				this.log.trace("Updating group: " + g.getIdentifier());

				try {
					PreparedStatement pstmt = this.conn.prepareStatement(sql);
					pstmt.setString(1, g.getName());
					pstmt.setString(2, g.getDescription());
					pstmt.setString(3, g.getParent());
					pstmt.setString(4, g.getDescription());
					pstmt.setString(5, g.getParent());
					pstmt.execute();
					pstmt.close();
				} catch (Exception e) {
					this.log.error("Could not insert a new group", e);
				}

				for (AbstractService s : g.getServices()) {
					if (!s.isRegistered()) {
						continue;
					}

					this.log.trace("Updating group membership for " + s.getIdentifier() + " in group " + g.getIdentifier());

					try {
						sql = "INSERT INTO group_memberships (`group`, service) VALUES (?, ?) ON DUPLICATE KEY UPDATE service = service";

						PreparedStatement pstmt = this.conn.prepareStatement(sql);
						pstmt.setString(1, g.getName());
						pstmt.setString(2, s.getIdentifier());
						pstmt.execute();
						pstmt.close();
					} catch (Exception e) {
						this.log.error("Could not insert a new group mebership", e);
					}
				}
			}

			this.conn.commit();
			this.conn.setAutoCommit(true);
		} catch (SQLException e) {
			this.log.error("SQL exception", e);
		}
	}

	private void updateGroupsDeleteRedundant() {
		try {
			String sql = "DELETE FROM group_memberships WHERE service NOT IN (SELECT identifier FROM services)";
			PreparedStatement stmt = this.conn.prepareStatement(sql);
			int rows = stmt.executeUpdate();
			stmt.close();

			if (rows > 0) {
				this.log.warn("Deleted " + rows + " redundant group memberships");
			}
		} catch (SQLException e) {
			this.log.warn("Could not delete redundant groups: " + e.getMessage());
		}
	}

	private void updateNode(StructureNode n) {
		if (!n.isDatabaseUpdateRequired()) {
			this.log.trace("Remote node update not required for: " + n.toString());
			return;
		}

		this.log.debug("Updating node: " + n.getIdentifier() + "(type: " + n.getType() + ")");

		int pindex = 0;
		String sql = "INSERT INTO nodes (identifier, serviceType, lastUpdated) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lastUpdated = ?, serviceCount = ?, instanceApplicationVersion = ? ";

		try {
			PreparedStatement pstmt = this.conn.prepareStatement(sql);
			pstmt.setString(++pindex, n.getIdentifier());
			pstmt.setString(++pindex, n.getType());
			pstmt.setTimestamp(++pindex, new Timestamp(Calendar.getInstance().getTime().getTime()));
			pstmt.setTimestamp(++pindex, new Timestamp(Calendar.getInstance().getTime().getTime()));
			pstmt.setInt(++pindex, n.getServiceCount());
			pstmt.setString(++pindex, n.getInstanceApplicationVersion());
			pstmt.execute();
			pstmt.close();
		} catch (Exception e) {
			this.log.error(e.getMessage());
		}

		n.setDatabaseUpdateRequired(false);
	}

	private void updateService(AbstractService s) {
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
			PreparedStatement stmt = this.conn.prepareStatement(sql);
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
		} catch (Exception e) {
			this.log.error("Insert new/update service: " + s.getDescription(), e);
		}

		sql = "INSERT INTO service_check_results (service, checked, karma, output) VALUES (?, ?, ?, ?) ";

		try {
			PreparedStatement stmt = this.conn.prepareStatement(sql);
			stmt.setString(1, s.getIdentifier());
			stmt.setTimestamp(2, new java.sql.Timestamp(s.getLastUpdated().toDate().getTime()));
			stmt.setString(3, s.getKarmaString());
			stmt.setString(4, s.getOutput());
			stmt.execute();
			stmt.close();
		} catch (Exception e) {
			this.log.error("Cannot insert service check result: " + e);
		}

		s.setDatabaseUpdateRequired(false);
	}
}
