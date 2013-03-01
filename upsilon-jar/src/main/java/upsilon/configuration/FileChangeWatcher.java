package upsilon.configuration;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class FileChangeWatcher {
    interface Listener {
        public void fileChanged(File f);
    }

    private static final transient Logger LOG = LoggerFactory.getLogger(FileChangeWatcher.class);

    private File fileBeingWatched;
    private long mtime = 0;
    private final Listener l;
    private final Thread t;
    private boolean continueMonitoring = true;

    public FileChangeWatcher(File f, Listener l) {
        this.fileBeingWatched = f;
        this.l = l;
        this.t = new Thread("File watcher for: " + this.fileBeingWatched.getName()) {
            @Override
            public void run() {
                FileChangeWatcher.this.watchForChanges();
            }
        };
    }

    public void checkForModification() {
        LOG.trace("Checking file for modification: " + this.mtime + " vs " + this.fileBeingWatched.getAbsolutePath() + " " + this.fileBeingWatched.lastModified());

        if (this.mtime < this.fileBeingWatched.lastModified()) {
            this.mtime = this.fileBeingWatched.lastModified();

            LOG.debug("Configuration file has changed, notifying listeners.");
            this.l.fileChanged(this.fileBeingWatched);
        }
    }

    public void setWatchedFile(File f) {
        this.fileBeingWatched = f;
    }

    public void start() {
        this.t.start();
    }

    public void stop() {
        this.continueMonitoring = false;
    }

    private void watchForChanges() {
        while (FileChangeWatcher.this.continueMonitoring) {
            this.checkForModification();

            try {
                Thread.sleep(2000);
            } catch (InterruptedException e) {
                e.printStackTrace();
                break;
            }
        }
    }
}
