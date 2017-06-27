<?php

class Calls_CustomLogic
{
  function setBasicData(&$bean, $event, $arguments)
  {
    $changes = array();
    $db = DBManagerFactory::getInstance();
    $changes['date_start_end'] = $this->setDateTime($bean->date_of_call_c);
    $changes['date_of_call_c'] = $bean->date_of_call_c;

    if(!empty($arguments['dataChanges']['date_start'])) {
      $changes['date_start_end'] = $bean->date_start;
      $changes['date_of_call_c'] = $this->setDateTime($arguments['dataChanges']['date_start']['after'], true);
    }

    $bean->load_relationship("prospects_calls_1");
    $reletedTarget = $bean->prospects_calls_1->getBeans();

    foreach($reletedTarget as $target) {
      $targetBean = BeanFactory::getBean('Prospects', $target->id);
      $changes['name'] = $targetBean->name ." data: ". $changes['date_of_call_c'] ." Call";
      $targetBean->status_c = $bean->target_status_c;

      if(!empty($arguments['dataChanges']['description']) || !empty($arguments['dataChanges']['date_of_call_c'])) {
        $target_description = $targetBean->description;
        $date_of_call = (!empty($arguments['dataChanges']['date_of_call_c'])) ? $arguments['dataChanges']['date_of_call_c']['before'] : $bean->date_of_call_c;
        $description = (!empty($arguments['dataChanges']['description'])) ? $arguments['dataChanges']['description']['before'] : $bean->description;

        $target_description = preg_replace("/". $date_of_call .": Call"."\n". $description ."\n" ."/", $bean->date_of_call_c .": Call"."\n". $description ."\n", $target_description);
        $targetBean->description = $target_description;
      }

      $targetBean->save();

      if($arguments['isUpdate'] == 1) {
        $getRelatedTasks = $db->query("SELECT * FROM `tasks` WHERE `parent_id` = '{$bean->id}' AND `name` LIKE 'Proszę o uzupełnienie danych o spotkaniu%' AND (`deleted` = 0 OR status NOT LIKE 'Completed')");

        if($db->getRowCount($getRelatedTasks) > 0) {
          $db->query("UPDATE `tasks` SET `date_due` = '{$changes['date_start_end']}' WHERE `parent_id` = '{$bean->id}' AND `name` LIKE 'Proszę o uzupełnienie danych o spotkaniu%'");
        } else {
          $this->createTask("Calls", $bean->id, $targetBean->name, $changes['date_of_call_c'], $bean);
        }
      }
    }

    $db->query("UPDATE `calls` SET `name` = '{$changes['name']}', `date_start` = '{$changes['date_start_end']}', `date_end` = '{$changes['date_start_end']}' WHERE `id` = '{$bean->id}'");
    $db->query("UPDATE `calls_cstm` SET `date_of_call_c` = '{$changes['date_of_call_c']}' WHERE `id_c` = '{$bean->id}'");
  }

  function manageCalls(&$bean, $event, $arguments)
  {
    $new_one = false;

    if($bean->new_one_c == 1 && !empty($bean->date_of_next_call_c) && $bean->target_status_c != "Convert to Lead") {
      $target_name = "";
      $bean->load_relationship("prospects_calls_1");
      $reletedTarget = $bean->prospects_calls_1->getBeans();

      $callBean = BeanFactory::newBean('Calls', array('disable_row_level_security' => true));
      $callBean->new_with_id = true;
      $callBean->id = create_guid();
      $callBean->date_of_call_c = $bean->date_of_next_call_c;
      $callBean->assigned_user_id = $bean->assigned_user_id;
      $callBean->target_status_c = $bean->target_status_c;
      $callBean->new_one_c = 1;

      foreach ($reletedTarget as $target) {
        $callBean->load_relationship("prospects_calls_1");
        $callBean->set_relationship("prospects_calls_1_c", array('prospects_calls_1prospects_ida' => $target->id,'prospects_calls_1calls_idb' => $callBean->id), true, true);

        $targetBean = BeanFactory::getBean('Prospects', $target->id);
        $targetBean->description = "\n". $targetBean->description . $bean->date_of_call_c .": Call"."\n". $bean->description ."\n";

        $target_name = $targetBean->name;
        $callBean->name = "";
        $targetBean->save();
      }

      $callBean->save();
      $new_one = true;

      $this->createTask("Calls", $callBean->id, $target_name, $bean->date_of_next_call_c, $callBean);  
    }

    if($new_one == true) {
      $db = DBManagerFactory::getInstance();
      $db->query("UPDATE `calls_cstm` SET `new_one_c` = 0 WHERE `id_c` = '{$bean->id}'");
    }
  }

  private function createTask($parent_type, $parent_id, $target_name, $date_due, $bean)
  {
    $taskBean = BeanFactory::newBean('Tasks', array('disable_row_level_security' => true));
    $taskBean->new_with_id = true;
    $taskBean->id = create_guid();
    $taskBean->parent_type = $parent_type;
    $taskBean->parent_id = $parent_id;
    $taskBean->name = "Proszę o uzupełnienie danych o spotkaniu z ". $target_name;
    $taskBean->date_due = $this->setDateTime($date_due);
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

  private function setNewDescription()
  {

  }
}

?>
