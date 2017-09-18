<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ActivitiesData 
{
	private $db;
	private $user;

	public $data = array();

	public function __construct($user)
	{
		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();

		$this->data["bugs"] = json_encode($this->getBugInformations());
		$this->data["login"] = json_encode($this->getLoginToRMS());
		$this->data["notifications"] = json_encode($this->getNotifications());
		$this->data["chat"] = json_encode($this->getChatActivities());
	}

	public function getNotifications()
	{
		$sum = array();

		$query = "SELECT COUNT(`id`) AS `ile`, `severity` 
					FROM `notifications` 
					WHERE `assigned_user_id`='{$this->user}' 
						AND `deleted`=0 
						AND `is_read`=0 
					GROUP BY `severity`";

		$result = $this->db->query($query);
		while($row = $this->db->fetchByAssoc($result)) {
			$severity = (empty($row['severity'])) ? "Information" : $row['severity'];
			$sum[$severity] = $row['ile'];
			$sum['all'] += $row['ile'];
		}
		
		return $sum;
	}

	public function getLoginToRMS()
	{
		$sum = array(
			"mobile" => 0,
			"normal" => 0,
		);
		$query = "SELECT COUNT(`id`) AS `count`, `mobile`
					FROM `tracker_mobile` 
					WHERE `id` LIKE '{$this->user}' 
						AND DATE(`date_entered`) = CURRENT_DATE
					GROUP BY `mobile`";

		$result = $this->db->query($query);
		while($row = $this->db->fetchByAssoc($result)) {
			if($row['mobile'] == 1) {
				$sum['mobile'] += $row['count'];
			} else {
				$sum['normal'] += $row['count'];
			}
		}
		
		return $sum;
	}

	public function getBugInformations()
	{
		$query = "SELECT COUNT(`id`) AS `count`
					FROM `bugs`
					WHERE `created_by`='{$this->user}'
						AND DATE(`date_entered`) = CURRENT_DATE";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		return $row['count'];
	}

	public function getChatActivities()
	{	
		$sum = 0;
		$query = "SELECT COUNT(`id`) AS `count`
					FROM `activities`
					WHERE `activity_type`='post'
						AND `created_by`='{$this->user}'
						AND DATE(`date_entered`) = CURRENT_DATE";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		$sum += $row['count'];

		$query = "SELECT COUNT(`id`) AS `count`
					FROM `comments`
					WHERE `created_by`='{$this->user}'
						AND DATE(`date_entered`) = CURRENT_DATE";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		$sum += $row['count'];

		return $sum;
	}

	public function addToDatabase($user_name)
	{
		$this->db->query("INSERT INTO `rms_report_activities` VALUES(
			'".create_guid()."', 
			'{$user_name}',
			CURRENT_TIMESTAMP,
			'{$this->data["bugs"]}',
			'{$this->data["login"]}',
			'{$this->data["notifications"]}',
			'{$this->data["chat"]}')"
		);
	}
}