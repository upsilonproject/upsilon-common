<?php

define('INSTALLATION_IN_PROGRESS', true);
$title = 'Installer';

require_once 'includes/widgets/header.php';
require_once 'includes/classes/Installer.php';
require_once 'includes/classes/FormInstallationQuestions.php';

$installer = new Installer();
$installer->runTests();

$tpl->assign('installationTests', $installer->getTestResults());

$form = new FormInstallationQuestions();
if ($form->validate()) {
	$form->process();

	$tpl->assign('configFile', $form->generateConfigFile());
}

$tpl->assignForm($form);
$tpl->display('installer.tpl');

require_once 'includes/widgets/footer.php';

?>
