package upsilon;

import java.util.Collections;
import java.util.Vector;

import org.joda.time.Duration;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;
import upsilon.util.Util;

public class DaemonScheduler extends Daemon {
    private final Vector<StructureService> queue = new Vector<StructureService>();

    private boolean run = true;

    private transient static final Logger LOG = LoggerFactory.getLogger(DaemonScheduler.class);

    public DaemonScheduler() {
        Main.instance.queueMaintainer = this;
    }

    private void checkUpdateDelay() {
        if (Configuration.instance.services.isEmpty()) {
            return;
        }

        final Duration suggestedQueueMaintainerDelay = Duration.standardSeconds(Configuration.instance.services.size() / 10);

        if (!suggestedQueueMaintainerDelay.isShorterThan(GlobalConstants.DEF_TIMER_QUEUE_MAINTAINER_DELAY) && Configuration.instance.queueMaintainerDelay.isLongerThan(suggestedQueueMaintainerDelay)) {
            DaemonScheduler.LOG.warn("The queue maintainer delay is quite long for the amount of services that are configured. Suggested value is:" + suggestedQueueMaintainerDelay + ", the actual value is: " + Configuration.instance.queueMaintainerDelay);
        }
    }

    private void executeQueuedServices() {
        StructureService service;

        while ((service = this.poll()) != null) {
            this.setStatus("executing service check: " + service.getIdentifier());
            final RobustProcessExecutor rpe = new RobustProcessExecutor(service);
            rpe.execAsync();
        }
    }

    private StructureService poll() {
        if (this.queue.isEmpty()) {
            return null;
        } else {
            final StructureService s = this.queue.get(this.queue.size() - 1);
            this.queue.remove(this.queue.size() - 1);

            Collections.shuffle(this.queue); // Stops bad services holding up
                                             // other services.

            return s;
        }
    }

    private void queueServices() {
        synchronized (Configuration.instance.services) {
            for (final StructureService service : Configuration.instance.services) {
                this.setStatus("Service being checked for queue: " + service.getIdentifier());
                Util.lazySleep(Configuration.instance.queueMaintainerDelay);

                if (service.isReadyToBeChecked()) {
                    if (this.queue.contains(service)) {
                        DaemonScheduler.LOG.warn("service check required but it's already in the queue. Executor queue too long?: " + this.queue.size() + " items.");
                    } else {
                        this.queue.add(service);
                    }
                }
            }
        }
    }

    public void queueUrgent(final StructureService ss) throws IllegalStateException {
        if (!Configuration.instance.services.contains(ss)) {
            throw new IllegalStateException("Tried to urgently queue a service that is not registered globally. Ignoring.");
        }

        this.queue.add(0, ss);
    }

    @Override
    public void run() {
        this.checkUpdateDelay();

        // Now go to normal queueing
        while (this.run) {
            this.setStatus("Sleeping before next execution: " + Configuration.instance.queueMaintainerDelay);
            Util.lazySleep(Configuration.instance.queueMaintainerDelay);

            this.setStatus("Queueing services");
            this.queueServices();

            this.setStatus("executing queued services");
            this.executeQueuedServices();

            this.setStatus("updating db and peers");
            Database.updateAll();
            StructurePeer.updateAll();
        }

        DaemonScheduler.LOG.warn("Queue maintenance thread shutdown.");
    }

    @Override
    public void stop() {
        this.run = false;
    }

    @Override
    public String toString() {
        return "ServiceCheckQueue: " + this.queue.size() + " services";
    }
}
