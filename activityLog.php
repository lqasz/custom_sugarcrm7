<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Class overview of users activity
*/
class ActivityLog
{
    private $db;
    public $activity_array = null;
    /**
    * Constructor of the class
    */
    function __construct()
    {

        $this->db = DBManagerFactory::getInstance();
        $this->getActivity();
    }

    /**
    * Function gets inactive users from yesterday
    */
    public function getActivity()
    {
        echo 'getActivity<br />';
        $this->activity_array = null;
        $result = $this->db->query("SELECT `u`.`id`, `u`.`first_name`, `u`.`last_name`, `uc`.`aa_departments_id_c`
                                    FROM `users` `u`
                                        INNER JOIN `users_cstm` `uc` ON(`u`.`id` = `uc`.`id_c`)
                                    WHERE `u`.`id` NOT IN (
                                                SELECT `id`
                                                FROM `tracker_mobile`
                                                WHERE `date_entered` BETWEEN DATE(SUBDATE(current_date, 1)) AND DATE(CURDATE())
                                                GROUP BY `id`
                                            )
                                            AND `u`.`id` NOT IN (
                                                SELECT `assigned_user_id` 
                                                FROM `ac_holiday` 
                                                INNER JOIN `ac_holiday_cstm` ON(`id` = `id_c`) 
                                                WHERE `v_from` <= DATE(SUBDATE(current_date, 1)) AND `v_to` >= DATE(SUBDATE(current_date, 1))
                                                GROUP BY id
                                            )
                                            AND `u`.`show_on_employees` = 1 AND `u`.`deleted` = 0 
                                            AND DATE(`last_login`) NOT LIKE DATE(CURDATE())");

        while($row = $this->db->fetchByAssoc($result)) {
            $this->activity_array[$row['aa_departments_id_c']][] = $row['first_name'] ." ". $row['last_name'];
        }

        return $this->activity_array;
    }

    public function addNotificationForPM()
    {
        echo 'addNotification<br />';

        $all_descriptions = ''; // zbiorczy desc dla JJ
        // brakt osób niezalogowanych dnia poprzedniego
        if(empty($this->activity_array) || count($this->activity_array) == 0) {
            $all_descriptions = "Wszyscy z Twojego zespołu zalogowali się dnia poprzedniego!";
        }

        // wczytanie departmentów z id lidera, board date_entered ustawione jako najstarsze,
        // dla pewności, że JJ zbierze w pętli wszystkie notyfikacje
        $result = $this->db->query("SELECT id, assigned_user_id
                                    FROM aa_departments
                                    WHERE custom_team=0
                                    ORDER BY date_entered DESC ");

        while($department = $this->db->fetchByAssoc($result)) {
			$notif = BeanFactory::newBean('Notifications');

			if($department['assigned_user_id'] != '144c39bf-ccc3-65ec-2023-5407f7975b91'){
                $all_descriptions .= (!empty($this->activity_array[$department['id']])) ? implode("\n", $this->activity_array[$department['id']])."\n" : "";
				$notif->description = (!empty($this->activity_array[$department['id']])) ? implode("\n", $this->activity_array[$department['id']])."\n" : "Wszyscy z Twojego zespołu zalogowali się dnia poprzedniego!";

                $notif->name = ($notif->description == "Wszyscy z Twojego zespołu zalogowali się dnia poprzedniego!") ? "Wszyscy z Twojego zespołu zalogowali się dnia ". date("d-m", mktime(0, 0, 0, date("m"), date("d")-1, date("Y"))) : "Osoby niezalogowane dnia ". date("d-m", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
			} else {
				$notif->description = $all_descriptions;
                $notif->name = ($all_descriptions == "Wszyscy z Twojego zespołu zalogowali się dnia poprzedniego!") ? "Wszyscy z Twojego zespołu zalogowali się dnia ". date("d-m", mktime(0, 0, 0, date("m"), date("d")-1, date("Y"))) : "Osoby niezalogowane dnia ". date("d-m", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
			}

			$notif->assigned_user_id = $department['assigned_user_id'];
			$notif->severity = "information";
			$notif->created_by = "1";
			$notif->confirmation = 1;
			$notif->is_read = 0;
            $notif->deleted = 0;
			$notif->save();
			$notif = null;
			unset($notif);
        } // while
        echo 'END<br />';
    } //function
}

$activityLog = new ActivityLog();
// echo 'Nie było:<br /><pre>'.print_r($activityLog->activity_array, true).'</pre>';

$activityLog->addNotificationForPM();
