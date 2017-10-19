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

    function createPerson(&$bean, $event, $arguments)
    {
    	$db = DBManagerFactory::getInstance();
        $contact_result = $db->query("SELECT `id` FROM `contacts` WHERE `deleted` = 0 AND `first_name` = '{$bean->first_name}' AND `last_name` = '{$bean->last_name}'");

        if($db->getRowCount($contact_result) == 0) {
            $contact_bean = BeanFactory::newBean('Contacts');
            $contact_bean->new_with_id = true;
            $contact_bean->id = create_guid();
            $contact_bean->first_name = $bean->first_name;
            $contact_bean->last_name = $bean->last_name;
            $contact_bean->title = $bean->title;
            $contact_bean->website_c = $bean->website;
            $contact_bean->phone_mobile = $bean->phone_mobile;
            $contact_bean->linkedin_c = $bean->linkedin_c;
            $contact_bean->contact_stage_c = "lead";
            $contact_bean->description = "";

            for($i = 1; $i < 10; $i++) {
                if(!empty($bean->{'email'.$i})) {
                    $contact_bean->{'email'.$i} = $bean->{'email'.$i};
                } else {
                    break;
                }
            }

            $contact_bean->load_relationship('accounts_contacts');
            $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_bean->id ,'account_id' => $bean->accounts_leads_1accounts_ida), true, true);

            $contact_bean->save();
        } else {
            $contact_row = $db->fetchByAssoc($contact_result);
            $contact_bean = BeanFactory::getBean('Contacts', $contact_row['id']);
            $contact_bean->first_name = $bean->first_name;
            $contact_bean->last_name = $bean->last_name;
            $contact_bean->title = $bean->title;
            $contact_bean->website_c = $bean->website;
            $contact_bean->phone_mobile = $bean->phone_mobile;
            $contact_bean->linkedin_c = $bean->linkedin_c;
            $contact_bean->contact_stage_c = "lead";

            for($i = 1; $i < 10; $i++) {
                if(!empty($bean->{'email'.$i})) {
                    $contact_bean->{'email'.$i} = $bean->{'email'.$i};
                } else {
                    break;
                }
            }

            $contact_bean->load_relationship('accounts_contacts');
            $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_row['id'] ,'account_id' => $bean->accounts_leads_1accounts_ida), true, true);

            $contact_bean->save();
        }
    }

    function businessLogic(&$bean, $event, $arguments)
    {
        
    }
}