<?php

class Prospects_CustomLogic
{
	function addTask(&$bean, $event, $arguments) 
	{
		$db = DBManagerFactory::getInstance();

		if(empty($bean->linkedin_c) || empty($bean->title)) {
			$this->createTask("Proszę o uzupełnienie danych o osobie ". $bean->name, $bean);
		}

		if(empty($bean->date_of_first_call_c)) {
			$this->createTask("Proszę o uzupełnienie daty pierwszego kontaktu z ". $bean->name, $bean);
		}
	}

	function createFirstCall(&$bean, $event, $arguments)
    {
    	$db = DBManagerFactory::getInstance();
    	$get_calls = $db->query("SELECT `id` FROM `prospects_calls_1_c` WHERE `prospects_calls_1prospects_ida` = '{$bean->id}'");

    	if(!empty($bean->date_of_first_call_c) && ($db->getRowCount($get_calls) == 0)) {
			$callBean = BeanFactory::newBean('Calls', array('disable_row_level_security' => true));
			$callBean->new_with_id = true;
			$callBean->id = create_guid();
			$callBean->name = $bean->name .", ". date("Y-m-d", strtotime($bean->date_of_first_call_c));
			$callBean->date_of_call_c = date("Y-m-d", strtotime($bean->date_of_first_call_c));
			$callBean->assigned_user_id = $bean->assigned_user_id;
			$callBean->target_status_c = $bean->target_status_c;
			$callBean->new_one_c = 1;

			$callBean->load_relationship("prospects_calls_1");
			$callBean->set_relationship("prospects_calls_1_c", array('prospects_calls_1prospects_ida' => $bean->id,'prospects_calls_1calls_idb' => $callBean->id), true, true);

			$callBean->save();
    	}
    }

    function createPerson(&$bean, $event, $arguments)
    {
    	$db = DBManagerFactory::getInstance();
    	$get_contact_data = $db->query("SELECT `id` FROM `contacts` WHERE `deleted` = 0 AND `first_name` = '{$bean->first_name}' AND `last_name` = '{$bean->last_name}'");

    	$GLOBALS['log']->fatal(print_r($bean, true));

    	if($db->getRowCount($get_contact_data) == 0) {
    		$contact_bean = BeanFactory::newBean('Contacts');
    		$contact_bean->new_with_id = true;
    		$contact_bean->id = create_guid();
    		$contact_bean->salutation = $bean->salutation;
    		$contact_bean->first_name = $bean->first_name;
    		$contact_bean->last_name = $bean->last_name;
    		$contact_bean->title = $bean->title;
    		$contact_bean->facebook = $contact_bean->facebook;
    		$contact_bean->twitter = $bean->twitter;
    		$contact_bean->department = $bean->department;
    		$contact_bean->website_c = $bean->website_c;
    		$contact_bean->phone_mobile = $bean->phone_mobile;
    		$contact_bean->phone_work = $bean->phone_work;
    		$contact_bean->description = $bean->description;
    		$contact_bean->primary_address_street = $bean->primary_address_street;
    		$contact_bean->primary_address_city = $bean->primary_address_city;

    		for($i = 1; $i < 10; $i++) {
    			if(!empty($bean->{'email'.$i})) {
    				$contact_bean->{'email'.$i} = $bean->{'email'.$i};
    			} else {
    				break;
    			}
    		}

    		$contact_bean->save();
    	}
    }

    function createTask($name, $bean)
    {
    	$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
		$taskBean->new_with_id = true;
		$taskBean->id = create_guid();
		$taskBean->parent_type = "Prospects";
		$taskBean->parent_id = $bean->id;
		$taskBean->name = $name;
		$taskBean->date_due = date('Y-m-d H:i:s', strtotime($bean->date_modified));
		$taskBean->date_entered = date('Y-m-d H:i:s', strtotime($bean->date_entered));
		$taskBean->date_modified = date('Y-m-d H:i:s', strtotime($bean->date_modified));
		$taskBean->new_one_c = 0;
		$taskBean->created_by = $bean->created_by;
		$taskBean->assigned_user_id = $GLOBALS['current_user']->id;
		$taskBean->priority = "Low";

		$taskBean->save();
    }
}