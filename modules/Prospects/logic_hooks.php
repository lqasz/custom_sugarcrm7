<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 
// position, file, function 

$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1,'createFirstCall','custom/modules/Prospects/customlogic.php','Prospects_CustomLogic','createFirstCall',);
$hook_array['after_save'][] = Array(2,'createPerson','custom/modules/Prospects/customlogic.php','Prospects_CustomLogic','createPerson',);
?>