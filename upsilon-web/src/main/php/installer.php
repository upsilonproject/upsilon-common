<?php

define('INSTALLATION_IN_PROGRESS', true);
$title = 'Installer';

require_once 'includes/widgets/header.php';
require_once 'includes/classes/Installer.php';
require_once 'includes/classes/FormInstallationQuestions.php';

if (file_exists('includes/config.php')) {
	        $tpl->error('You config file <strong>includes/config.php</strong> already exists. This means upsilon is probably already installed. Either <a href = "login.php">Login</a> or delete the config file to do a reinstallation.');
}  

$installer = new Installer();
$installer->runTests();

$form = new FormInstallationQuestions();
if ($installer->hasPassedAllTests() && $form->validate()) {
	$form->process();

	$tpl->assign('configFile', $form->generateConfigFile());
}

$tpl->assign('installationTests', $installer->getTestResults());
$tpl->display('installer.tpl');

if ($installer->hasPassedAllTests()) {
	$tpl->assignForm($form);
	$tpl->display('form.tpl');
}


require_once 'includes/widgets/footer.php';

?>
