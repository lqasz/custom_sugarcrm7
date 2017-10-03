<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ModulesData 
{
	private $db;
	private $user;

	private $data = array();

	public function __construct($user, $modules) 
	{
		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();

		foreach($modules as $module_name => $module_data) {
			$this->data[$module_data['label']] = $this->getUserActionsByModule($module_name);
		}

		$this->data = json_encode($this->data);
	}

	public function getUserActionsByModule($module)
	{
		$sum = array(
			"Created" => 0,
			"Modified" => 0,
		);

		$module_created_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `$module` WHERE DATE(`date_entered`) = CURRENT_DATE AND `created_by`='{$this->user}'");
		$row = $this->db->fetchByAssoc($module_created_result);
		$sum['Created'] += $row['count'];

		$module_modified_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `{$module}_audit` WHERE DATE(`date_created`) = CURRENT_DATE AND `created_by`='{$this->user}'");
		$row = $this->db->fetchByAssoc($module_modified_result);
		$sum['Modified'] += $row['count'];

		return $sum;
	}

	public function addToDatabase($user_name)
	{
		$this->db->query("INSERT INTO `rms_report_modules` VALUES(
			'".create_guid()."', 
			'{$user_name}',
			ADDDATE(CURRENT_DATE,-1),
			'{$this->data}')"
		);
	}
}