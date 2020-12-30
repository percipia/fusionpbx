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
	Copyright (C) 2010 - 2023
	All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Andrew Querol <andrew@querol.me>
*/

//define the dnd class
	class do_not_disturb {
		public $debug;
		public $domain_uuid;
		public $domain_name;
		public $extension_uuid;
		public $extension;
		public $enabled;

		//update the user_status
		public function user_status() {
			//update the status
				if ($this->enabled == "true") {
					//update the call center status
						$user_status = "Logged Out";
						$fp = event_socket_create();
						if ($fp) {
							$switch_cmd .= "callcenter_config agent set status ".$_SESSION['username']."@".$this->domain_name." '".$user_status."'";
							$switch_result = event_socket_request($fp, 'api '.$switch_cmd);
						}

		public function disable(array $uuids) {
			if (!permission_exists('do_not_disturb')) {
				return;
			}
			$this->set($uuids, false);
		}

		public function enable(array $uuids) {
			if (!permission_exists('do_not_disturb')) {
				return;
			}
			$this->set($uuids, true);
		}

		/**
		 * toggle records
		 * @param array $uuids The uuids to toggle
		 */
		public function toggle($records) {

			//assign private variables
				$this->app_name = 'calls';
				$this->app_uuid = '19806921-e8ed-dcff-b325-dd3e5da4959d';
				$this->permission = 'do_not_disturb';
				$this->list_page = 'calls.php';
				$this->table = 'extensions';
				$this->uuid_prefix = 'extension_';
				$this->toggle_field = 'do_not_disturb';
				$this->toggle_values = ['true','false'];

			if (permission_exists($this->permission)) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//toggle the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get current toggle state
							foreach($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}
							if (is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select ".$this->uuid_prefix."uuid as uuid, extension, number_alias, ";
								$sql .= "call_timeout, do_not_disturb, ";
								$sql .= "forward_all_enabled, forward_all_destination, ";
								$sql .= "forward_busy_enabled, forward_busy_destination, ";
								$sql .= "forward_no_answer_enabled, forward_no_answer_destination, ";
								$sql .= $this->toggle_field." as toggle, follow_me_uuid ";
								$sql .= "from v_".$this->table." ";
								$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
								$sql .= "and ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
								$database = new database;
								$rows = $database->select($sql, $parameters, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									foreach ($rows as $row) {
										$extensions[$row['uuid']]['extension'] = $row['extension'];
										$extensions[$row['uuid']]['number_alias'] = $row['number_alias'];
										$extensions[$row['uuid']]['call_timeout'] = $row['call_timeout'];
										$extensions[$row['uuid']]['do_not_disturb'] = $row['do_not_disturb'];
										$extensions[$row['uuid']]['forward_all_enabled'] = $row['forward_all_enabled'];
										$extensions[$row['uuid']]['forward_all_destination'] = $row['forward_all_destination'];
										$extensions[$row['uuid']]['forward_busy_enabled'] = $row['forward_busy_enabled'];
										$extensions[$row['uuid']]['forward_busy_destination'] = $row['forward_busy_destination'];
										$extensions[$row['uuid']]['forward_no_answer_enabled'] = $row['forward_no_answer_enabled'];
										$extensions[$row['uuid']]['forward_no_answer_destination'] = $row['forward_no_answer_destination'];
										$extensions[$row['uuid']]['state'] = $row['toggle'];
										$extensions[$row['uuid']]['follow_me_uuid'] = $row['follow_me_uuid'];
									}
								}
								unset($sql, $parameters, $rows, $row);
							}

			$this->set($uuids, null);
		} //function

		protected function update(array $extension) : array {
			//disable other features
			if ($extension['do_not_disturb'] == feature_base::enabled) {
				$extension['forward_all_enabled'] = feature_base::disabled; //false
				$extension['follow_me_enabled'] = feature_base::disabled; //false
			}
			// Important to have the parent update last. Otherwise the above information will not be sent for feature key syncing.
			return parent::update($extension);
		}

		/**
		 * @param array $uuids The extension UUIDs to perform this operation on
		 * @param ?bool $new_state The new state or null to toggle
		 */
		private function set(array $uuids, ?bool $new_state) {
			$extensions = $this->get_existing_state($uuids);

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('extension_edit', 'temp');

								//send feature event notify to the phone
									if (!empty($_SESSION['device']['feature_sync']['boolean']) && $_SESSION['device']['feature_sync']['boolean'] == "true") {
										foreach ($extensions as $uuid => $extension) {
											$feature_event_notify = new feature_event_notify;
											$feature_event_notify->domain_name = $_SESSION['domain_name'];
											$feature_event_notify->extension = $extension['extension'];
											$feature_event_notify->do_not_disturb = $extension['do_not_disturb'];
											$feature_event_notify->ring_count = ceil($extension['call_timeout'] / 6);
											$feature_event_notify->forward_all_enabled = $extension['forward_all_enabled'];
											$feature_event_notify->forward_busy_enabled = $extension['forward_busy_enabled'];
											$feature_event_notify->forward_no_answer_enabled = $extension['forward_no_answer_enabled'];
											//workarounds: send 0 as freeswitch doesn't send NOTIFY when destination values are nil
											$feature_event_notify->forward_all_destination = $extension['forward_all_destination'] != '' ? $extension['forward_all_destination'] : '0';
											$feature_event_notify->forward_busy_destination = $extension['forward_busy_destination'] != '' ? $extension['forward_busy_destination'] : '0';
											$feature_event_notify->forward_no_answer_destination = $extension['forward_no_answer_destination'] != '' ? $extension['forward_no_answer_destination'] : '0';
											$feature_event_notify->send_notify();
											unset($feature_event_notify);
										}
									}

								//synchronize configuration
									if (!empty($_SESSION['switch']['extensions']['dir']) && is_readable($_SESSION['switch']['extensions']['dir'])) {
										require_once "app/extensions/resources/classes/extension.php";
										$ext = new extension;
										$ext->xml();
										unset($ext);
									}

								//clear the cache
									$cache = new cache;
									foreach ($extensions as $uuid => $extension) {
										$cache->delete("directory:".$extension['extension']."@".$_SESSION['domain_name']);
										if ($extension['number_alias'] != '') {
											$cache->delete("directory:".$extension['number_alias']."@".$_SESSION['domain_name']);
										}
									}
			// Set the DND state
			$updates = array();
			foreach ($extensions as $uuid => $extension) {
				// Create a copy of $new_state since we do not want to clobber it when toggling.
				$updated_state = $new_state;
				if (is_null($new_state)) {
					$updated_state = $extension['do_not_disturb'] != feature_base::enabled;
				}
				// Update the extension array with the new DND state
				$extension['do_not_disturb'] = $updated_state ? feature_base::enabled : feature_base::disabled;

				// Build the update array and perform any per-extension updates
				$updates['extensions'][] = $this->update($extension);
			}
			$this->save($updates);

			unset($records, $extensions, $extension, $updates);
		}
	} //class

?>