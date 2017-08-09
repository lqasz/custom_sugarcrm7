<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class AC_FeeProposal_Customlogic {

    public function addNumber($bean, $event, $arguments)
    {

        if ($arguments['isUpdate'] == 0 && empty($bean->fees_porposal_number_c)) {
            $db = DBManagerFactory::getInstance();

            $res = $db->query('SELECT * FROM ac_feeproposal_cstm ORDER BY fees_porposal_number_c DESC LIMIT 1');
            $res2 = $db->fetchByAssoc($res);

            if (intval($res2['fees_porposal_number_c'])>= 170001) {
                $nnn = intval(substr($res2['fees_porposal_number_c'], 2, 10));
                $nnn = str_pad($nnn+1, 4, 0, STR_PAD_LEFT);

                $akt_data = new DateTime('NOW');
                $bean->fees_porposal_number_c = $akt_data->format('y').$nnn;
            } else {
                $bean->fees_porposal_number_c = '170001';
            }

        } // if is updated
    }

    public function addFeesName(&$bean, $event, $arguments)
    {
        $fee_name;
        $update = false;
        $old_name = $bean->name;
        $db = DBManagerFactory::getInstance();

        if($arguments['isUpdate'] == 1) {
            $update = true;
        }

        $fee_name = $this->createFeesName($bean->floors_c, $bean->ac_feeproposal_aa_buildings_1, $bean->fees_porposal_number_c, strtoupper($bean->custom_c), $bean->service_c);

        $query = 'UPDATE ac_feeproposal fp SET fp.name = "'. $fee_name .'" WHERE fp.id="'.$bean->id.'" AND fp.deleted=0';
        $db->query($query);

        $this->copyFolders($fee_name, $old_name, $update);
    }

    public function createFeesName($bean_floors, $bean_fee_building, $bean_fee_number, $bean_project_name, $bean_service)
    {
        $db = DBManagerFactory::getInstance();

        $floors = '';
        $fee_name = "";
        if($bean_floors != '') {
            $clean_string = str_replace('^',"", $bean_floors);
            $floors .= $clean_string ."p";
            if(strpos($clean_string, ",")) {
                $cleaner_string = str_replace(',',"_", $clean_string);
                $floors = $cleaner_string ."p";
            }
        }

        $fee_building = key($bean_fee_building->getBeans());
        if(strlen($floors) > 0) {
            $res = $db->query('SELECT * FROM aa_buildings WHERE id="'.$fee_building.'" ');
            $budynek = $db->fetchByAssoc($res);
            $fee_name = $bean_fee_number.' B'.$budynek['building_number'].' '.$floors.' '.$bean_project_name.' '.$bean_service;
        } elseif(empty($fee_building)) {
            $fee_name = $bean_fee_number.' '.$bean_project_name.' '.$bean_service;
        } else {
            $res = $db->query('SELECT * FROM aa_buildings WHERE id="'.$fee_building.'" ');
            $budynek = $db->fetchByAssoc($res);
            $fee_name = $bean_fee_number.' B'.$budynek['building_number'].' '.$bean_project_name.' '.$bean_service;
        }

        return $fee_name;
    }

    public function copyFolders($fee_name, $old_name, $update)
    {
        $con = ssh2_connect('89.250.193.178', 2222);
        $call = ssh2_auth_password($con , 'root', 'D0ntF0rg3tEv3r');

        $cmd = array();
        $cmd[] = "cd /volume1/01\ FEE\ PROPOSALS/";
        if($update == true) {
            $cmd[] = "mv ./'". $old_name ."' ./'". $fee_name ."'";
        } else {
            $cmd[] = "mkdir '". $fee_name ."'";
            $cmd[] = "chmod 777 '". $fee_name ."'";
            $cmd[] = "cd '". $fee_name ."'";
            $cmd[] = "cp -r /volume1/09\ TEMPLATES/F00\ FEE\ PROPOSAL\ FOLDER\ TEMPLATE/* ./";
            $cmd[] = "chmod -R 777 ./";
        }

        $GLOBALS['log']->fatal(print_r($cmd, true));
        $commands = implode(';', $cmd);

        $stream = ssh2_exec($con, $commands);
        stream_set_blocking($stream, true);
    }

    public function cancelOpportunity(&$bean, $event, $arguments)
    {
        if($arguments['isUpdate'] != 1) {
            $db = DBManagerFactory::getInstance();
            $rel_opp_result = $db->query("SELECT `opportunities_ac_feeproposal_1opportunities_ida` FROM `opportunities_ac_feeproposal_1_c` WHERE `opportunities_ac_feeproposal_1ac_feeproposal_idb`='{$bean->id}'");

            $rel_opp = $db->fetchByAssoc($rel_opp_result);
            $opportunity_bean = BeanFactory::getBean('Opportunities', $rel_opp['opportunities_ac_feeproposal_1opportunities_ida']);

            if($opportunity_bean->framework_c != 1 && $opportunity_bean->archive_c != 1) {
                // $opportunity_bean->deleted = 1;
                $opportunity_bean->archive_c = 1;
                $opportunity_bean->save();
            }
        }
    }
}
?>
