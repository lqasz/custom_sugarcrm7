<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportStatistic
{
	private $db;
	private $user_id;
	private $user_role;
	private $week_data;
	private $result;
	private $role_modules;

	public function __construct($user_id, $user_role, $tasks_data, $modules_data, $activities_data) 
	{
		$this->db = DBManagerFactory::getInstance();
		$this->user_id = $user_id;
		$this->user_role = $user_role;
		$this->result = array();
		$this->week_data = array_merge($modules_data, $activities_data, array("Tasks" => $tasks_data));
	}

	public function syncUserStatistic($user_name)
	{
		$user_statistics_result = $this->db->query("SELECT `user_data`, `week_number` FROM `rms_statistics` WHERE `user_id`='{$this->user_id}'");

		if($this->db->getRowCount($user_statistics_result) == 0) {
			$this->week_data = json_encode($this->week_data);

			$this->db->query("INSERT INTO `rms_statistics` VALUES('{$this->user_id}', CURRENT_TIMESTAMP, '{$this->week_data}', 1)");
		} else {
			include('custom/rms_report/roleModules.php');

			$output = array();
			$user_statistic_row = $this->db->fetchByAssoc($user_statistics_result);
			$data = mb_convert_encoding($user_statistic_row['user_data'], "UTF-8");
			$data = str_replace('&quot;', '"', $data);
			$statistic_data = json_decode($data, true);

			$regresion = 0;
			foreach($statistic_data as $module_name => $module_data) {
				switch($module_name) {
					case "Chat":
					case "Bugs":
						$message = "up";
						$distance = $this->week_data[$module_name] - $module_data;
						$it_contains = ($module_data == 0) ? 0 : $this->week_data[$module_name] / $module_data;

						if($distance == 0) {
							$message = "average";
						} else if($distance < 0) {
							$message = "down";
						}

						$this->result[$module_name] = array(
							"distance" => $distance,
							"it_contains" => $it_contains,
							"message" => $message
						);

						$regresion += (0.25 * $distance);
						$output[$module_name] = ($this->week_data[$module_name] + $module_data) / 2;
					break;
					case "Login":
					break;
					case "Notifications":
						foreach($module_data as $key => $value) {
							$message = "up";
							$distance = $value - ($this->week_data[$module_name][$key]);
							$it_contains = ($this->week_data[$module_name][$key] == 0) ? 0 : $value / $this->week_data[$module_name][$key];

							if($distance == 0) {
								$message = "average";
							} else if($distance < 0) {
								$message = "down";	
							}

							$regresion += (0.25 * $distance);
							$output[$module_name] = ($this->week_data[$module_name][$key] + $value) / 2;
						}
					break;
					default:
						foreach($module_data as $key => $value) {
							$message = "up";
							$distance = ($key == "Deleted Tasks" || $key == "Overdue Tasks") ? $value - $this->week_data[$module_name][$key] : $this->week_data[$module_name][$key] - $value;
							
							if($key == "Deleted Tasks" || $key == "Overdue Tasks") {
								$it_contains = ($this->week_data[$module_name][$key] == 0) ? 0 : $value / $this->week_data[$module_name][$key];
							} else {
								$it_contains = ($value == 0) ? 0 : $this->week_data[$module_name][$key] / $value;
							}

							if($distance == 0) {
								$message = "average";
							} else if($distance < 0) {
								$message = "down";
							}

							$this->result[$module_name][$key] = array(
								"distance" => $distance,
								"it_contains" => $it_contains,
								"message" => $message
							);

							$weight = 1;
							if($module_name != "Tasks") {
								$weight = $this->role_modules[$module_name]['weight'];
							}

							if(date("w") == 5 && $key == "Tomorrow Tasks") {
								$weight = 0;
							}

							$regresion += ($weight * $distance);
							$output[$module_name][$key] = ($this->week_data[$module_name][$key] + $value) / 2;
						}
					break;
				}
			}

			$this->result['modules'] = $this->result;
			$this->result['regresion'] = $regresion;

			$week_number = $user_statistic_row['week_number'] + 1;

			$json = json_encode($this->result);
			$this->db->query("INSERT INTO `rms_report_regresion` VALUES('". create_guid() ."', '{$user_name}', CURRENT_DATE, '{$json}', '{$week_number}')");

			$output = json_encode($output);
			$this->db->query("UPDATE `rms_statistics` SET `user_data`='{$output}', `date_of_last_update`=CURRENT_TIMESTAMP, `week_number`='{$week_number}' WHERE `user_id`='{$this->user_id}'");
		}
	}

	public function setRoleModules($role_modules)
	{
		$this->role_modules = $role_modules;
	}
}