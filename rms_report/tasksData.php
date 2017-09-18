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
		
		$this->data['Overdue Tasks'] = $this->getTasks("AND DATE(`date_due`) < '{$today}'");
		$this->data['Today Tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$today}'");
		$this->data['Tomorrow Tasks'] = $this->getTasks("AND DATE(`date_due`) = '{$tomorrow}'");
		$this->data['Next Tasks'] = $this->getTasks("AND DATE(`date_due`) > '{$tomorrow}'");
		$this->data['Created Tasks'] = json_encode($this->getTasks("AND DATE(`date_entered`) = '{$today}'", 'NOT', 0, 0, '`created_by`'));
		$this->data['Quick Tasks'] = json_encode($this->getTasks("", 'NOT', 0, 1));

		$this->data['Sum'] = $this->data['Overdue Tasks']['all'] + $this->data['Today Tasks']['all'] + $this->data['Tomorrow Tasks']['all'] + $this->data['Next Tasks']['all'];

		$this->data['Overdue Tasks'] = json_encode($this->data['Overdue Tasks']);
		$this->data['Today Tasks'] = json_encode($this->data['Today Tasks']);
		$this->data['Tomorrow Tasks'] = json_encode($this->data['Tomorrow Tasks']);
		$this->data['Next Tasks'] = json_encode($this->data['Next Tasks']);

		$this->data['Closed Tasks'] = json_encode($this->getTasks("AND DATE(`date_modified`) = '{$today}'", ''));
		$this->data['Deleted Tasks'] = json_encode($this->getTasks("AND DATE(`date_modified`) = '{$today}'", 'NOT', 1));
	}

	public function addToDatabase($user_name)
	{
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
			'{$this->data['sum']}'
			)"
		);
	}

	public function test()
	{
		$data = array();
		$rms_report = $this->db->query("SELECT * FROM `rms_report_tasks`");
		while($row = $this->db->fetchByAssoc($rms_report)) {
			foreach($row as $key => $json) {
				$json = mb_convert_encoding($json, "UTF-8");
				$json = str_replace('&quot;', '"', $json);

				$data[$key] = json_decode($json, true);
			}
		}
	}

	public function getTasks($where, $status='NOT', $deleted=0, $new_one=0, $user_activity='`assigned_user_id`')
	{
		$sum = array(
			"all" => 0
		);
		$sql_tasks = "SELECT COUNT(`id`) AS `count`, `parent_id`, `parent_type`
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
		$sql_tasks .= "GROUP BY `parent_id`, `parent_type`";

		$sql_periodic_tasks = "SELECT DISTINCT `parent_id`, `parent_type`
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
			if($row['parent_type'] == "Project") {
				$parent_type_result = $this->db->query("SELECT `project_number_c` FROM `project_cstm` WHERE `id_c`='{$row['parent_id']}'");
				$parent_type_row = $this->db->fetchByAssoc($parent_type_result);

				$sum[$parent_type_row['project_number_c']] = $row['count'];
			} else {
				$sum[$row['parent_type']] = $row['count'];
			}
			
			$sum['all'] += $row['count'];
		}

		$result = $this->db->query($sql_periodic_tasks);
		while($row = $this->db->fetchByAssoc($result)) {
			if($row['parent_type'] == "Project") {
				$parent_type_result = $this->db->query("SELECT `project_number_c` FROM `project_cstm` WHERE `id_c`='{$row['parent_id']}'");
				$parent_type_row = $this->db->fetchByAssoc($parent_type_result);

				$sum[$parent_type_row['project_number_c']]++;
			} else {
				$sum[$row['parent_type']]++;
			}
			
			$sum['all']++;
		}

		return $sum;
	}
}