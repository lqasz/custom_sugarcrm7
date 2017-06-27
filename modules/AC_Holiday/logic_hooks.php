<?php

$hook_version = 1; 
$hook_array = Array(); 

$hook_array['after_save'] = Array(); 
$hook_array['after_delete'] = Array(); 
$hook_array['after_save'][] = Array(1, 'manage notifications', 'custom/modules/AC_Holiday/customlogic.php','AC_Holiday_Customlogic', 'manageNotifications');
$hook_array['after_delete'][] = Array(2, 'delete notification', 'custom/modules/AC_Holiday/customlogic.php', 'AC_Holiday_Customlogic', 'deleteNotification');
?>