<?php
$db = DBManagerFactory::getInstance();

if(isset($_GET['update']) && $_GET['update'] == 1) {
	$period_tasks_array = array();
	$periodic_tasks = json_decode(json_encode($_POST['JSONperiodicTasks']), true);

	foreach($periodic_tasks as $position => $position_data) {
		foreach($position_data['tasks'] as $task_id => $task_data) {
			if($task_data['deleted'] == 1) {
				$db->query("UPDATE `aa_tenants` SET `deleted`=1 WHERE `id`='{$task_id}'");
				$period_tasks_array['toDelete'] = $task_id;
			} elseif($task_data['update'] == 1) {
				$periodic_count = $db->query("SELECT `id` FROM `aa_tenants` WHERE `id` = '{$task_id}'");

				if($db->getRowCount($periodic_count) > 0) {
					$departments = implode("|", $task_data['departments']);
					$period_tasks_array['toUpdate'][$task_id] = $task_data;
					$period_tasks_array['toUpdate'][$task_id]['resp'] = $position;
					$db->query("UPDATE `aa_tenants` SET `name`='{$task_data['name']}' WHERE `id`='{$task_id}'");

					$db->query("UPDATE `aa_tenants_cstm` SET `day_of_week_c`='{$task_data['dayOfWeek']}', `day_of_month_c`='{$task_data['dayOfMonth']}', `month_quarter_c`='{$task_data['month']}', `responsible_c`='{$position}', `departments_c`='{$departments}' WHERE `id_c`='{$task_id}'");
				} else {
					$departments = implode("|", $task_data['departments']);
					$period_tasks_array['toUpdate'][$task_id] = $task_data;
					$period_tasks_array['toUpdate'][$task_id]['resp'] = $position;
					$db->query("INSERT INTO `aa_tenants`(`id`, `name`, `deleted`) VALUES('{$task_id}', '{$task_data['name']}', 0)");

					$db->query("INSERT INTO `aa_tenants_cstm`(`id_c`, `day_of_week_c`, `day_of_month_c`, `month_quarter_c`, `responsible_c`, `departments_c`) VALUES('{$task_id}', '{$task_data['dayOfWeek']}', '{$task_data['dayOfMonth']}', '{$task_data['month']}', '{$position}', '{$departments}')");
				}
			}
		}
	}

	if(!empty($period_tasks_array['toUpdate'])) {
		foreach($period_tasks_array['toUpdate'] as $task_id => $task_data) {
			addNewTask($task_id, $task_data);
		}
	}

	echo json_encode(true);
} elseif(isset($_GET['getTasks']) && $_GET['getTasks'] == 1) {
	$periodic_tasks['assistant'] = array(
		"label" => "Assistant",
		"tasks" => array(),
	);

	$periodic_tasks['junior_manager'] = array(
		"label" => "Junior Manager",
		"tasks" => array(),
	);

	$periodic_tasks['manager'] = array(
		"label" => "Manager",
		"tasks" => array(),
	);

	$periodic_tasks['senior_manager'] = array(
		"label" => "Senior Manager",
		"tasks" => array(),
	);

	$periodic_tasks['partner'] = array(
		"label" => "Partner",
		"tasks" => array(),
	);

	$periodic_tasks_result = $db->query("SELECT * FROM `aa_tenants` 
											INNER JOIN `aa_tenants_cstm`
												ON(`id`=`id_c`)
											WHERE `deleted`=0");

	while($task = $db->fetchByAssoc($periodic_tasks_result)) {
		$departments = explode("|", $task['departments_c']);

		$periodic_tasks[$task['responsible_c']]['tasks'][$task['id']] = array(
			'name' => $task['name'],
            'dayOfWeek' => str_replace("amp;", "", $task['day_of_week_c']),
            'dayOfMonth' => str_replace("amp;", "", $task['day_of_month_c']),
            'month' => str_replace("amp;", "", $task['month_quarter_c']),
            'departments' => $departments,
            'update' => 0,
            'deleted' => 0,
            'new' => 0,
		);
	}

	echo json_encode($periodic_tasks);
}

function addNewTask($periodic_task_id, $task_data)
{
	$db = DBManagerFactory::getInstance();
	$quarter[1] = array(1, 2, 3);
	$quarter[2] = array(4, 5, 6);
	$quarter[3] = array(7, 8, 9);
	$quarter[4] = array(10, 11, 12);
	$verification_array = array();
	$current_date = date('Y-m-d');
	$next_three_years = date('Y-m-d', strtotime('+3 years', strtotime($current_date)));
	$days_diff = floor(strtotime($next_three_years) - strtotime($current_date)) / (60*60*24);
	$period_array = getPeriodData(trim($task_data['dayOfWeek']), trim($task_data['dayOfMonth']), trim($task_data['month']));
	deleteTasks($periodic_task_id);

	$next_day = $current_date;
	for($i = 0; $i < $days_diff; $i++) {
		$next_day = date('Y-m-d', strtotime($next_day .' +1 days'));
		$day_week = date('N', strtotime($next_day));
		$day_month = date('j', strtotime($next_day));
		$month = date('n', strtotime($next_day));
		$year = date('Y', strtotime($next_day));

		if(isset($period_array['day_of_week']['every'])) {
			$verification_array['day_week']['add_task'] = 1;
		} elseif(isset($period_array['day_of_week']['value']) && 
			($period_array['day_of_week']['value'] == $day_week)) {
			$verification_array['day_week']['add_task'] = 1;
		} elseif(isset($period_array['day_of_week']['from_to'])) {
			if($period_array['day_of_week']['from_to'][0][0] <= $day_week &&
				$day_week < $period_array['day_of_week']['from_to'][0][1]) 
			{
				$verification_array['day_week']['add_task'] = 1;
			}
		} elseif(isset($period_array['day_of_week']['and'])) {
			if(in_array($day_week, $period_array['day_of_week']['and'][0])) {
				$verification_array['day_week']['add_task']['and'] = 1;
			}
		}

		if(isset($period_array['day_of_month']['every'])) {
			$verification_array['day_month']['add_task'] = 1;
		} elseif(isset($period_array['day_of_month']['value']) &&
			($period_array['day_of_month']['value'] == $day_month)) {
				$verification_array['day_month']['add_task'] = 1;
		} elseif(isset($period_array['day_of_month']['text'])) {
			if($period_array['day_of_month']['text'] == "begin" && $day_month == 1) {
				$verification_array['day_month']['add_task'] = 1;
			} elseif($period_array['day_of_month']['text'] == "end" && $day_month == cal_days_in_month(CAL_GREGORIAN , $month , $year)) {
				$verification_array['day_month']['add_task'] = 1;
			}
		} elseif(isset($period_array['day_of_month']['from_to'])) {
			if($period_array['day_of_month']['from_to'][0][0] <= $day_month &&
				$day_month < $period_array['day_of_month']['from_to'][0][1]) 
			{
				$verification_array['day_month']['add_task'] = 1;
			}
		} elseif(isset($period_array['day_of_month']['and'])) {
			if(in_array($day_month, $period_array['day_of_month']['and'][0])) {
				$verification_array['day_month']['add_task']['and'] = 1;
			}
		}

		if(isset($period_array['month']['every'])) {
			$verification_array['month']['add_task'] = 1;
		} elseif(isset($period_array['month']['value']) &&
			($period_array['month']['value'] == $month)) {
				$verification_array['month']['add_task'] = 1;
		} elseif(isset($period_array['month']['from_to'])) {
			if($period_array['month']['from_to'][0][0] <= $month &&
				$month < $period_array['month']['from_to'][0][1]) 
			{
				$verification_array['month']['add_task'] = 1;
			}
		} elseif(isset($period_array['month']['and'])) {
			if(in_array($month, $period_array['month']['and'][0])) {
				$verification_array['month']['add_task']['and'] = 1;
			}
		} elseif(isset($period_array['month']['quarter'])) {
			$value = $period_array['month']['quarter'][0][0];
			if(in_array($month, $quarter[$value])) {
				$verification_array['month']['add_task']['and'] = 1;	
			}
		} elseif(isset($period_array['month']['quarter_and'])) {
			foreach($period_array['month']['quarter_and'][0] as $key => $value) {
				if(in_array($month, $quarter[$value])) {
					$verification_array['month']['add_task']['and'] = 1;	
				}
			}
		}

		if(isset($verification_array['day_week']) && isset($verification_array['day_month']) && isset($verification_array['month'])) {
			createTask($periodic_task_id, $task_data, $next_day);
		}

		unset($verification_array['day_week']);
		unset($verification_array['day_month']);
		unset($verification_array['month']);
	}
}

function deleteTasks($periodic_task_id)
{
	$db = DBManagerFactory::getInstance();
	$tasks_result = $db->query("SELECT `task_id` FROM `periodictasks_tasks` WHERE `periodic_task_id`='{$periodic_task_id}'");
	
	if($db->getRowCount($tasks_result) > 0) {
		while($task_row = $db->fetchByAssoc($tasks_result)) {
			$db->query("UPDATE `tasks` SET `deleted`=1 WHERE `id`='{$task_row['task_id']}'");
		}
	}
}

function getPeriodData($day_of_week, $day_of_month, $month)
{
	$output = array();
	
	$stage['day_of_week'] = getSimpleStages($day_of_week);
	$stage['day_of_month'] = getSimpleStages($day_of_month);
	$stage['month'] = getSimpleStages($month);

	if($day_of_month == "end" || $day_of_month == "begin") {
		$stage['day_of_month'] = array();
		$stage['day_of_month']['text'] = $day_of_month;
	}

	if(!empty($stage['month'])) {
		if(strstr($month, 'q')) {
			if(strpos($month, "&")) {
				$stage['month'] = array();
				preg_match_all('!\d+!', $month, $stage['month']['quarter_and']);
			} else {
				$stage['month'] = array();
				preg_match_all('!\d+!', $month, $stage['month']['quarter']);
			}
		}
	}	

	return $stage;
}

function getSimpleStages($period)
{
	$stage['every'] = "every";

	if($period != "*") {
		if(is_numeric($period)) {
			$stage = array();
			$stage['value'] = $period;
		} elseif(strpos($period, "-")) {
			$stage = array();
			preg_match_all('!\d+!', $period, $stage["from_to"]);
		} elseif(strpos($period, "&")) {
			$stage = array();
			preg_match_all('!\d+!', $period, $stage["and"]);
		} else {
			$stage = array();
		}
	}

	return $stage;
}

function createTask($periodic_task_id, $task_data, $date)
{
	$db = DBManagerFactory::getInstance();

	foreach($task_data['departments'] as $key => $dep) {
		$position_result = $db->query("SELECT `usr`.`id_c` AS `u_id`, 
												`dep`.`id_c` AS `d_id`
										FROM `users_cstm` AS `usr`
										LEFT JOIN `aa_departments_cstm` AS `dep`
											ON(`aa_departments_id_c` = `dep`.`id_c`)
										LEFT JOIN `users`
											ON(`users`.`id`=`usr`.`id_c`)
										WHERE `position_c`='{$task_data['resp']}'
											AND LOWER(`dep`.`short_c`) LIKE '%".$dep."%'
											AND `users`.`status`='active'");

		while($user = $db->fetchByAssoc($position_result)) {
			$task_bean = BeanFactory::newBean('Tasks');
			$task_bean->new_with_id = true;
			$task_bean->id = create_guid();
			$task_bean->name = $task_data['name'];
			$task_bean->date_due = date('Y-m-d H:i:s', strtotime($date));
			$task_bean->date_start = date('Y-m-d H:i:s', strtotime($date));
			$task_bean->date_entered = date('Y-m-d H:i:s');
			$task_bean->date_modified = date('Y-m-d H:i:s');
			$task_bean->modified_user_id = "1";
			$task_bean->created_by = "1";
			$task_bean->every_day_c = false;
			$task_bean->every_week_c = false;
			$task_bean->every_month_c = false;
			$task_bean->description = "";
			$task_bean->status = "Not Started";
			$task_bean->priority = "Medium";
			$task_bean->new_one_c = false;
			$task_bean->parent_id = $user['d_id'];
			$task_bean->assigned_user_id = $user['u_id'];
			$task_bean->parent_type = "AA_Departments";

			$task_bean->save();

			$db->query("INSERT INTO `periodictasks_tasks` VALUES('".$task_bean->id."', '".$periodic_task_id."')");
		}
	}
}
?>