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

	public function __construct() 
	{
		$this->db = DBManagerFactory::getInstance();
	}

	public function getUserActionsByModule($user, $module)
	{
		$sum = array(
			"created" => 0,
			"modified" => 0,
		);

		$module_created_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `$module` WHERE DATE(`date_entered`) = CURRENT_DATE AND `created_by`='{$user}'");
		$row = $this->db->fetchByAssoc($module_created_result);
		$sum['created'] += $row['count'];

		$module_modified_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `{$module}_audit` WHERE DATE(`date_created`) = CURRENT_DATE AND `created_by`='{$user}'");
		$row = $this->db->fetchByAssoc($module_modified_result);
		$sum['modified'] += $row['count'];

		return $sum;
	}
}