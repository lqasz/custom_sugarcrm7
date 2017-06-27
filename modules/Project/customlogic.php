<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**************************************************/
//
//   function addToRelatedFields - update fee status to win
//   function addToRelatedFields also save a connection to tenant in companies
//

class Project_Customlogic
{
    private function getTeamViaDepartment($depID)
    {
        // wybiera aktywne departmenty i pobiera ich short_c to jest porównywalne z descriptionz z starego
        $db = DBManagerFactory::getInstance();

        $departmentList = array();
        $result = $db->query("SELECT d.*, dc.*, t.id as tid, t.description as tshort
            FROM `aa_departments` d
            LEFT JOIN `aa_departments_cstm` dc ON (d.id=dc.id_c)
            LEFT JOIN `teams` t ON (dc.short_c=t.description)
            WHERE d.deleted=0 AND d.id='{$depID}' ");

        while ($row = $db->fetchByAssoc($result)) {
            $departmentList[] = $row;
        }

        return $departmentList[0]['tid'];
    }

    private function getUserTeam($user_id)
    {
        $db = DBManagerFactory::getInstance();

        $assignedResponsible= $db->query("SELECT t.*
                FROM teams t
                WHERE t.associated_user_id ='{$user_id}' AND t.private=1 AND t.deleted = 0 ");
        $teams = $db->fetchByAssoc($assignedResponsible);
        return $teams['id'];
    }
    // kopiowanie załączników z fee proposala do projektu
    // nie potrzeba dodawać relacji do tego project_notes_1, dodaje się automatycznie przy zapisie
    public function copyAttachments(&$bean, $event, $arguments)
    {
        $GLOBALS['log']->error('sprawdzenie czy to nowy rekord czy aktualizacja, jeżeli nowy to kopiujemy');
        if ($arguments['isUpdate'] == false) {
            $GLOBALS['log']->error('kopiowanie załączników - copyAttachments');

            $fp_bean = BeanFactory::getBean('AC_FeeProposal', $bean->project_ac_feeproposal_1ac_feeproposal_idb);

            if ($fp_bean->load_relationship('ac_feeproposal_activities_1_notes')) {
                $working_dir = "/home/admin/domains/". $_SERVER['HTTP_HOST'] ."/public_html/upload/";

                $relatedNotes = $fp_bean->ac_feeproposal_activities_1_notes->getBeans();

                if (count($relatedNotes)>0) {
                    foreach ($relatedNotes as $key => $feeNote) {
                        $GLOBALS['log']->error('duplikacja załączników do nowo utworzonego projektu- copyAttachments');
                        $pNote = BeanFactory::newBean('Notes');
                        $GLOBALS['log']->error($feeNote->name);
                        $pNote->name = $feeNote->name;
                        $pNote->parent_name = $bean->name;
                        $pNote->parent_type = 'Project';
                        $pNote->parent_id = $bean->id;
                        $pNote->filename = $feeNote->filename;
                        $pNote->file_mime_type = $feeNote->file_mime_type;
                        $pNote->save();

                        $GLOBALS['log']->error($pNote->id);

                        if (!copy($working_dir.$feeNote->id, $working_dir.$pNote->id)) {
                            $GLOBALS['log']->error('failed to copy');
                        }
                        unset($pNote);

                        $GLOBALS['log']->error('przepisanie do projektu - copyAttachments');
                    }
                }
            }
        }
    }

    public function setRelationship(&$bean, $event, $arguments)
    {
        if(empty($bean->load_relationship("project_ac_feeproposal_1_c"))) {
            $db = DBManagerFactory::getInstance();
            $fee = $db->fetchByAssoc($db->query("SELECT `id`, `fees_porposal_number_c` FROM `ac_feeproposal` INNER JOIN `ac_feeproposal_cstm` ON(`id` = `id_c`) WHERE name LIKE '{$bean->name}'"));
            $bean->set_relationship("project_ac_feeproposal_1_c", array("project_ac_feeproposal_1project_ida" => $bean->id, "project_ac_feeproposal_1ac_feeproposal_idb" => $fee['id']), true, true);
            $bean->project_number_c = $fee['fees_porposal_number_c'];
        }
    }

    public function trackPeopleChanges(&$bean, $event, $arguments)
    {
        $GLOBALS['log']->fatal('dodanie relacji do firm - addRelationToAccount');
        global $db;

        if($arguments['isUpdate'] == true) {
            $users_array = array("user_id_c", "user_id1_c", "user_id2_c");

            foreach($users_array as $user) {
                if(!empty($arguments['dataChanges'][$user])) {
                    $old_user_query = $db->query('SELECT `id` FROM `tasks` WHERE `parent_id` = "'. $bean->id .'" AND `assigned_user_id` = "'. $arguments['dataChanges'][$user]['before'] .'" AND `deleted` = "0" AND `status` LIKE "Not Started" ');

                    while ($old_user_row = $db->fetchByAssoc($old_user_query)) {
                        $task = BeanFactory::getBean('Tasks', $old_user_row['id']);

                        $task->assigned_user_id = $arguments['dataChanges'][$user]['after'];
                        $task->save();
                        unset($task);
                        $task = null;
                    }

                    $old_user_query = $db->query('SELECT `id` FROM `notifications` WHERE `parent_type` = "AC_Invoices" AND `assigned_user_id` = "'. $arguments['dataChanges'][$user]['before'] .'" AND `deleted` = "0" AND `name` LIKE "%'.$bean->project_number_c.'%"');

                    while ($old_user_row = $db->fetchByAssoc($old_user_query)) {
                        $notifi = BeanFactory::getBean('Notifications', $old_user_row['id']);

                        $notifi->assigned_user_id = $arguments['dataChanges'][$user]['after'];
                        $notifi->save();
                        unset($notifi);
                        $notifi = null;
                    }
                }   
            }
        }
    } // trackPeopleChanges

    public function createTasks(&$bean, $event, $arguments)
    {

        $GLOBALS['log']->error('sprawdzenie czy to nowy rekord czy aktualizacja, jeżeli nowy to kopiujemy');
        if ($arguments['isUpdate'] == false) {
            $GLOBALS['log']->error('tworzenie zadań do projektu - createTasks');
            global $current_user;
            // if we save invoice and add agreement
            if ($_REQUEST['module'] == 'FA_invoices') {
                // nie wiem po co ale ok
                $GLOBALS['log']->error('jak wystąpi to obczaj customlogic projektów');
            } else {
                for ($i = 0; $i < 35; $i++) {
                    if (!empty($_REQUEST['task_name_' . $i])) {
                        $GLOBALS['log']->error('tworzymy taska z relacją do projektu');
                        $project_task_bean = BeanFactory::newBean('Tasks');
                        $project_task_bean->name = $_REQUEST['task_name_' . $i];

                        // dopisać zmianę daty jeżeli jest S lub E
                        if (strlen($_REQUEST['task_date_start_' . $i]) == 10) {
                            list($d, $m, $y) = explode('/', $_REQUEST['task_date_start_' . $i]);
                            $date_start = $y . '-' . $m . '-' . $d;
                            $project_task_bean->date_due = date_format($date_start, 'Y-m-d H:i:s');
                            $project_task_bean->date_start = date("Y-m-d H:i:s");
                        } else {
                            $from = substr($_REQUEST['task_date_start_' . $i], 0, 1);
                            $what = substr($_REQUEST['task_date_start_' . $i], 1, 1);
                            $much = substr($_REQUEST['task_date_start_' . $i], 2, 5);

                            if ($from == 'S') {
                                list($d, $m, $y) = explode('/', $_REQUEST['estimated_start_date']);
                            } else {
                                list($d, $m, $y) = explode('/', $_REQUEST['estimated_end_date']);
                            }

                            $mdate = date_create($y . '-' . $m . '-' . $d);
                            if ($much == 1) {
                                $mdate->modify($what . $much . " day");
                            } else {
                                $mdate->modify($what . $much . " days");
                            }
                            $project_task_bean->date_due = date_format($mdate, 'Y-m-d H:i:s');
                            $project_task_bean->date_start = date("Y-m-d H:i:s");
                        }
                        $GLOBALS['log']->error('Daty: '.$project_task_bean->date_due.' '.$project_task_bean->date_start);
                        // dopisz PM QS SV jeśli jest
                        if ($_REQUEST['assigned_user_name_' . $i] == 'QS') {
                            $project_task_bean->assigned_user_id = $_REQUEST['user_id1_c'];
                        } elseif ($_REQUEST['assigned_user_name_' . $i] == 'PM') {
                            $project_task_bean->assigned_user_id = $_REQUEST['user_id_c'];
                        } elseif ($_REQUEST['assigned_user_name_' . $i] == 'SV') {
                            $project_task_bean->assigned_user_id = $_REQUEST['user_id2_c'];
                        } else {
                            if (isset($_REQUEST['task_team_' . $i])) {
                                $project_task_bean->assigned_user_id = $_REQUEST['user_id_c'];
                            } else {
                                $project_task_bean->assigned_user_id = $_REQUEST['assigned_user_id_' . $i];
                            }
                        }

                        $project_task_bean->status = 'Not Started';
                        $project_task_bean->parent_id = $bean->id;
                        $project_task_bean->parent_name = $bean->name;
                        $project_task_bean->parent_type = 'Project';

                        // Check parent type
                        $project_task_bean->load_relationship('teams');
                        $assigned_teams = array();
                        // tablica  z teamami wz zależnośći od related to będzie się przydzielać

                            // Board dodawany do każdego by  je widzieć
                            $assigned_teams[] = '462fff89-1c17-981f-353b-57492384e7a2';

                        // id teamu przypisanego użytkownika
                        $assigned_user_team_id = $this->getUserTeam($project_task_bean->assigned_user_id);
                        $main_assigned_team = $assigned_user_team_id; // dafaultowo od przypisanego użytkownika

                        $assigned_teams[] = $assigned_user_team_id; // team użytkownika do którego nadajemy
                        // $assigned_teams[] = $this->getUserTeam($bean->user_id2_c);
                        $assigned_teams[] = $this->getUserTeam($bean->user_id_c);
                        $assigned_teams[] = $this->getUserTeam($bean->user_id1_c);
                        $assigned_teams[] = $this->getUserTeam($bean->user_id2_c);
                        // sylwia jaczewska
                        $assigned_teams[] = $this->getUserTeam('54935fe3-a85f-9b4c-75b5-5744826784b5');
                        // jakub jurkowski
                        $assigned_teams[] = $this->getUserTeam('5b809a64-e252-625f-7637-57448298e6d9');
                        // development
                        $assigned_teams[] = $this->getUserTeam('1dc90971-b5cd-7168-2808-574923fed94e');

                        $assigned_teams[] = $this->getTeamViaDepartment($bean->aa_departments_id_c);

                        $project_task_bean->team_id = $main_assigned_team;
                        $project_task_bean->teams->replace($assigned_teams);
                        $project_task_bean->save();

                        $project_task_bean = null;
                        unset($project_task_bean);
                    } // !empty($_REQUEST['task_name_' . $i]
                } // while via tasks
            } // if $_REQUEST['module'] == 'FA_invoices'
        } // if czy update czy też nowy rekord
    }

    // /* update fee status */
    // public function addToRelatedFields(&$bean, $event, $arguments)
    // {
    //     $GLOBALS['log']->error('dodanie relacji do pól - addToRelatedFields');
    // 	// print_r($_REQUEST);
    // 	// die();
    // 	// 500 error
    // 	// think latter what error is this

    // 	// if we save invoice and add agreement  // why???
    // 	if ($_REQUEST['module'] == 'FA_invoices') {

    // 	} else {

    // 		if ($_REQUEST['relate_to'] == 'fp_feesproposals_project') {

    //     // relate project to fee proposal
    //                 $fee = BeanFactory::getBean('fp_FeesProposals', $_REQUEST['relate_id']);
    //                 $fee->related_project_name_c = $_REQUEST['fp_feesproposals_project_name'];
    //                 $fee->related_project_id_c = $bean->id;
    //                 $fee->result = 'Win';
    //                 $fee->status = 'Close';
    //                 $fee->save();
    //                 $fee = null;
    //                 unset($fee);

    // 			//// move notes from fee to project
    // 			$query = "UPDATE notes, notes_cstm SET notes.parent_type = 'Project', notes_cstm.relatednotes_c='project', notes.parent_id='" . $bean->id . "' WHERE notes.id=notes_cstm.id_c AND notes.parent_id='" . $_REQUEST['relate_id'] . "' ";
    // 			$GLOBALS['db']->query($query);

    // 		} else {

    // 			$query = "UPDATE fp_feesproposals_cstm SET related_project_name_c = '" . $_REQUEST['fp_feesproposals_project_name'] . "', related_project_id_c = '" . $_REQUEST['relate_id'] . "' WHERE id_c='" . $_REQUEST['fp_feesproposals_projectfp_feesproposals_ida'] . "' ";
    // 			$GLOBALS['db']->query($query);

    // 		}
    // 		//$query = "UPDATE fp_feesproposals_cstm SET name = '".$_REQUEST['fp_feesproposals_project_name']."' WHERE id_c='".$_REQUEST['fp_feesproposals_projectfp_feesproposals_ida']."' ";
    // 		//$GLOBALS['db']->query($query);
    // 	}
    // }

    public function saveMiddleDates(&$bean, $event, $arguments)
    {
        $GLOBALS['log']->error('dodanie kamieni milowych jeżeli to nowy rekord - saveMiddleDates');

        if ($arguments['isUpdate'] == false) {
            $i = 1;
            $GLOBALS['log']->error('Przejście przez _REQUEST');
            while (isset($_REQUEST['middledatestart_' . $i])) {
                $GLOBALS['log']->error('Sprawdzenie czy jest odpowiednia data');
                if (strlen($_REQUEST['middledatestart_' . $i]) == 10) {
                    list($d, $m, $y) = explode('/', $_REQUEST['middledatestart_' . $i]);
                    $date_due = $y . '-' . $m . '-' . $d;
                    $description = $_REQUEST['description_' . $i];

                    $pTask = BeanFactory::newBean('ProjectTask');
                    $GLOBALS['log']->error($description);
                    $pTask->name = $description;
                    $pTask->date_due = $date_due;
                    $pTask->date_start = $date_due;
                    $pTask->priority = 'High';
                    $pTask->date_finish = $date_due;
                    $pTask->duration = 0;
                    $pTask->milestone_flag = 1;
                    $pTask->assigned_user_id = $bean->user_id2_c;
                    $pTask->utilization = 0;
                    $pTask->save();
                    $GLOBALS['log']->error($pTask->id);
                    unset($pTask);
                }

                $i++;
            }// while via middle dates
        } // end of if about is updated


    } // end of function

    public function setProjectTeams($bean, $event, $arguments)
    {
        $assigned_teams = array();
        $teamSetBean = new TeamSet();
        $bean->load_relationship('teams');
        $db = DBManagerFactory::getInstance();

        $GLOBALS['log']->error("ZMIANA TEAMÓW");

        if($arguments['isUpdate'] == 1) {
            if(!empty($arguments['dataChanges'])) {
                $update_teams = array();
                $old_related_teams = array();
                $project_teams = $teamSetBean->getTeams($bean->team_set_id);

                foreach ($project_teams as $team) {
                    $old_related_teams[] = $team->id;
                }

                foreach ($arguments['dataChanges'] as $data) {
                    $helper = $data['field_name'];

                    if(($helper == 'user_id_c') || ($helper == 'user_id1_c') || ($helper == 'user_id2_c')) {
                        $update_teams[] = $helper;
                        $assigned_teams['after'][] = $this->getUserTeam($data['after']);
                        $assigned_teams['before'][] = $this->getUserTeam($data['before']);

                        if(($helper == 'user_id_c') || ($helper == 'user_id1_c')) {
                            $user_bean_after = BeanFactory::getBean('Users', $data['after']);
                            $user_bean_before = BeanFactory::getBean('Users', $data['before']);

                            $assigned_teams['after'][] = $this->getTeamViaDepartment($user_bean_after->aa_departments_id_c);
                            $assigned_teams['before'][] = $this->getTeamViaDepartment($user_bean_before->aa_departments_id_c);
                        }
                    } elseif($helper == 'aa_departments_id_c') {
                        $update_teams[] = $helper;
                        $assigned_teams['after'][] = $this->getTeamViaDepartment($data['after']);
                        $assigned_teams['before'][] = $this->getTeamViaDepartment($data['before']);
                    } elseif($helper == 'team_set_id') {
                        $update_teams[] = $helper;
                        $new_teams = $teamSetBean->getTeams($data['after']);

                        foreach ($new_teams as $team) {
                            $assigned_teams['after'][] = $team->id;
                        }
                    }
                }

                if(!empty($update_teams)) {
                    $query = $db->query("SELECT `id` FROM `teams` WHERE `description` LIKE 'QS'");
                    $qs = $db->fetchByAssoc($query);
                    $field_watcher = array("user_id_c", "user_id1_c", "user_id2_c", "aa_departments_id_c");

                    $assigned_teams['after'][] = $this->getUserTeam("801c0c78-edc1-e54f-08c2-5407f786ce48"); // add Prokocki private team
                    $assigned_teams['after'][] = $this->getUserTeam("94a70af3-d407-b5de-d1f2-568b80f84d3b"); // add Lesko private team
                    $assigned_teams['after'][] = $this->getUserTeam("85ac3697-84bc-9400-07f9-5770d2e0c12e"); // add Lukasik private team
                    $assigned_teams['after'][] = $this->getUserTeam("d8c6bac7-eb79-5cc3-6580-5770ce45b719"); // add Gryzewski private team
                    $assigned_teams['after'] = array_diff($assigned_teams['after'], $assigned_teams['before']);
                    $teams_without_before = array_diff($old_related_teams, $assigned_teams['before']);
                    $assigned_teams = array_merge($assigned_teams['after'], $teams_without_before);

                    // $GLOBALS['log']->error("ALL TEAMS:");
                    // $GLOBALS['log']->error($assigned_teams);

                    if (($key = array_search($qs['id'], $assigned_teams)) !== false) {
                        unset($assigned_teams[$key]);
                    }

                    foreach ($assigned_teams as $team) {
                        $query = $db->query("SELECT `associated_user_id` FROM `teams` WHERE `id`='{$team}'");
                        $user_id = $db->fetchByAssoc($query);

                        if(!empty($user_id['associated_user_id'])) {
                            $user_bean = BeanFactory::getBean('Users', $user_id['associated_user_id']);

                            if($user_bean->status == "Inactive" || $user_bean->employee_status == "Terminated") {
                                if (($key = array_search($team, $assigned_teams)) !== false) {
                                    unset($assigned_teams[$key]);
                                }
                            }
                        }
                    }

                    $bean->team_id = "462fff89-1c17-981f-353b-57492384e7a2"; // add board team
                    $bean->teams->replace($assigned_teams);
                }
            }
        } else {
            $assigned_teams[] = $this->getUserTeam($bean->user_id_c); // add PM team
            $assigned_teams[] = $this->getUserTeam($bean->user_id1_c); // add QS team
            $assigned_teams[] = $this->getTeamViaDepartment($bean->aa_departments_id_c); // add department team
            $assigned_teams[] = $this->getUserTeam("801c0c78-edc1-e54f-08c2-5407f786ce48"); // add Prokocki private team
            $assigned_teams[] = $this->getUserTeam("94a70af3-d407-b5de-d1f2-568b80f84d3b"); // add Lesko private team
            $assigned_teams[] = $this->getUserTeam("85ac3697-84bc-9400-07f9-5770d2e0c12e"); // add Lukasik private team
            $assigned_teams[] = $this->getUserTeam("d8c6bac7-eb79-5cc3-6580-5770ce45b719"); // add Gryzewski private team

            if($bean->user_id2_c != "144c39bf-ccc3-65ec-2023-5407f7975b91" && $bean->user_id2_c != "e07026a9-691a-67e7-32a6-5407f619ae5b") {
                $sv_bean = BeanFactory::getBean('Users', $bean->user_id2_c);
                $sv_department = $sv_bean->aa_departments_id_c;

                $assigned_teams[] = $this->getUserTeam($bean->user_id2_c); // add SV team
                $assigned_teams[] = $this->getTeamViaDepartment($sv_department); // add sv department team
            }

            $qs_bean = BeanFactory::getBean('Users', $bean->user_id1_c);
            $qs_department = $qs_bean->aa_departments_id_c;
            // różne od QS department
            if($qs_department != "15889a0a-9c90-0f6d-1962-5732e96debb0") {
                $assigned_teams[] = $this->getTeamViaDepartment($qs_department); // add qs department team
            }

            $assigned_teams[] = $this->getTeamViaDepartment("8b636633-dae5-6af9-15ae-56a5b82126b7"); // add fa department team
            $GLOBALS['log']->fatal("DODAWANIE ZESPOŁOW DO PROJEKTU");
            $GLOBALS['log']->fatal(print_r($assigned_teams, true));
            $bean->team_id = "462fff89-1c17-981f-353b-57492384e7a2"; // add board team
            $bean->teams->replace($assigned_teams);

            $this->createPCL($arguments['isUpdate'] == 1, $assigned_teams, $bean);
        }
    }

    private function createPCL($update, $project_teams, $bean)
    {
        $pcl;
        $db = DBManagerFactory::getInstance();

        if($update) {
            $pcl_row = $db->fetchByAssoc($db->query("SELECT `cases_project_1cases_ida` FROM `cases_project_1_c` WHERE `cases_project_1project_idb`='{$bean->id}'"));
            
            $pcl = BeanFactory::getBean("Cases", $pcl_row['cases_project_1cases_ida']);
        } else {
            $pcl = BeanFactory::newBean("Cases");
            $pcl->new_with_id = true;
            $pcl->id = create_guid();
            $pcl->name = $bean->name;
            $pcl->assigned_user_id = "144c39bf-ccc3-65ec-2023-5407f7975b91";
            $pcl->save();

            $db->query("INSERT INTO `cases_project_1_c` VALUES('".create_guid()."', CURRENT_TIMESTAMP, 0, '".$pcl->id."', '".$bean->id."')");
        }

        $pcl->load_relationship('teams');
        $pcl->team_id = "462fff89-1c17-981f-353b-57492384e7a2"; // board team
        $pcl->teams->replace($project_teams);
    }

    public function colorFee($bean, $event, $arguments)
    {
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE `ac_feeproposal` SET `sales_stage` = 'Closed Won', `date_modified` = CURDATE(), `modified_user_id` = '{$GLOBALS['current_user']->id}' WHERE `name` LIKE '{$bean->name}'");
    }

    public function addRelationshipsWithUsers(&$bean, $event, $arguments)
    {
        $i = 0;
        $relationships = array("user_id1_c" => "users_project_1", "user_id_c" => "users_project_2", "user_id2_c" => "users_project_3");

        foreach ($relationships as $key => $relation) {
            $i++;
            if($bean->load_relationship($relation)) {
                $bean->{"users_project_".$i."project_idb"} = $bean->id;
                $bean->{$relation}->add($bean->{$key});
            }
        }
    }

    public function putProjectFiles(&$bean, $event, $arguments)
    {
        if($arguments['isUpdate'] == 0) {
            if($bean->add_folders_c == true) {
                $con = ssh2_connect('89.250.193.178', 2222);
                $call = ssh2_auth_password($con , 'root', 'D0ntF0rg3tEv3r');

                $cmd = array();
                $cmd[] = "cd /volume1/02\ PROJECTS/";
                $cmd[] = "mkdir '". $bean->name ."'";
                $cmd[] = "chmod 777 '". $bean->name ."'";
                $cmd[] = "cd '". $bean->name ."'";
                $cmd[] = "cp -r /volume1/09\ TEMPLATES/P00\ PROJECT\ FOLDER\ TEMPLATE/* ./";
                $cmd[] = "chmod -R 777 ./";
                $commands = implode(';', $cmd);

                $stream = ssh2_exec($con, $commands);
                stream_set_blocking($stream, true);

                $db = DBManagerFactory::getInstance();
                $db->query("UPDATE `project_cstm` SET `add_folders_c` = 0 WHERE `id_c` = '{$bean->id}'");
            }
        } else {

        }
    }

    public function setArchivingDate(&$bean, $event, $arguments)
    {
        if($arguments == true) {
            if(!empty($arguments['dataChanges']['archival_c'])) {
                if(0 == $arguments['dataChanges']['archival_c']['before'] && 1 == $arguments['dataChanges']['archival_c']['after']) {
                    $db = DBManagerFactory::getInstance();
                    $db->query("UPDATE `project_cstm` SET `archival_date_c` = DATE($bean->date_modified) WHERE `id_c` = '{$bean->id}'");
                }
            }
        }
    }
} // end of class

/****


        // if we save invoice and add agreement
        if ($_REQUEST['module'] == 'FA_invoices') {
            $GLOBALS['log']->fatal('to się nie powinno robić');
        } else {

            $bean->load_relationship('accounts');
            $bean->accounts->add($_REQUEST['account_id_c']);
            $bean->accounts->add($_REQUEST['account_id1_c']);

            if (empty($bean->fetched_row['id'])) {
                $nnn = explode(' ', $bean->name);
                $bean->project_number_c = $nnn[0];
            }
            print_r($bean);
        echo '<br /><br />';
        $bean->load_relationship('bdg_buildings');
        print_r($bean);
        die();
        $bean->bdg_buildings->add($_REQUEST['bdg_buildings_project_1bdg_buildings_ida']);
        }
    }
    *****/


/*
 * może się kiedyś przyda jak nie to do dasha i usunąć
      //  $fp_bean = BeanFactory::getBean('AC_FeeProposal', $_GET['feeid']);
        // foreach ($relatedBeans as $key => $value) {
        //     echo $key.'<br />';
        //     print_r($relatedBeans);
        //     $fee->retrieve($key);

        //     if ($fee->load_relationship('Notes')) {
        //         $relatedBeans2 = $fee->notes->getBeans();
        //         $bean->project_notes_1_c->replace($relatedBeans2);
        //     }
        // }
        */
?>
