package upsilon;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import upsilon.dataStructures.StructurePeer;
import upsilon.dataStructures.StructureService;
import upsilon.util.GlobalConstants;
import upsilon.util.MutableFlexiTimer;
import upsilon.util.Util;

public class DaemonExecuter extends Daemon {
	private final DaemonQueueMaintainer queue;
	private final MutableFlexiTimer ft = new MutableFlexiTimer(GlobalConstants.MIN_EXECUTOR_SLEEP, GlobalConstants.MAX_EXECUTOR_SLEEP, GlobalConstants.INC_EXECUTOR_SLEEP, "between service execution");
	private static transient final Logger LOG = LoggerFactory.getLogger(DaemonExecuter.class);

	private boolean run = true;

	public DaemonExecuter() {
		this.queue = Main.instance.queueMaintainer;
	}

	private void executeQueuedServices() {
		this.ft.submitResult(this.queue.isEmpty());

		StructureService service;

		while ((service = this.queue.poll()) != null) {
			this.setStatus("executing service check: " + service.getIdentifier());
			RobustProcessExecutor rpe = new RobustProcessExecutor(service);
			rpe.execAsync();
		}
	}

	@Override
	public void run() {
		this.setStatus("starting");
		LOG.info("Executor daemon started, execution delay: " + Configuration.instance.executorDelay);

		while (this.run) {
			this.setStatus("sleeping " + Configuration.instance.executorDelay + " before next execution");
			Util.lazySleep(Configuration.instance.executorDelay);

			try {
				this.setStatus("executing queued services");
				this.executeQueuedServices();

				this.setStatus("updating db and peers");
				Database.updateAll();
				StructurePeer.updateAll();
			} catch (Exception e) {
				LOG.error("Stray exception in DaemonExecuter, calling main shutdown. Exception was: " + e);
				e.printStackTrace();

				Main.instance.shutdown();
			}
		}

		RobustProcessExecutor.threadPool.shutdown();
		DaemonExecuter.LOG.warn("Executer thread shutdown.");
	}

	@Override
	public void stop() {
		this.run = false;
		this.setStatus("stopped");
	}
}
