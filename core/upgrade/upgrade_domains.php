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
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//check the permission
	if(defined('STDIN')) {
		//includes files
		require_once dirname(__DIR__, 2) . "/resources/require.php";
		$_SERVER["DOCUMENT_ROOT"] = $document_root;
		$display_type = 'text'; //html, text
	}
	else if (!$included) {
		//includes files
		require_once dirname(__DIR__, 2) . "/resources/require.php";
		require_once "resources/check_auth.php";
		if (permission_exists('upgrade_apps') || if_group("superadmin")) {
			//echo "access granted";
		}
		else {
			echo "access denied";
			exit;
		}
		$display_type = 'html'; //html, text
	}

//run all app_defaults.php files
	$domain = new domains;
	$domain->display_type = $display_type;
	$domain->upgrade();

?>
