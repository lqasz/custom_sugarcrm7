<?php

$hook_version = 1; 
$hook_array = Array();

$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'add_number', 'custom/modules/Opportunities/customlogic.php','Opportunities_Customlogic', 'add_number');


$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'create_name', 'custom/modules/Opportunities/customlogic.php','Opportunities_Customlogic', 'create_name');
$hook_array['after_save'][] = Array(2, 'saveToFee', 'custom/modules/Opportunities/customlogic.php','Opportunities_Customlogic', 'saveToFee');



?>