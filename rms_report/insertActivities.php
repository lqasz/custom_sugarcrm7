<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to create and add indicators for active users
*/
class InsertActivities
{
	public function __construct($user_name, $t_data, $m_data, $a_data)
	{
		$db = DBManagerFactory::getInstance();
		$next_tasks = $t_data['Tomorrow Tasks'] + $t_data['Next Tasks'];

		$combined_indicator = ($next_tasks != 0) ? -1 * $a_data['Notifications'] / $next_tasks : -1 * $a_data['Notifications']; // max from this indicator is 0

		$modules_indicator = array(
			"Contacts" => 0,
			"Companies" => 0,
			"Other Modules" => 0,
		);

		foreach($m_data as $module_name => $module_data) {
			if($module_name == "Contacts" || $module_name == "Companies") {
				$modules_indicator[$module_name] += $module_data["Created"] + $module_data["Modified"];
			} else {
				$modules_indicator["Other Modules"] += $module_data["Created"] + $module_data["Modified"];
			}
		}

		$indicators = array(
			'w1' => $next_tasks-$t_data['Overdue Tasks'],
			'w2' => $t_data['Created Tasks'] - $t_data['Deleted Tasks'],
			'w3' => $t_data['Closed Tasks'],
			'w4' => $t_data['Quick Tasks'],
			'w5' => $modules_indicator['Contacts'],
			'w6' => $modules_indicator['Companies'],
			'w7' => $modules_indicator['Other Modules'],
			'w8' => $a_data['Login']['Login by Mobile'] + $a_data['Login']['Normal Login'],
			'w9' => $a_data['Chat'],
			'w10' => $a_data['Bugs'],
			'w11' => $combined_indicator,
		);

		$db->query("INSERT INTO `rms_statistics` VALUES('". create_guid() ."', '{$user_name}', CURRENT_TIMESTAMP, '{$indicators['w1']}', '{$indicators['w2']}', '{$indicators['w3']}', '{$indicators['w4']}', '{$indicators['w5']}', '{$indicators['w6']}', '{$indicators['w7']}', '{$indicators['w8']}', '{$indicators['w9']}', '{$indicators['w10']}', '{$indicators['w11']}')");
	}
}

?>