<?php

	$hook_array = Array(); 
	// position, file, function 
	$hook_array['after_save'] = Array(); 
	$hook_array['after_save'][] = Array(1, 'businessLogic','custom/modules/AB_TimeSheetQS/customlogic.php','AB_TimeSheetQS_CustomLogic','businessLogic');
?>