<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Contacts_CustomLogic
{
	function businessLogic(&$bean, $event, $arguments) 
	{
		if($bean->syncWithContact != 1) {
			if(!empty($bean->lead_id_c)) {
				$lead_bean = BeanFactory::getBean('Leads', $bean->lead_id_c);
				$lead_bean->first_name = $bean->first_name;
				$lead_bean->last_name = $bean->last_name;
				$lead_bean->title = $bean->title;
				$lead_bean->website = $bean->website_c;
				$lead_bean->phone_mobile = $bean->phone_mobile;
				$lead_bean->linkedin_c = $bean->linkedin_c;
				$lead_bean->account_id = $bean->account_id;
				$lead_bean->contact_id_c = $bean->id;
				$lead_bean->syncWithContact = 1;
				$lead_bean->save();
				unset($lead_bean);
			} else if(!empty($bean->prospect_id_c)) {
				$target_bean = BeanFactory::getBean('Prospects', $bean->prospect_id_c);
				$target_bean->first_name = $bean->first_name;
				$target_bean->last_name = $bean->last_name;
				$target_bean->title = $bean->title;
				$target_bean->website_c = $bean->website_c;
				$target_bean->phone_mobile = $bean->phone_mobile;
				$target_bean->linkedin_c = $bean->linkedin_c;
				$target_bean->account_id = $bean->account_id;
				$target_bean->contact_id_c = $bean->id;
				$target_bean->syncWithContact = 1;
				$target_bean->save();
				unset($target_bean);
			}
		}
	}
}