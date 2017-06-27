<?php

/**
 * Class manage contacts
 */
class Contacts_CustomLogic
{
	/**
	 * Function adds task to the user if he didn`t make up all required data
	 */
	function addTask($bean, $event, $arguments) 
	{
		if($bean->phone_mobile == "" || $bean->primary_address_street == "" || $bean->primary_address_city == "") {
			$db = DBManagerFactory::getInstance();
			// add new task with all needed data
			$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
			$taskBean->new_with_id = true;
			$taskBean->id = create_guid();
			$taskBean->parent_type = "Contacts";
			$taskBean->parent_id = $bean->id;
			$taskBean->name = "Complete contact: ". $bean->first_name ." ". $bean->last_name;
			$taskBean->date_due = $bean->date_entered;
			$taskBean->date_entered = $bean->date_entered;
			$taskBean->date_modified = $bean->date_modified;
			$taskBean->new_one_c = 0;
			$taskBean->created_by = $bean->created_by;
			$taskBean->assigned_user_id = $bean->created_by;
			$taskBean->priority = "Low";
			$main_assigned_team = $this->getUserTeam($taskBean->assigned_user_id, $db);
			$secondary_assigned_team[] = $this->getUserTeam($taskBean->created_by, $db);
			$taskBean->load_relationship('teams');
			$taskBean->team_id = $main_assigned_team;
	        	$taskBean->teams->replace($secondary_assigned_team);

			$taskBean->save();
		} // if
	} // function

	/**
	 * Function gets private team from user
	 */
	public function getUserTeam($user_id, $db)
    {
        $assignedResponsible = $db->query("SELECT t.*
                FROM teams t
                WHERE t.associated_user_id ='{$user_id}' AND t.private=1 AND t.deleted = 0 ");
        $teams = $db->fetchByAssoc($assignedResponsible);
        return $teams['id'];
    } // function
} // class
