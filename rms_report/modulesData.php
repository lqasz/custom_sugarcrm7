<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ModulesData 
{
	private $db;

	public function __construct() 
	{
		$this->db = DBManagerFactory::getInstance();
	}

	public function getUserActionsByModule($user, $module)
	{
		if($module == "ac_invoices") {
			$fields = array(
				"accept1_c" => "QS",
				"accept2_c" => "PM",
				"accept3_c" => "SV",
				"accept4_c" => "FA",
				"reject_c" => "Reject",
				"na_all_c" => "NA all",
				"package_no_c" => "Package No",
				"proform_paid_c" => "Proform Paid",
			);

			$sum = array(
				"Modified" => array(
					"all" => 0,
				),
				"Created" => 0,
			);

			$invoice_updated_result = $this->db->query("SELECT COUNT(`id`) AS `count`, `field_name` FROM `ac_invoices_audit` WHERE `created_by`='{$user}' AND DATE(`date_created`) = CURRENT_DATE GROUP BY `field_name`");

			while($row = $this->db->fetchByAssoc($invoice_updated_result)) {
				if(!empty($fields[$row['field_name']])) {
					$sum["Modified"][$row['field_name']] += $row['count'];
					$sum["Modified"]['all'] += $row['count'];
				}
			}

			$invoice_created_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `ac_invoices` WHERE `created_by`='{$user}' AND DATE(`date_entered`) = CURRENT_DATE");
			$row = $this->db->fetchByAssoc($invoice_created_result);
			$sum["Created"] += $row['count'];
		} else {
			$sum = array(
				"Created" => 0,
				"Modified" => 0,
			);

			$module_created_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `$module` WHERE DATE(`date_entered`) = CURRENT_DATE AND `created_by`='{$user}'");
			$row = $this->db->fetchByAssoc($module_created_result);
			$sum['Created'] += $row['count'];

			$module_modified_result = $this->db->query("SELECT COUNT(`id`) AS `count` FROM `{$module}_audit` WHERE DATE(`date_created`) = CURRENT_DATE AND `created_by`='{$user}'");
			$row = $this->db->fetchByAssoc($module_modified_result);
			$sum['Modified'] += $row['count'];
		}

		return $sum;
	}
}