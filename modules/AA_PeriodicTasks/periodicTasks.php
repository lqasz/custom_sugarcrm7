<?php
$db = DBManagerFactory::getInstance();

/* if update is set then update or add new records to the db
 * else get records from db to the model
 */
if(isset($_GET['update']) && $_GET['update'] == 1) {
	$period_tasks_array = array();
	$periodic_tasks = json_decode(json_encode($_POST['JSONperiodicTasks']), true);

	$holidays = array();
	$holidays_year = date("Y");
	// get all holidays from this year to 3 years latter
	for($i = 0; $i <= 2; $i++) {
		$page = file_get_contents('http://www.kalendarzswiat.pl/swieta/wolne_od_pracy/'.$holidays_year);
	    $matches = array();

	    preg_match_all('#data-date *= *["\']?([^"\']*)#is', $page, $matches);

	    foreach($matches[1] as $key => $value) {
	    	$holidays[date('Y-m-d', strtotime($value))] = 1;
	    }
	    $holidays_year++;
	}

	// loop on array which contains all periodic tasks
	foreach($periodic_tasks as $position => $position_data) {
		foreach($position_data['tasks'] as $task_id => $task_data) {
			// delete or update tasks
			if($task_data['deleted'] == 1) {
				$db->query("UPDATE `aa_tenants` SET `deleted`=1 WHERE `id`='{$task_id}'");
				$period_tasks_array['toDelete'][] = $task_id;
			} elseif($task_data['update'] == 1) {
				$periodic_count = $db->query("SELECT `id` FROM `aa_tenants` WHERE `id` = '{$task_id}'");

				// structure for each single task
				$period_tasks_array['toUpdate'][$task_id] = $task_data;
				$period_tasks_array['toUpdate'][$task_id]['resp'] = $position;

				$departments = implode("|", $task_data['departments']); // all departments all together

				/* 
				 * if exists then update all information in scheme table
				 * else insert into sheme table
				 */ 
				if($db->getRowCount($periodic_count) > 0) {
					$db->query("UPDATE `aa_tenants` SET `name`='{$task_data['name']}' WHERE `id`='{$task_id}'");

					$db->query("UPDATE `aa_tenants_cstm` SET `day_of_week_c`='{$task_data['dayOfWeek']}', `day_of_month_c`='{$task_data['dayOfMonth']}', `month_quarter_c`='{$task_data['month']}', `responsible_c`='{$position}', `departments_c`='{$departments}' WHERE `id_c`='{$task_id}'");
				} else {
					$db->query("INSERT INTO `aa_tenants`(`id`, `name`, `deleted`) VALUES('{$task_id}', '{$task_data['name']}', 0)");

					$db->query("INSERT INTO `aa_tenants_cstm`(`id_c`, `day_of_week_c`, `day_of_month_c`, `month_quarter_c`, `responsible_c`, `departments_c`) VALUES('{$task_id}', '{$task_data['dayOfWeek']}', '{$task_data['dayOfMonth']}', '{$task_data['month']}', '{$position}', '{$departments}')");
				}
			}
		} // for
	} // for

	// simply delete tasks
	if(!empty($period_tasks_array['toDelete'])) {
		foreach($period_tasks_array['toDelete'] as $task_id) {
			deleteTasks($task_id);
		}
	}

	// add new tasks by data stored in structure
	if(!empty($period_tasks_array['toUpdate'])) {
		foreach($period_tasks_array['toUpdate'] as $task_id => $task_data) {
			addNewTask($task_id, $task_data);
		}
	}

	echo json_encode(true);
} elseif(isset($_GET['getTasks']) && $_GET['getTasks'] == 1) {

	/*
	 * Structure for periodic tasks model
	 */
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

	// fetch tasks all from db
	$periodic_tasks_result = $db->query("SELECT * FROM `aa_tenants` 
											INNER JOIN `aa_tenants_cstm`
												ON(`id`=`id_c`)
											WHERE `deleted`=0");

	while($task = $db->fetchByAssoc($periodic_tasks_result)) {
		$departments = explode("|", $task['departments_c']);

		// add tasks to the model
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

/**
 * Fuction runs verification of time period and after this,
 * create single task to related users and departments
 * @param periodic_task_id shema task id
 * @param array with task data
 */
function addNewTask($periodic_task_id, $task_data)
{
	$verification_array = array();
	$db = DBManagerFactory::getInstance();

	// all months in quarters
	$quarter[1] = array(1, 2, 3);
	$quarter[2] = array(4, 5, 6);
	$quarter[3] = array(7, 8, 9);
	$quarter[4] = array(10, 11, 12);

	$current_date = date('Y-m-d');
	$next_three_years = date('Y-m-d', strtotime('+3 years', strtotime($current_date)));
	$days_diff = floor(strtotime($next_three_years) - strtotime($current_date)) / (60*60*24); // 3 years latter - current date in days representation
	
	// first verification
	$period_array = getPeriodData(trim($task_data['dayOfWeek']), trim($task_data['dayOfMonth']), trim($task_data['month']));

	// delete task
	deleteTasks($periodic_task_id);

	$next_day = $current_date;

	// loop which starts today and ends 3 years latter
	for($i = 0; $i < $days_diff; $i++) {
		$next_day = date('Y-m-d', strtotime($next_day .' +1 days'));
		$day_week = date('N', strtotime($next_day));
		$day_month = date('j', strtotime($next_day));
		$month = date('n', strtotime($next_day));
		$year = date('Y', strtotime($next_day));

		/*
		 * Long verification by day of the week, day of the month and month/quarter
		 */
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

		// if it`s true then add task
		if(isset($verification_array['day_week']) && 
			isset($verification_array['day_month']) && 
			isset($verification_array['month'])
		) {
			// set to the correct date
			if($day_week == 6) {
				$next_day = date('Y-m-d', strtotime($next_day .' +2 days'));
			} elseif($day_week == 7) {
				$next_day = date('Y-m-d', strtotime($next_day .' +1 days'));
			}

			if(isset($holidays[$next_day])) {
				$next_day = date('Y-m-d', strtotime($next_day .' +1 days'));
			}

			// simply create tasks
			createTask($periodic_task_id, $task_data, $next_day);
		}

		// unset variables
		unset($verification_array['day_week']);
		unset($verification_array['day_month']);
		unset($verification_array['month']);
	}
}

/**
 * Fuction set delete flag to true on specified records
 * @param periodic_task_id shema task id
 */
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

/**
 * Fuction do verification of time period
 * @param day_of_week
 * @param day_of_month
 * @param month
 * @return array with stages for all periods
 */
function getPeriodData($day_of_week, $day_of_month, $month)
{	
	$stage['day_of_week'] = getSimpleStages($day_of_week);
	$stage['day_of_month'] = getSimpleStages($day_of_month);
	$stage['month'] = getSimpleStages($month);

	if($day_of_month == "end" || $day_of_month == "begin") {
		$stage['day_of_month'] = array();
		$stage['day_of_month']['text'] = $day_of_month;
	}

	if(strstr($month, 'q')) {
		if(strpos($month, "&")) {
			$stage['month'] = array();
			preg_match_all('!\d+!', $month, $stage['month']['quarter_and']);
		} else {
			$stage['month'] = array();
			preg_match_all('!\d+!', $month, $stage['month']['quarter']);
		}
	}	

	return $stage;
}

/**
 * Fuction do simple verification of all periods
 * @param period - could be any
 * @return array for specified period
 */
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

/*
 * Simple function to create a task and make relationships with sheme table
 */
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
			$task_bean->modified_user_id = "144c39bf-ccc3-65ec-2023-5407f7975b91";
			$task_bean->created_by = "144c39bf-ccc3-65ec-2023-5407f7975b91";
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
			$task_bean->generated_c = 1;
			$task_bean->save();

			// make relationship
			$db->query("INSERT INTO `periodictasks_tasks` VALUES('".$periodic_task_id."', '".$task_bean->id."')");
		} // while
	} // foreach
}
?>