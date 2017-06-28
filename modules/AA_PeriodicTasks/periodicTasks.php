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
					$db->query("UPDATE `aa_tenants` SET `name`='{$task_data['name']}' WHERE `id`='{$task_id}'");

					$db->query("UPDATE `aa_tenants_cstm` SET `day_of_week_c`='{$task_data['dayOfWeek']}', `day_of_month_c`='{$task_data['dayOfMonth']}', `month_quarter_c`='{$task_data['month']}', `responsible_c`='{$position}', `departments_c`='{$departments}' WHERE `id_c`='{$task_id}'");
				} else {
					$departments = implode("|", $task_data['departments']);
					$period_tasks_array['toUpdate'][$task_id] = $task_data;
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
	deleteTasks($periodic_task_id);
	$period_array = getPeriodData(trim($task_data['dayOfWeek']), trim($task_data['dayOfMonth']), trim($task_data['month']));

	$current_year = date('Y-m-d');
	$next_year = date('Y-m-d', strtotime('+3 years', strtotime($current_year)));

	$divided_days = floor(strtotime($next_year) - strtotime($current_year)) / (60*60*24);

	for($i=0; $i < $divided_days; $i++) { 
		echo date('Y-m-d', strtotime('+1 days'));
	}
	// $task_bean = BeanFactory::newBean('Tasks');
	// $task_bean->new_with_id = true;
	// $task_bean->id = create_guid();
	// $task_bean->name = $task_data['name'];

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
		$stage['day_of_month']['value'] = $day_of_month;
	}

	if(empty($stage['month'])) {
		if(strpos($month, "q") == 0) {
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
		} elseif(strpos($period, "&"))  {
			$stage = array();
			preg_match_all('!\d+!', $period, $stage["and"]);
		} else {
			$stage = array();
		}
	}

	return $stage;
}
?>