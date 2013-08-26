<?php

class Installer {
        private $testResults = array();

        public function runTests() {
                $this->testResults = array();
                $this->testResults['phpVersion'] = $this->testPhpVersion();
                $this->testResults['pdoAvailable'] = class_exists('pdo');
                $this->testResults['mysqliAvailable'] = class_exists('mysqli');
		$this->testResults['configFileCreated'] = !file_exists('includes/config.php');
        }

        private function testPhpVersion() {
                return true;
        }

        public function getTestResults() {
                return $this->testResults;
        }

        public function hasPassedAllTests() {
                foreach ($this->testResults as $test) {
                        if ($test == false) {
                                return false;
                        }
                }

                return true;
        }

}

?>

