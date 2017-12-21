<?php

class AB_TimeSheetQS_CustomLogic
{
	private $db;

	public function __construct()
	{
		$this->db = DBManagerFactory::getInstance();
	}
	
    function businessLogic(&$bean, $event, $arguments)
    {
    	$run = true;
		$assignedTeams = array();
    	$teamSetBean = new TeamSet();

		$user_id = $bean->assigned_user_id;
		while($run) {
			$reports_to = $this->db->fetchByAssoc($this->db->query("SELECT `reports_to` AS `id`FROM `ab_timesheet_department_structure` WHERE `user_id`='{$user_id}'"));

			if($reports_to['id'] == $user_id) {
				$run = false;
			}

			$user_id = $reports_to['id'];
			$assignedTeams[] = $teamSetBean->getUserPrivateTeam($reports_to['id']);
		}

		$time_sheet_QS = BeanFactory::getBean("AB_TimeSheetQS", $bean->id);
		$time_sheet_QS->load_relationship('teams');
		$assignedTeams[] = $teamSetBean->getUserPrivateTeam('801c0c78-edc1-e54f-08c2-5407f786ce48');
		$assignedTeams[] = $teamSetBean->getTeamViaDepartment('4d4281ae-106b-b8dd-d888-56a5b788ca8d');

		$time_sheet_QS->team_id = $teamSetBean->getUserPrivateTeam($bean->assigned_user_id);
		$time_sheet_QS->teams->replace($assignedTeams);
    }
}