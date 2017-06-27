<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

require_once('modules/Teams/TeamSet.php');

/**
 * Class manage tasks
 */
class Tasks_CustomLogic
{
	private $db; // database instance

	public function __construct()
	{
		$this->db = DBManagerFactory::getInstance();
	} // constructor

	/**
	 * Function adds tasks to all active employees
	 */
	function addTaskRTeam(&$bean)
	{
		// select active employees
		$sql = 'SELECT id FROM users WHERE deleted = "0" AND employee_status = "Active" AND id NOT LIKE "'. $bean->assigned_user_id .'"';
		$result = $this->db->query($sql);

		// set default configuration, because hook is after save we do it by sql query
		$query = 'UPDATE tasks SET parent_type = "AA_RTeam", parent_id = "bb97e16c-2a8f-d68a-4c30-5714ed4d11a0" WHERE id = "'. $bean->id .'"';
		$this->db->query($query);

		// put new tasks to the database
		while($row = $this->db->fetchByAssoc($result) ) {
			// add all needed informations to the bean
			$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
			$new_id = create_guid();
			$taskBean->id = $new_id;
			$taskBean->name = $bean->name;
			$taskBean->new_with_id = true;
			$taskBean->date_due = $bean->date_due;
			$taskBean->date_entered = $bean->date_entered;
			$taskBean->date_modified = $bean->date_modified;
			$taskBean->new_one_c = 0;
			$taskBean->created_by = $bean->created_by;
			$taskBean->assigned_user_id = $row['id'];
			$taskBean->priority = $bean->priority;
			$main_assigned_team = $this->getUserTeam($taskBean->assigned_user_id);
			$secondary_assigned_team[] = $this->getUserTeam($bean->created_by);
			$taskBean->load_relationship('teams');
			$taskBean->team_id = $main_assigned_team;
	    	$taskBean->teams->replace($secondary_assigned_team);

			$taskBean->save();

			// set default configuration, because hook is after save we do it by sql query
			$query = 'UPDATE tasks SET parent_type = "AA_RTeam", parent_id = "bb97e16c-2a8f-d68a-4c30-5714ed4d11a0" WHERE id = "'. $new_id .'"';
			$this->db->query($query);
		} // while

		// set teams to the original bean
		$main_assigned_team = $this->getUserTeam($taskBean->created_by);
		$secondary_assigned_team[] = $this->getUserTeam($bean->created_by);
		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
    	$bean->teams->replace($secondary_assigned_team);
	}

	/**
	 * Function add teams to the tasks related to the department
	 */
	function addDepartmentTask(&$bean)
	{
		$assigned_teams = array();
		$query = 'SELECT `reports_to_id` AS `r_id` FROM `users` WHERE `id` = "'. $bean->assigned_user_id .'"';
		$result = $this->db->query($query);
		$row = $this->db->fetchByAssoc($result);

		// set teams
		$main_assigned_team = $this->getUserTeam($row['r_id']);
		$assigned_teams[] = $this->getUserTeam($bean->assigned_user_id);
    	$assigned_teams[] = $this->getUserTeam($bean->created_by);
    	$assigned_teams[] = "462fff89-1c17-981f-353b-57492384e7a2"; // board team

		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
    	$bean->teams->replace($assigned_teams);
	} // function

	/**
	 * Function add teams to the tasks related to the project
	 */
	function addProjectTask(&$bean)
	{
		$assigned_teams = array();
		$teamSetBean = new TeamSet();
		$project_bean = BeanFactory::getBean("Project", $bean->parent_id);

		$main_assigned_team = $this->getUserTeam($bean->assigned_user_id);
		$assigned_teams[] = $this->getUserTeam($bean->created_by);
		$assigned_teams[] = "462fff89-1c17-981f-353b-57492384e7a2"; // board team
		
		//Retrieve the teams from the team_set_id
		$teams = $teamSetBean->getTeams($project_bean->team_set_id);
		foreach($teams as $team) {
			$assigned_teams[] = $team->id; // all teams related to the project
		} // foreach

		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
		$bean->teams->replace($assigned_teams);
	} // function

	/**
	 * Function add teams to the all other tasks
	 */
	function addSimpleTask(&$bean)
	{
		$main_assigned_team = $this->getUserTeam($bean->assigned_user_id);
		$secondary_assigned_team[] = $this->getUserTeam($bean->created_by);
		$secondary_assigned_team[] = "462fff89-1c17-981f-353b-57492384e7a2";

		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
    	$bean->teams->replace($secondary_assigned_team);
	} // function

	/**
	 * Function add teams to the tasks related to the fee proposal
	 */
	function addFeeProposalTask(&$bean)
	{
		$assigned_teams = array();

		// select all needed informations (we could also done this by getBean)
		$sql = 'SELECT `ac_feeproposal`.`created_by` AS `created`, `ac_feeproposal`.`user_id_c` AS `Resp`, `ac_feeproposal`.`user_id1_c` AS `CAM`, `ac_feeproposal`.`user_id2_c` AS `SV` FROM `ac_feeproposal` LEFT JOIN `tasks` ON(`ac_feeproposal`.`id` = `tasks`.`parent_id`) WHERE `tasks`.`id` = "'. $bean->id .'"';
		$result = $this->db->query($sql);
		$row = $this->db->fetchByAssoc($result);

		$main_assigned_team = $this->getUserTeam($bean->assigned_user_id);
		$assigned_teams[] = $this->getUserTeam($bean->created_by);
		$assigned_teams[] = $this->getUserTeam($row['Resp']);
		$assigned_teams[] = $this->getUserTeam($row['CAM']);
		$assigned_teams[] = $this->getUserTeam($row['SV']);
		$assigned_teams[] = "462fff89-1c17-981f-353b-57492384e7a2";
		$qs_team = '96dc75a8-bfdc-4232-88df-57492304ef7a';

		// Loop through the members
		$sql = $this->db->query('SELECT DISTINCT `user_id` FROM `team_memberships` WHERE `team_id` LIKE "'. $qs_team .'"');
		while($team_member = $this->db->fetchByAssoc($sql)) {
			if($team_member['user_id'] == $bean->assigned_user_id) {
				$assigned_teams[] = $qs_team;
			} // if
		} // while

		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
		$bean->teams->replace($assigned_teams);
	} // function

	/**
	 * Function add teams to the tasks related to the opportunitie
	 */
	function addOpportunitieTask(&$bean)
	{
		$assigned_teams = array();

		// it is very similar to the addFeeProposalTask function
		$sql = 'SELECT `opportunities`.`created_by` AS `created`, `opportunities_cstm`.`user_id_c` AS `Resp`, `opportunities_cstm`.`user_id1_c` AS `CAM`, `opportunities_cstm`.`user_id2_c` AS `SV` FROM `opportunities` LEFT JOIN `tasks` ON(`opportunities`.`id` = `tasks`.`parent_id`) LEFT JOIN `opportunities_cstm` ON(`opportunities_cstm`.`id_c` = `opportunities`.`id`) WHERE `tasks`.`id` = "'. $bean->id .'"';
		$GLOBALS['log']->error($sql);
		$result = $this->db->query($sql);
		$row = $this->db->fetchByAssoc($result);

		$main_assigned_team = $this->getUserTeam($bean->assigned_user_id);
		$assigned_teams[] = $this->getUserTeam($bean->created_by);
		$assigned_teams[] = $this->getUserTeam($row['Resp']);
		$assigned_teams[] = $this->getUserTeam($row['CAM']);
		$assigned_teams[] = $this->getUserTeam($row['SV']);
		$assigned_teams[] = "462fff89-1c17-981f-353b-57492384e7a2";

		$bean->load_relationship('teams');
		$bean->team_id = $main_assigned_team;
		$bean->teams->replace($assigned_teams);
	} // function

	/**
	 * Function manage tasks by related parent type
	 */
	function manageTasks(&$bean, $event, $arguments)
	{
		if($bean->parent_type == "AA_RTeam") {
			$this->addTaskRTeam($bean);
		} elseif($bean->parent_type == "AA_Departments") {
			$this->addDepartmentTask($bean);
		} elseif($bean->parent_type == "Project") {
			$this->addProjectTask($bean);
		} elseif($bean->parent_type == "AC_FeeProposal") {
			$this->addFeeProposalTask($bean);
		} elseif($bean->parent_type == "Opportunities") {
			$this->addOpportunitieTask($bean);			
		} else {
			// if parent type is empty, it happens where we create task from quick task
			if(empty($bean->parent_type)) {

				// than add user department parent type and parent id
				$this->db->query("UPDATE `tasks` 
					SET `parent_type`='AA_Departments', 
						`parent_id`=(SELECT aa_departments_id_c FROM users_cstm WHERE id_c = '{$bean->assigned_user_id}') 
					WHERE parent_type IS NULL AND id='{$bean->id}'");
			} // if

			$this->addSimpleTask($bean);
		} // if/else
	} // function

	/**
	 * Function get user private team
	 * @return team id
	*/
	public function getUserTeam($user_id)
  	{
		$assignedResponsible = $this->db->query("SELECT t.* FROM teams t WHERE t.associated_user_id ='{$user_id}' AND t.private=1 AND t.deleted = 0 ");
		$teams = $this->db->fetchByAssoc($assignedResponsible);
		return $teams['id'];
  	} // function

  	/**
	 * Function departments associated to the user
	 * @return departments list
	*/
	public function getUserDepartment($user_id)
	{
		$departmentList = array();
		$result = $this->db->query('SELECT `teams`.`id` AS  `t_id` FROM `teams` LEFT JOIN `team_memberships` 
									ON(`teams`.`id` = `team_memberships`.`team_id`) LEFT JOIN `aa_departments` ON(`teams`.`name` = `aa_departments`.`name`)
									WHERE `team_memberships`.`user_id` LIKE "'. $user_id .'" AND `aa_departments`.`name` IS NOT NULL');

		while($row = $this->db->fetchByAssoc($result)) {
			$departmentList[] = $row;
		} // while

		return $departmentList;
	} // function

	/**
	 * Function repeats task in specified time range
	*/
	public function everyTime(&$bean, $event, $arguments)
	{
		// if it is some every task
		if($bean->deleted == 0 && empty($arguments['isUpdate']) && (!empty($bean->every_day_c) || !empty($bean->every_week_c) || !empty($bean->every_month_c)) && (!isset($_REQUEST['change']))) {
			$_REQUEST['change'] = 1; // set global change to 1 to prevent inf loop
			$start_day = $bean->date_start;
			$end_day = $bean->date_due;
			$final_end_date = $bean->date_due;
			$max_repeated = abs(strtotime($end_day) - strtotime($start_day)); // set range

			if ($bean->every_day_c == true) {
				$interval = 1;
				$max_repeated = floor($max_repeated / (60*60*24)); // seconds to days
			} elseif ($bean->every_week_c == true) {
				$interval = 7;
				$max_repeated = floor($max_repeated / (60*60*24*7)); // seconds to weeks
			} elseif ($bean->every_month_c == true) {
				$interval = 30; // default month value
				$max_repeated = floor($max_repeated / (60*60*24*30)); // seconds to abs(months)
			} // if/elseif

			$help_date = $bean->date_start;
			for($i = 1; $i <= $max_repeated - 1; $i++) {

				if($interval == 30) {
					$end_day = date('Y-m-d H:i:s', strtotime($help_date . ' +1 month'));
				} else {
					$end_day = date('Y-m-d H:i:s', strtotime($help_date . ' +'. $interval .' days')); // plus 1 or 7 days
				} // if/else

				// set new bean
				$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
				$taskBean->id = create_guid();
				$taskBean->name = $bean->name;
				$taskBean->new_with_id = true; // very important
				$taskBean->date_entered = $bean->date_entered;
				$taskBean->date_modified = $bean->date_modified;
				$taskBean->assigned_user_id = $bean->assigned_user_id;
				$taskBean->modified_user_id = $bean->modified_user_id;
				$taskBean->created_by = $bean->created_by;
				$taskBean->every_day_c = $bean->every_day_c;
				$taskBean->every_week_c = $bean->every_week_c;
				$taskBean->every_month_c = $bean->every_month_c;
				$taskBean->description = $bean->description;
				$taskBean->status = $bean->status;
				$taskBean->priority = $bean->priority;
				$taskBean->new_one_c = false;
				$taskBean->parent_type = $bean->parent_type;
				$taskBean->parent_id = $bean->parent_id;
				$taskBean->date_start = $help_date;

				// if SAT or SUN then due date bypasses weekend
				if(date('w', strtotime($end_day)) == 6) {
	            	$end_day = date('Y-m-d H:i:s', strtotime($end_day . ' +2 days'));

	            	if($interval == 1) { 
	            		if(strtotime($end_day) == strtotime($final_end_date)) { return; } // to prevent doubled records
	            		else { $i += 2; } // if/else
	            	} // if
	            } elseif(date('w', strtotime($end_day)) == 0) {
	            	$end_day = date('Y-m-d H:i:s', strtotime($end_day . ' +1 days'));

	            	if($interval == 1) { 
	            		if(strtotime($end_day) == strtotime($final_end_date)) { return; }
	            		else { $i++; } // if/else
	            	} // if
	            } // if/elseif

				// after validation set due date
				$taskBean->date_due = $end_day;

				$taskBean->save(); // after save task goes to the manageTasks function and then it will get a team 
				unset($taskBean);
				$taskBean = null;

				$help_date = $end_day; // next start date will be this due date
			} // for
		} // if
	} // function
} // class

?>