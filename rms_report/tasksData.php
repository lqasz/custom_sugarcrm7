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

	public $tasks_type = array();

	public function __construct($user) 
	{	
		$today = date("Y-m-d");
		$tomorrow = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));

		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();
		
		$this->tasks_type['overdue_tasks'] = $this->getTasks("AND DATE(`date_due`) < '{$today}'");
		$this->tasks_type['today_tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$today}'");
		$this->tasks_type['tomorrow_tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$tomorrow}'");
		$this->tasks_type['next_tasks'] = $this->getTasks("AND DATE(`date_due`) > '{$tomorrow}'");
		$this->tasks_type['created_tasks'] = $this->getTasks("AND DATE(`date_entered`) = '{$today}'", 'NOT', 0, 0, '`created_by`');
		$this->tasks_type['quick_tasks'] = $this->getTasks("", 'NOT', 0, 1);

		$this->tasks_type['sum'] = $this->tasks_type['Overdue Tasks'] + $this->tasks_type['Today Tasks'] + $this->tasks_type['Tomorrow Tasks'] + $this->tasks_type['Next Tasks'];

		$this->tasks_type['closed_tasks'] = $this->getTasks("AND DATE(`date_modified`) = '{$today}'", '');
		$this->tasks_type['deleted_tasks'] = $this->getTasks("AND DATE(`date_modified`) = '{$today}'", 'NOT', 1);
	}

	public function getTasks($where, $status='NOT', $deleted=0, $new_one=0, $user_activity='`assigned_user_id`')
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
		$row = $this->db->fetchByAssoc($result);
		$sum += $row['count'];

		$result = $this->db->query($sql_periodic_tasks);
		$count = $this->db->getRowCount($result);
		$sum += $count;

		return $sum;
	}

	public function getTasksByParentType($parent_type)
	{
		return $this->getTasks("AND `parent_type` LIKE '{$parent_type}'");
	}
}