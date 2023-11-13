<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2021
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//endpoint to build the configuration files for all phones to save to the TFTP directory
//redirects user to their current page when accessed and displays a status message.

//includes
	require_once dirname(__DIRNAME__, 2) . "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists("device_config_build") || if_group("superadmin")) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//set the variables
	$cmd = $_GET['cmd'];
	$domain_uuid = $_SESSION['domain_uuid'];

	//prepare the command
	if ($cmd == "rebuild") {
	//build all phone configs
		if (strlen($_SESSION['provision']['path']['text']) > 0) {
			if (is_dir($_SERVER["DOCUMENT_ROOT"] . PROJECT_PATH . '/app/provision')) {
				$prov = new provision;
				$prov->domain_uuid = $domain_uuid;
				$response = $prov->write(null);
				
				//prepare then show response
				$message = "Manual configuration file rebuild has completed successfully!";
				message::add($text['label-event']." ".$message, 'positive', 3500);
			}
		}
	}

//redirect user to previous page
	header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
