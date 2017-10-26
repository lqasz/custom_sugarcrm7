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
	public $decoded_data = array();

	public function __construct($user)
	{
		$this->user = $user;
		$this->db = DBManagerFactory::getInstance();

		$this->data["Bugs"] = $this->getBugInformations();
		$this->data["Login"] = $this->getLoginToRMS();
		$this->data["Notifications"] = $this->getNotifications();
		$this->data["Chat"] = $this->getChatActivities();

		$this->decoded_data = $this->data;

		foreach($this->data as $activity => $value) {
			$this->data[$activity] = json_encode($value);
		}
	}

	public function getNotifications()
	{
		$sum = array();

		$query = "SELECT COUNT(`id`) AS `ile`
					FROM `notifications` 
					WHERE `assigned_user_id`='{$this->user}' 
						AND `deleted`=0 
						AND `is_read`=0";

		$all = 0;
		$result = $this->db->query($query);
		while($row = $this->db->fetchByAssoc($result)) {
			$all += $row['ile'];
		}
		
		$sum['Number of all Notifications'] += $all;
		return $sum;
	}

	public function getLoginToRMS()
	{
		$sum = array(
			"Login by Mobile" => 0,
			"Normal Login" => 0,
		);
		$query = "SELECT COUNT(`id`) AS `count`, `mobile`
					FROM `tracker_mobile` 
					WHERE `id` LIKE '{$this->user}' 
						AND DATE(`date_entered`) = CURRENT_DATE
					GROUP BY `mobile`";

		$result = $this->db->query($query);
		while($row = $this->db->fetchByAssoc($result)) {
			if($row['Login by Mobile'] == 1) {
				$sum['Login by Mobile'] += $row['count'];
			} else {
				$sum['Normal Login'] += $row['count'];
			}
		}
		
		return $sum;
	}

	public function getBugInformations()
	{
		$sum = 0;
		$query = "SELECT COUNT(`id`) AS `count`
					FROM `bugs`
					WHERE `created_by`='{$this->user}'
						AND DATE(`date_entered`) = CURRENT_DATE";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		return ($sum += $row['count']);
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
			ADDDATE(CURRENT_DATE,-1),
			'{$this->data["Bugs"]}',
			'{$this->data["Login"]}',
			'{$this->data["Notifications"]}',
			'{$this->data["Chat"]}')"
		);
	}
}