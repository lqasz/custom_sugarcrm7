<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from other activities tables
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

		$this->data["Bugs"] = $this->getBugInformations(); // get all bugs created today
		$this->data["Login"] = $this->getLoginToRMS(); // get device which was used to login
		$this->data["Notifications"] = $this->getNotifications(); // get all unread notifications
		$this->data["Chat"] = $this->getChatActivities(); // get all chat activities from today
	}

	private function getNotifications()
	{
		$query = "SELECT COUNT(`id`) AS `count`
					FROM `notifications` 
					WHERE `assigned_user_id`='{$this->user}' 
						AND `deleted`=0 
						AND `is_read`=0";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);
		
		return $row['count'];
	}

	private function getLoginToRMS()
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

	private function getBugInformations()
	{
		$sum = 0;
		$query = "SELECT COUNT(`id`) AS `count`
					FROM `bugs`
					WHERE `created_by`='{$this->user}'
						AND DATE(`date_entered`) = CURRENT_DATE";

		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		return $row['count'];
	}

	private function getChatActivities()
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
		// insert {$user_name} today activities
		$this->db->query("INSERT INTO `rms_report_activities` VALUES(
			'".create_guid()."', 
			'{$user_name}',
			CURRENT_TIMESTAMP,
			'{$this->data["Bugs"]}',
			'{$this->data["Login"]["Normal Login"]}',
			'{$this->data["Login"]["Login by Mobile"]}',
			'{$this->data["Notifications"]}',
			'{$this->data["Chat"]}')"
		);
	}
}