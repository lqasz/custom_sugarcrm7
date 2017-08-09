<?php

$hook_version = 1; 
$hook_array = Array();

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'create_name', 'custom/modules/Opportunities/customlogic.php','Opportunities_Customlogic', 'create_name');
?>