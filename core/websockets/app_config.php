<?php

	// application details
		$apps[$x]['name'] = 'Websockets';
		$apps[$x]['uuid'] = '1e4044c1-9f3f-4993-a39f-0e580c16af19';
		$apps[$x]['category'] = '';
		$apps[$x]['subcategory'] = '';
		$apps[$x]['version'] = '';
		$apps[$x]['license'] = 'Mozilla Public License 1.1';
		$apps[$x]['url'] = 'http://www.fusionpbx.com';
		$apps[$x]['description']['en-us'] = '';

	// default settings
		$y = 0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = '17c0b4bc-7f76-4cc8-8bf6-2cb7b3696ac0';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'websocket_server';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = 'bind_port';
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'numeric';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = '8081';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'Websocket router listening port.';
