<?php

$hook_version = 1; 
$hook_array = Array();

$hook_array['before_save'] = Array();
$hook_array['before_save'][] = Array(1, 'addNumber', 'custom/modules/AC_FeeProposal/customlogic.php','AC_FeeProposal_Customlogic', 'addNumber');


$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'addFeesName', 'custom/modules/AC_FeeProposal/customlogic.php','AC_FeeProposal_Customlogic', 'addFeesName');

?>