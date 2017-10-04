<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 
// position, file, function 

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1,'createPerson','custom/modules/Leads/customlogic.php','Leads_CustomLogic','createPerson',);
$hook_array['after_save'][] = Array(2,'businessLogic','custom/modules/Leads/customlogic.php','Leads_CustomLogic','businessLogic',);
?>