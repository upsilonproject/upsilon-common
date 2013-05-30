<?php

class Installer {
        private $testResults = array();

        public function runTests() {
                $this->testResults = array();
                $this->testResults['phpVersion'] = $this->testPhpVersion();
                $this->testResults['pdoAvailable'] = class_exists('pdo');
                $this->testResults['mysqliAvailable'] = class_exists('mysqli');
        }

        private function testPhpVersion() {
                return true;
        }

        public function getTestResults() {
                return $this->testResults;
        }
}

?>

