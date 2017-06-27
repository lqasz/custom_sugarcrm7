<?php

class Meetings_CustomLogic
{	
	function setBasicData(&$bean, $event, $arguments)
	{
		$changes = array();
		$db = DBManagerFactory::getInstance();
		$changes['date_start_end'] = $this->setDateTime($bean->date_of_meeting_c);
		$changes['date_of_meeting_c'] = $bean->date_of_meeting_c;

		if(!empty($arguments['dataChanges']['date_start'])) {
		  $changes['date_start_end'] = $bean->date_start;
		  $changes['date_of_meeting_c'] = $this->setDateTime($arguments['dataChanges']['date_start']['after'], true);
		}

		$bean->load_relationship("leads_meetings_1");
		$reletedLead = $bean->leads_meetings_1->getBeans();

		foreach($reletedLead as $lead) {
		  $leadBean = BeanFactory::getBean('Leads', $lead->id);
		  $changes['name'] = $leadBean->name ." data: ". $changes['date_of_meeting_c'] ." ". $bean->contact_type_c;
		  $leadBean->status = $bean->lead_status_c;

		  if(!empty($arguments['dataChanges']['description']) || !empty($arguments['dataChanges']['date_of_meeting_c']) || !empty($arguments['dataChanges']['contact_type_c'])) {
	        $lead_description = $leadBean->description;
	        $contact_type = (!empty($arguments['dataChanges']['contact_type_c'])) ? $arguments['dataChanges']['contact_type_c']['before'] : $bean->contact_type_c;
	        $date_of_meeting = (!empty($arguments['dataChanges']['date_of_meeting_c'])) ? $arguments['dataChanges']['date_of_meeting_c']['before'] : $bean->date_of_meeting_c;
	        $description = (!empty($arguments['dataChanges']['description'])) ? $arguments['dataChanges']['description']['before'] : $bean->description;

	        $lead_description = preg_replace("/". $date_of_meeting .": ". $contact_type ."\n". $description ."\n" ."/", $bean->date_of_meeting_c .": ". $bean->contact_type_c ."\n". $bean->description ."\n", $lead_description);
	        $leadBean->description = $lead_description;
	      }

		  $leadBean->save();

		  if($arguments['isUpdate'] == 1) {
				$getRelatedTasks = $db->query("SELECT * FROM `tasks` WHERE `parent_id` = '{$bean->id}' AND `name` LIKE 'Proszę o uzupełnienie danych o spotkaniu%'");

				if($db->getRowCount($getRelatedTasks) > 0) {
					$db->query("UPDATE `tasks` SET `date_due` = '{$changes['date_start_end']}' WHERE `parent_id` = '{$bean->id}' AND `name` LIKE 'Proszę o uzupełnienie danych o spotkaniu%'");


				} else {
					$this->createTask("Meetings", $bean->id, $leadBean->name, $changes['date_of_meeting_c'], $bean);
				}
			}
		}
		
		$db->query("UPDATE `meetings` SET `name` = '{$changes['name']}', `date_start` = '{$changes['date_start_end']}', `date_end` = '{$changes['date_start_end']}' WHERE `id` = '{$bean->id}'");
		$db->query("UPDATE `meetings_cstm` SET `date_of_meeting_c` = '{$changes['date_of_meeting_c']}' WHERE `id_c` = '{$bean->id}'");
	}

	function manageMeetings(&$bean, $event, $arguments)
	{
		$new_one = false;

		if($bean->new_one_c == 1 && !empty($bean->date_of_next_meeting_c) && $bean->lead_status_c != "Involved") {
			$lead_name = "";
			$bean->load_relationship("leads_meetings_1");
			$reletedLead = $bean->leads_meetings_1->getBeans();
			$meetingBean = BeanFactory::newBean('Meetings', array('disable_row_level_security' => true));
			$meetingBean->new_with_id = true;
			$meetingBean->id = create_guid();
			$meetingBean->contact_type_c = "Meeting";
			$meetingBean->date_of_meeting_c = $bean->date_of_next_meeting_c;
			$meetingBean->assigned_user_id = $bean->assigned_user_id;
			$meetingBean->lead_status_c = $bean->lead_status_c;
			$meetingBean->new_one_c = 1;

			foreach($reletedLead as $lead) {
				$meetingBean->load_relationship("leads_meetings_1");
				$meetingBean->set_relationship("leads_meetings_1_c", array('leads_meetings_1leads_ida' => $lead->id,'leads_meetings_1meetings_idb' => $meetingBean->id), true, true);

				$leadBean = BeanFactory::getBean('Leads', $lead->id);
				$leadBean->description = "\n". $leadBean->description . $bean->date_of_meeting_c .": ". $bean->contact_type_c ."\n". $bean->description ."\n";

				$lead_name .= $leadBean->name ." ";
				$meetingBean->name = $lead_name ."data: ". $bean->date_of_next_meeting_c;
				$leadBean->save();
			}

			$meetingBean->save();
			$new_one = true;

			$this->createTask("Meetings", $meetingBean->id, $lead_name, $bean->date_of_next_meeting_c, $meetingBean);
		}

		if($new_one == true) {
			$db = DBManagerFactory::getInstance();
			$db->query("UPDATE `meetings_cstm` SET `new_one_c` = 0 WHERE `id_c` = '{$bean->id}'");
		}
	}

	private function createTask($parent_type, $parent_id, $lead_name, $date_due, $bean)
	{
		$taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
		$taskBean->new_with_id = true;
		$taskBean->id = create_guid();
		$taskBean->parent_type = $parent_type;
		$taskBean->parent_id = $parent_id;
		$taskBean->name = "Proszę o uzupełnienie danych o spotkaniu z ". $lead_name;
		$taskBean->date_due = date("Y-m-d H:i:s", strtotime($date_due));
		$taskBean->date_entered = $bean->date_entered;
		$taskBean->date_modified = $bean->date_modified;
		$taskBean->new_one_c = 0;
		$taskBean->created_by = $bean->created_by;
		$taskBean->assigned_user_id = $GLOBALS['current_user']->id;
		$taskBean->priority = "Low";

		$taskBean->save();
	}

	private function setDateTime($unformated_date, $only_date = false)
	{
		$date['day'] = date("d", strtotime($unformated_date));
		$date['month'] = date("m", strtotime($unformated_date));
		$date['year'] = date("Y", strtotime($unformated_date));

		$date_time = date('Y-m-d H:i:s', mktime(12, 0, 0, $date['month'], $date['day'], $date['year']));

		if($only_date) {
		  $date_time = date('Y-m-d', strtotime($date_time));
		}

		return $date_time;
	}
}

?>
