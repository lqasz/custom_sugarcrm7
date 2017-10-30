<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class TasksData 
{
	private $db;
	private $user;

	public $data = array();

	public function __construct($user) 
	{	
		$today = date("Y-m-d");
		$tomorrow = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));

		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();
		
		// get tasks activites specified by key in array
		$this->data['Overdue Tasks'] = $this->getTasks("AND DATE(`date_due`) < '{$today}'");
		$this->data['Today Tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$today}'");

		$this->data['Tomorrow Tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$tomorrow}'");
		$this->data['Next Tasks'] = $this->getTasks("AND DATE(`date_due`) > '{$tomorrow}'");

		$this->data['Created Tasks'] = $this->getTasks("AND DATE(`date_entered`) = '{$today}'", 'NOT', 0, 0, '`created_by`');
		$this->data['Quick Tasks'] = $this->getTasks("", 'NOT', 0, 1);

		$this->data['Closed Tasks'] = $this->getTasks("AND DATE(`date_modified`) = '{$today}'", '', 0, 0, '`modified_user_id`');
		$this->data['Deleted Tasks'] = $this->getTasks("AND DATE(`date_modified`) = '{$today}'", 'NOT', 1, 0, '`modified_user_id`');

		$this->data['Sum'] = $this->data['Overdue Tasks'] + $this->data['Today Tasks'] + $this->data['Tomorrow Tasks'] + $this->data['Next Tasks'];
	}

	private function getTasks($where, $status='NOT', $deleted=0, $new_one=0, $user_activity='`assigned_user_id`')
	{
		$sum = 0;
		$sql_tasks = "SELECT COUNT(`id`) AS `count`
						FROM `tasks` 
							LEFT JOIN `tasks_cstm` 
								ON(`id`=`id_c`) 
							WHERE $user_activity='{$this->user}' 
								AND `status` $status LIKE '%Completed%'
								AND `deleted`=$deleted
								AND `new_one_c`=$new_one
								AND `every_day_c`=0 
								AND `every_week_c`=0 
								AND `every_month_c`=0 
								AND `generated_c`=0 ";
		$sql_tasks .= $where;

		$sql_periodic_tasks = "SELECT DISTINCT `name`
								FROM `tasks` 
									LEFT JOIN `tasks_cstm` 
										ON(`id` = `id_c`) 
								WHERE $user_activity='{$this->user}' 
									AND `status` $status LIKE '%Completed%' 
									AND `deleted`=$deleted
									AND `new_one_c`=$new_one
									AND (`every_day_c`=1 
										OR `every_week_c`=1 
										OR `every_month_c`=1 
										OR `generated_c`=1) ";
		$sql_periodic_tasks .= $where;

		$result = $this->db->query($sql_tasks);
		while($row = $this->db->fetchByAssoc($result)) {			
			$sum += $row['count'];
		}

		$result = $this->db->query($sql_periodic_tasks);
		while($row = $this->db->fetchByAssoc($result)) {
			$sum++;
		}

		return $sum;
	}

	public function addToDatabase($user_name)
	{
		// insert {$user_name} tasks activities to databese
		$this->db->query("INSERT INTO `rms_report_tasks` VALUES(
			'".create_guid()."', 
			'{$user_name}',
			CURRENT_TIMESTAMP,
			'{$this->data['Overdue Tasks']}',
			'{$this->data['Today Tasks']}',
			'{$this->data['Tomorrow Tasks']}',
			'{$this->data['Next Tasks']}',
			'{$this->data['Created Tasks']}',
			'{$this->data['Quick Tasks']}',
			'{$this->data['Closed Tasks']}',
			'{$this->data['Deleted Tasks']}',
			'{$this->data['Sum']}'
			)"
		);
	}
}