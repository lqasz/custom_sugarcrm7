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

    function associatePerson(&$bean, $event, $arguments)
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
    		$contact_bean->website_c = $bean->website_c;
            $contact_bean->phone_mobile = $bean->phone_mobile;
    		$contact_bean->linkedin_c = $bean->linkedin_c;
            $contact_bean->contact_stage_c = "target";
    		$contact_bean->description = "";

    		for($i = 1; $i < 10; $i++) {
    			if(!empty($bean->{'email'.$i})) {
    				$contact_bean->{'email'.$i} = $bean->{'email'.$i};
    			} else {
    				break;
    			}
    		}

            $contact_bean->load_relationship('accounts_contacts');
            $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_bean->id ,'account_id' => $bean->accounts_prospects_1accounts_ida), true, true);

    		$contact_bean->save();
    	} else {
            $contact_row = $db->fetchByAssoc($contact_result);
            $contact_bean = BeanFactory::getBean('Contacts', $contact_row['id']);
            $contact_bean->first_name = $bean->first_name;
            $contact_bean->last_name = $bean->last_name;
            $contact_bean->title = $bean->title;
            $contact_bean->website_c = $bean->website_c;
            $contact_bean->phone_mobile = $bean->phone_mobile;
            $contact_bean->linkedin_c = $bean->linkedin_c;

            if($contact_bean->contact_stage_c == "contact" || $contact_bean->contact_stage_c == "") {
                $contact_bean->contact_stage_c = "target";
            }

            for($i = 1; $i < 10; $i++) {
                if(!empty($bean->{'email'.$i})) {
                    $contact_bean->{'email'.$i} = $bean->{'email'.$i};
                } else {
                    break;
                }
            }

            $contact_bean->load_relationship('accounts_contacts');
            $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_row['id'] ,'account_id' => $bean->accounts_prospects_1accounts_ida), true, true);

            $contact_bean->save();
        }
    }

    function businessLogic(&$bean, $event, $arguments)
    {
        $choice = str_replace("^", "", $bean->client_for_c);
        $choice = split(",", $choice);

        foreach(array("rsc_c", "pkig_c", "ats_c", "rwt_c", "rdsg_c") as $value) {
            $bean->{$value} = 0;
        }

        foreach($choice as $key => $value) {
            $company = strtolower($value) ."_c";

            $bean->{$company} = 1;
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