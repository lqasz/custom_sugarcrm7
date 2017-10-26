<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class InsertActivities
{
	private $indicators = array();

	public function __construct($user_name, $tasksData, $modulesData, $activitiesData)
	{
		$db = DBManagerFactory::getInstance();
		$sum_of_tasks = $tasksData['Tomorrow Tasks'] + $tasksData['Next Tasks'];
		$combined_indicator = ($sum_of_tasks != 0) ? -1 * $activitiesData['Notifications']['Number of all Notifications'] / $sum_of_tasks : -1 * $activitiesData['Notifications']['Number of all Notifications'];

		$modules_indicator = array(
			"Contacts" => 0,
			"Companies" => 0,
			"Other Modules" => 0,
		);

		foreach($modulesData as $module_name => $module_data) {
			if($module_name == "Contacts" || $module_name == "Companies") {
				$modules_indicator[$module_name] += $module_data["Created"] + $module_data["Modified"];
			} else {
				$modules_indicator["Other Modules"] += $module_data["Created"] + $module_data["Modified"];
			}
		}

		$indicators = array(
			'w1' => $sum_of_tasks-$tasksData['Overdue Tasks'],
			'w2' => $tasksData['Created Tasks'] - $tasksData['Deleted Tasks'],
			'w3' => $tasksData['Closed Tasks'],
			'w4' => $tasksData['Quick Tasks'],
			'w5' => $modules_indicator['Contacts'],
			'w6' => $modules_indicator['Companies'],
			'w7' => $modules_indicator['Other Modules'],
			'w8' => $activitiesData['Login']['Login by Mobile']+$activitiesData['Login']['Normal Login'],
			'w9' => $activitiesData['Chat'],
			'w10' => $activitiesData['Bugs'],
			'w11' => $combined_indicator,
		);

		$db->query("INSERT INTO `rms_statistics` VALUES('". create_guid() ."', '{$user_name}', CURRENT_TIMESTAMP, '{$indicators['w1']}', '{$indicators['w2']}', '{$indicators['w3']}', '{$indicators['w4']}', '{$indicators['w5']}', '{$indicators['w6']}', '{$indicators['w7']}', '{$indicators['w8']}', '{$indicators['w9']}', '{$indicators['w10']}', '{$indicators['w11']}')");
	}
}

?>