<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Opportunities_Customlogic 
{
    function create_name($bean, $event, $arguments) 
    {
        $db = DBManagerFactory::getInstance();
        $opp_name = $this->createOppName($bean);
        $query = 'UPDATE opportunities fp SET fp.name = "'.$opp_name.'" WHERE fp.id="'.$bean->id.'" AND fp.deleted=0';
        $db->query($query);
    }

    function createOppName($bean)
    {
        $db = DBManagerFactory::getInstance();

        $floors = '';
        $opp_name = "";
        if($bean->floors_c != '') {
            $clean_string = str_replace('^',"", $bean->floors_c);
            $floors .= $clean_string ."p";
            if(strpos($clean_string, ",")) {
                $cleaner_string = str_replace(',',"_", $clean_string);
                $floors = $cleaner_string ."p";
            }
        }

        $opp_building = $bean->opportunities_aa_buildings_1aa_buildings_idb;
        if(strlen($floors) > 0) {
            $res = $db->query('SELECT * FROM aa_buildings WHERE id="'.$opp_building.'" ');
            $budynek = $db->fetchByAssoc($res);
            $opp_name = 'B'.$budynek['building_number'].' '.$floors.' '.$bean->custom_c.' '.$bean->service_c;
        } elseif(empty($opp_building)) {
            $opp_name = $bean->custom_c.' '.$bean->service_c;
        } else {
            $res = $db->query('SELECT * FROM aa_buildings WHERE id="'.$opp_building.'" ');
            $budynek = $db->fetchByAssoc($res);
            $opp_name = 'B'.$budynek['building_number'].' '.$bean->custom_c.' '.$bean->service_c;
        }

        return $opp_name;
    }
}

?>