<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Accounts_CustomLogic
{
	function addTask($bean, $event, $arguments) 
	{
		$db = DBManagerFactory::getInstance();

		if($bean->phone_office == "" || $bean->billing_address_street == "" || $bean->billing_address_city == "" || $bean->nip_c == "") {
			$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
			$taskBean->new_with_id = true;
			$taskBean->id = create_guid();
			$taskBean->parent_type = "Accounts";
			$taskBean->parent_id = $bean->id;
			$taskBean->name = "Proszę o uzupełnienie danych firmy: ". $bean->name;
			$taskBean->date_due = (!empty($bean->date_entered)) ? $bean->date_entered : $bean->date_modified;
			$taskBean->date_entered = $bean->date_entered;
			$taskBean->date_modified = $bean->date_modified;
			$taskBean->new_one_c = 0;
			$taskBean->created_by = $bean->created_by;
			$taskBean->assigned_user_id = "9122d6b9-46e5-9013-99f7-540f4beb464e";
			$taskBean->priority = "Low";
			$main_assigned_team = $this->getUserTeam($taskBean->assigned_user_id, $db);
			$secondary_assigned_team[] = $this->getUserTeam($taskBean->created_by, $db);
			$taskBean->load_relationship('teams');
			$taskBean->team_id = $main_assigned_team;
	        $taskBean->teams->replace($secondary_assigned_team);

			$taskBean->save();
		}
	}

	public function getUserTeam($user_id, $db)
    {
        $assignedResponsible = $db->query("SELECT t.*
                FROM teams t
                WHERE t.associated_user_id ='{$user_id}' AND t.private=1 AND t.deleted = 0 ");
        $teams = $db->fetchByAssoc($assignedResponsible);
        return $teams['id'];
    }
}