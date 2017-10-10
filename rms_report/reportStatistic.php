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

		dump($this->week_data); die();
	}

	public function syncUserStatistic()
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
					case "Notifications":
						foreach($module_data as $key => $value) {
							$message = "up";
							$distance = $value - ($this->week_data[$module_name][$key]);
							$it_contains = ($value == 0) ? 0 : $this->week_data[$module_name][$key] / $value;

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
							$it_contains = ($value == 0) ? 0 : $this->week_data[$module_name][$key] / $value;

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

							$regresion += ($weight * $distance);
							$output[$module_name][$key] = ($this->week_data[$module_name][$key] + $value) / 2;
						}
					break;
				}
			}

			echo $this->user_id ." regresion: ". $regresion ."<br/>";
			dump($this->result);

			$output = json_encode($output);
			$week_number = $user_statistic_row['week_number'] + 1;

			$this->db->query("UPDATE `rms_statistics` SET `user_data`='{$output}', `date_of_last_update`=CURRENT_TIMESTAMP, `week_number`='{$week_number}' WHERE `user_id`='{$this->user_id}'");
		}
	}

	public function getUsersTasksStatistic()
	{
		$statistic_result = $this->db->query("SELECT * FROM `rms_report_tasks`");

		while($user_statistic = $this->db->fetchByAssoc($statistic_result)) {
			$this->average_data[$user_statistic['user_id']]['tasks'] = $user_statistic;
			$this->average_data[$user_statistic['user_id']]['iteration'] = $user_statistic['iteration'];
			unset($this->average_data[$user_statistic['user_id']]['tasks']['iteration']);
			unset($this->average_data[$user_statistic['user_id']]['tasks']['user_id']);
			unset($this->average_data[$user_statistic['user_id']]['tasks']['date_modified']);
		}
	}

	public function syncUsersTasksStatistic()
	{
		foreach($this->users as $dep_id => $user) {
			foreach($user as $user_id => $user_data) {
				$task_type = $user_data['Tasks']['type'];

				if(!empty($this->average_data[$user_id])) {
					$iter = ($this->average_data[$user_id]['iteration'] + 1);

					foreach($this->average_data[$user_id]['tasks'] as $field => $average) {
						$message = ($field == "overdue_tasks" || $field == "today_tasks") ? "down" : "up";
						
						if($task_type[$field] - $average < 0) {
							$message = ($field == "overdue_tasks" || $field == "today_tasks") ? "up" : "down";
						}

						$value = ($average == 0) ? 0 : $task_type[$field] / $average ;
						$this->statistic['users'][$user_id][$field] = array(
							"message" => $message,
							"value" => $value
						);

						$today_value = (floatval($task_type[$field]) + $average) / 2;
						$this->db->query("UPDATE `rms_report_tasks` SET `$field`='{$today_value}' WHERE `user_id`='{$user_id}'");
					}

					$this->db->query("UPDATE `rms_report_tasks` SET `date_modified`=CURRENT_TIMESTAMP, `iteration`='{$iter}' WHERE `user_id`='{$user_id}'");
				} else {
					$this->db->query("INSERT INTO `rms_report_tasks` VALUES('{$user_id}', CURRENT_TIMESTAMP, '{$task_type['overdue_tasks']}', '{$task_type['today_tasks']}', '{$task_type['tomorrow_tasks']}', '{$task_type['next_tasks']}', '{$task_type['created_tasks']}', '{$task_type['quick_tasks']}', '{$task_type['closed']}', '{$task_type['sum']}', 1)");
				}
			}
		}
	}

	public function getTaskIndicator($key)
	{
		$destimulant = array("overdue_tasks", "today_tasks");

		if(!empty($this->statistic[$key])) {
			foreach($this->statistic[$key] as $user_id => $data) {
				$indicator = 0;

				foreach($data as $field => $value) {
					if($value['message'] == "down") {
						$indicator -= $value['value'];
					} else {
						$indicator += $value['value'];
					}
				}

				echo $user_id .": ". ($indicator/8) ."<br/>";
				// if(in_array($, $destimulant)) {

				// }
			}
		}
	}

	public function process()
	{
		$this->getUsersTasksStatistic();
		$this->syncUsersTasksStatistic();

		$this->getTaskIndicator('users');
	}

	public function setRoleModules($role_modules)
	{
		$this->role_modules = $role_modules;
	}
}