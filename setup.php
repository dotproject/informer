<?php /* informer $Id: setup.php 2007/04/11 14:07 weboholic */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Informer';
$config['mod_version'] = '0.1';
$config['mod_directory'] = 'informer';
$config['mod_setup_class'] = 'CInformer';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Informer';
$config['mod_ui_icon'] = 'iCandy_Regional_Settings.png';
$config['mod_description'] = 'Informer allows easy access to a weekly/montly timecard based on existing task logs.';
$config['mod_config'] = true;

class CInformer {
	function install() {
		return true;
	}

	function remove() {
		return true;
	}

	function upgrade() {
		return true;
	}
}

?>