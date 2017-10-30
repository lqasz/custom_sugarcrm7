<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from specified modules tables
*/
class ModulesData 
{
	private $db;
	private $user;

	private $data = array();
	public $decoded_data = array();

	public function __construct($user, $modules) 
	{
		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();

		foreach($modules as $module_in_db => $module_name) {
			// gets info from modules which were assigned to {$user}
			$this->data[$module_name] = $this->getUserActionsByModule($module_in_db);
		}

		$this->decoded_data = $this->data;
		$this->data = json_encode($this->data);
	}

	private function getUserActionsByModule($module)
	{
		$sum = array(
			"Created" => 0,
			"Modified" => 0,
		);

		$query = "SELECT COUNT(`id`) AS `count` 
				FROM `$module` 
				WHERE DATE(`date_entered`) = CURRENT_DATE 
					AND `created_by`='{$this->user}'";

		$module_created_result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($module_created_result);
		$sum['Created'] += $row['count'];

		$query = "SELECT COUNT(`id`) AS `count` 
				FROM `{$module}_audit` 
				WHERE DATE(`date_created`) = CURRENT_DATE 
					AND `created_by`='{$this->user}'";

		$module_modified_result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($module_modified_result);
		$sum['Modified'] += $row['count'];

		return $sum;
	}

	public function addToDatabase($user_name)
	{
		// insert {$user_name} modules activities to databese
		$this->db->query("INSERT INTO `rms_report_modules` VALUES(
			'".create_guid()."', 
			'{$user_name}',
			CURRENT_TIMESTAMP,
			'{$this->data}')"
		);
	}
}