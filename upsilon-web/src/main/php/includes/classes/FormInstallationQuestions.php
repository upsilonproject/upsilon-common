<?php

use \libAllure\Form;
use \libAllure\ElementAlphaNumeric;
use \libAllure\ElementEmail;
use \libAllure\ElementPassword;
use \libAllure\Database;

class FormInstallationQuestions extends Form {
        public function __construct() {
                parent::__construct('formInstallation', 'Generate config.php - installation questions');

                $this->addSection('Database');
                $this->addElement(new ElementAlphaNumeric('dbName', 'Database name', 'upsilon'));
                $this->addElement(new ElementAlphaNumeric('dbUser', 'Database username'));
                $this->addElement(new ElementPassword('dbPass', 'Database user password'));
                $this->getElement('dbPass')->setOptional(true);

                $this->addSection('Administrator');
                $this->addElement(new ElementAlphaNumeric('adminUsername', 'First Admin Username', 'administrator'));
                $this->addElement(new ElementPassword('adminPassword1', 'First Admin Password'));
                $this->addElement(new ElementPassword('adminPassword2', 'First Admin Password (confirm)'));

                $this->requireFields('dbName', 'dbUser', 'adminUsername', 'adminPassword1', 'adminPassword2');

                $this->addDefaultButtons();
        }

        public function validateExtended() {
                $this->validateDatabase();
                $this->validateAdministrator();
        }

        private function validateAdministrator() {
                $password1 = $this->getElementValue('adminPassword1');
                $password2 = $this->getElementValue('adminPassword2');

                if ($password1 != $password2) {
                        $this->getElement('adminPassword2')->setValidationError('The passwords do not match.');
                }
        }

        private function validateDatabase() {
                try {
                        $this->validateDatabaseConnection();
                } catch (Exception $e) {
                        $this->getElement('dbName')->setValidationError('Could not connect to database: ' . $e->getMessage());
                        return;
                }

                try {
                        $this->validateDatabaseTables();
                } catch (Exception $e) {
                        $this->getElement('dbName')->setValidationError('Settings table does not exist. Did you import the setup/databases/schema.sql file?');
                        return;
                }

                $this->validateDatabaseInitialData();
        }

        private function validateDatabaseConnection() {
                $dsn = 'mysql:dbname=' . $this->getElementValue('dbName');
                $dbUser = $this->getElementValue('dbUser');
                $dbPass = $this->getElementValue('dbPass');

                $this->db = new Database($dsn, $dbUser, $dbPass);              
        }

        private function validateDatabaseTables() {
                $sql = 'DESC settings';
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
        }

        private function validateDatabaseInitialData() {
                $sql = 'SELECT s.* FROM settings s';
                $stmt = $this->db->prepare($sql);
                $stmt->execute();

                $settings = $stmt->fetchAll();

                if (count($settings) == 0) {
                        $this->getElement('dbName')->setValidationError('There is nothing in the settings table. Did you import the setup/databases/initialData.sql file?');
                }
        }

        public function process() {
                // Assign salt to $this, so others can call generateConfigFile later.
                $this->saltSuffix = uniqid();

                try {
                        $this->createAdministratorAccount();
                        $this->writeConfigFile();
                } catch (Exception $e) {
                        global $tpl;
                        $tpl->assign('configFailReason', $e->getMessage());
                        return;
                }

                redirect('index.php', 'upsilon-web installed.'); // We wrote the config file okay, redirect.
        }

        private function writeConfigFile() {
                $writeCfg = @file_put_contents('includes/config.php', $this->generateConfigFile());

                if ($writeCfg === false) {
                        throw new Exception('Could not write config file, file_put_contents returned false');
                }
        }

        // This method is exceedingly messy. Improvements to the technique are welcome.
        public function generateConfigFile() {
                $ret = '';
                $ret .= "<?php\n";
                $ret .= "date_default_timezone_set('" . date_default_timezone_get() . "');\n";
                $ret .= "ini_set('display_errors', 'on');\n";
                $ret .= "\n";
                $ret .= "define('CFG_DB_DSN', 'mysql:dbname={$this->getElementValue('dbName')}');\n";
                $ret .= "define('CFG_DB_USER', '{$this->getElementValue('dbUser')}');\n";
                $ret .= "define('CFG_DB_PASS', '{$this->getElementValue('dbPass')}');\n";
                $ret .= "\n// The following is configuration for advanced users only.\n";
                $ret .= "define('CFG_PASSWORD_SALT', '" . $this->saltSuffix . "'); // If you change this value, you will break all existing user passwords.  \n";
                $ret .= '?' . '>';

                return $ret;
        }

        private function createAdministratorAccount() {
                $sql = 'TRUNCATE users';
                $this->db->query($sql);

                $sql = 'INSERT INTO users (username, password, `group`) VALUES (:adminUsername, :adminPassword, 1) ';
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':adminUsername', $this->getElementValue('adminUsername'));
                $stmt->bindValue(':adminPassword', sha1($this->getElementValue('adminPassword1') . $this->saltSuffix));
                $stmt->execute();
        }

}
?>
