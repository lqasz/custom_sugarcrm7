 <?php
	$hook_version = 1;
	$hook_array = array ();
	$hook_array['before_save'] = array ();
	$hook_array['before_save'][] = array (
		1,
		'Create Sugar Notification on any new post or comment',
		'custom/modules/ActivityStream/Activities/customlogic.php',
		'SugarNotifyUser',
		'notifyUserOnPostComment'
	);
?>