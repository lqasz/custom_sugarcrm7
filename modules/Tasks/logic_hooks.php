<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 

// custom logic for
$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1,'everyTime','custom/modules/Tasks/customlogic.php','Tasks_CustomLogic','everyTime',); // repeated tasks
$hook_array['after_save'][] = Array(2,'manageTasks','custom/modules/Tasks/customlogic.php','Tasks_CustomLogic','manageTasks',); // setting teams to task

?>