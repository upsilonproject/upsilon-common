<?php
/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

require_once 'libAllure/Database.php';

abstract class Task {
	public abstract function execute();
	public $lastExecuted;

	public function getName() {
		return get_class($this);
	}
}

class Scheduler {
	private $db; 
	private $startTime;

	public function __construct(Database $db) {
		$this->db = $db;
		$this->startTime = time();
	}

	public function executeOverdueJobs() {
		$jobs = $this->getOverdueJobs();
		$jobs = $this->getClassesForJobs($jobs); 
		
		$this->execute($jobs);
	}

	public function executeEverything() {
		$jobs = $this->getJobs();
		$jobs = $this->getClassesForJobs($jobs);
		
		$this->execute($jobs);
	}

	private function execute($jobs) {
		foreach ($jobs as $job) {
			$job->execute();
			$this->updateRuntime($job); 
		}
	}


	private function updateRuntime(Task $job) {
		$sql = 'UPDATE scheduler_tasks SET lastRunTime = now() WHERE className = :className LIMIT 1';
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':className', get_class($job));
		$stmt->execute();
	}

	private function getClassesForJobs(array $jobs) {
		$ret = array();

		foreach ($jobs as $job) {
			if (class_exists($job['className'])) {
				$instance = new $job['className']();

				if ($instance instanceof Task) {
					$instance->lastExecuted = $job['lastRunTime'];
					$ret[] = $instance;
				}
			}
		}

		return $ret;
	}

	public function getJobs() {
		$sql = 'SELECT className, frequency, lastRunTime FROM scheduler_tasks';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getOverdueJobs() {
		$overdueJobs = array();
		$delta = 100;

		foreach ($this->getJobs() as $job) {
			$overdue = false;
			$secondsSinceLastRun = time() - strtotime($job['lastRunTime']);
			

			switch ($job['frequency']) {
			case 'hourly':
				$overdue = $secondsSinceLastRun > (3600 - $delta); break;
			case 'daily':
				$overdue = $secondsSinceLastRun > (86400 - $delta); break; 
			case 'always':
				$overdue = true; break;
			}

			if ($overdue) {
				$overdueJobs[] = $job; 
			}
		}

		return $overdueJobs;
	}
}

?>
