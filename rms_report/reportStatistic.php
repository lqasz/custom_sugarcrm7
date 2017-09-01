<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportStatistic
{
	private $db;
	private $users;
	private $average_data;
	private $statistic_data;

	public function __construct($users) 
	{
		$this->users = $users;
		$this->average_data = array();
		$this->statistic_data = array();
		$this->db = DBManagerFactory::getInstance();
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
}