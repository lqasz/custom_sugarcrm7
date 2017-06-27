<?php

class Leads_CustomLogic
{
	function addTask($bean, $event, $arguments) 
	{
		$db = DBManagerFactory::getInstance();

		if(empty($bean->linkedin_c) || empty($bean->title) || empty($bean->refered_by)) 
		{
			$secondary_assigned_team = array();
			$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
			$taskBean->new_with_id = true;
			$taskBean->id = create_guid();
			$taskBean->parent_type = "Leads";
			$taskBean->parent_id = $bean->id;
			$taskBean->name = "Proszę o uzupełnienie danych o osobie ". $bean->name;
			$taskBean->date_due = (!empty($bean->date_entered)) ? $bean->date_entered : $bean->date_modified;
			$taskBean->date_entered = $bean->date_entered;
			$taskBean->date_modified = $bean->date_modified;
			$taskBean->new_one_c = 0;
			$taskBean->created_by = $bean->created_by;
			$taskBean->assigned_user_id = $GLOBALS['current_user']->id;
			$taskBean->priority = "Low";

			$taskBean->save();
		}
	}

	function createFirstMeeting(&$bean, $event, $arguments)
    {
    	if($arguments['isUpdate'] != 1) {
			$meetingBean = BeanFactory::newBean('Meetings', array('disable_row_level_security' => true));
			$meetingBean->new_with_id = true;
			$meetingBean->id = create_guid();
			$meetingBean->name = $bean->name ." data: ". date("Y-m-d", strtotime($bean->data_of_first_meeting_c));
			$meetingBean->date_of_meeting_c = date("Y-m-d", strtotime($bean->data_of_first_meeting_c));
			$meetingBean->assigned_user_id = $bean->assigned_user_id;
			$meetingBean->lead_status_c = $bean->status;
			$meetingBean->new_one_c = 1;

			$meetingBean->load_relationship("leads_meetings_1");
			$meetingBean->set_relationship("leads_meetings_1_c", array('leads_meetings_1leads_ida' => $bean->id,'leads_meetings_1meetings_idb' => $meetingBean->id), true, true);

			$GLOBALS['log']->fatal(print_r($meetingBean, true));
			$meetingBean->save();
    	}
    }
}