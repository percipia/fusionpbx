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
	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
	Salvatore Caruso <salvatore.caruso@nems.it>
	Riccardo Granchi <riccardo.granchi@nems.it>
	Errol Samuels <voiptology@gmail.com>
	Andrew Querol <andrew@querol.me>
*/

//define the follow me class
	class follow_me extends feature_base {
		public function disable(array $uuids) {
			if (!permission_exists('follow_me')) {
				return;
			}
			$this->set($uuids, false);
		}

		public function enable(array $uuids) {
			if (!permission_exists('follow_me')) {
				return;
			}
			$this->set($uuids, true);
		}

		/**
		 * toggle records
		 * @param array $uuids The uuids to toggle
		 */
		public function toggle(array $uuids) {
			if (!permission_exists('follow_me')) {
				return;
			}

			$this->set($uuids, null);
		} //function

		protected function update(array $extension) : array {
			$extension = parent::update($extension);
			//disable other features
			if ($extension['follow_me_enabled'] == feature_base::enabled) {
				$extension['do_not_disturb'] = feature_base::disabled; //false
				$extension['forward_all_enabled'] = feature_base::disabled; //false
			}
			return $extension;
		}

		/**
		 * @param array $uuids The extension UUIDs to perform this operation on
		 * @param ?bool $new_state The new state or null to toggle
		 */
		private function set(array $uuids, ?bool $new_state) {
			$extensions = $this->getExistingState($uuids);

		/**
		 * toggle records
		 */
		public function toggle($records) {

			//assign private variables
				$this->app_name = 'calls';
				$this->app_uuid = '19806921-e8ed-dcff-b325-dd3e5da4959d';
				$this->permission = 'follow_me';
				$this->list_page = 'calls.php';
				$this->table = 'extensions';
				$this->uuid_prefix = 'extension_';
				$this->toggle_field = 'follow_me_enabled';
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

						//build update array
							$x = 0;
							foreach ($extensions as $uuid => $extension) {

								//count destinations
									$destinations_exist = false;
									if (
										$extension['state']	== $this->toggle_values[1] //false becoming true
										&& is_uuid($extension['follow_me_uuid'])
										) {
										$sql = "select count(*) from v_follow_me_destinations where follow_me_uuid = :follow_me_uuid";
										$parameters['follow_me_uuid'] = $extension['follow_me_uuid'];
										$database = new database;
										$num_rows = $database->select($sql, $parameters, 'column');
										$destinations_exist = $num_rows ? true : false;
										unset($sql, $parameters, $num_rows);
									}

								//determine new state
									$new_state = $extension['state'] == $this->toggle_values[1] && $destinations_exist ? $this->toggle_values[0] : $this->toggle_values[1];

								//toggle feature
									if ($new_state != $extension['state']) {
										$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $uuid;
										$array[$this->table][$x][$this->toggle_field] = $new_state;
										if (is_uuid($extension['follow_me_uuid'])) {
											$array['follow_me'][$x]['follow_me_uuid'] = $extension['follow_me_uuid'];
											$array['follow_me'][$x]['follow_me_enabled'] = $new_state;
										}
									}

								//disable other features
									if ($new_state == $this->toggle_values[0]) { //true
										$array[$this->table][$x]['forward_all_enabled'] = $this->toggle_values[1]; //false
										$array[$this->table][$x]['do_not_disturb'] = $this->toggle_values[1]; //false
									}

								//increment counter
									$x++;

							}

						//save the changes
							if (is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('extension_edit', 'temp');
									$p->add('follow_me_edit', 'temp');

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('extension_edit', 'temp');
									$p->delete('follow_me_edit', 'temp');

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

								//set message
									message::add($text['message-toggle']);

							}
							unset($records, $extensions, $extension);
					}

			}

		} //function

	} //class

?>
