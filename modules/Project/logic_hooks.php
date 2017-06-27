<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will
// be automatically rebuilt in the future.
$hook_version = 1;
$hook_array = array();

$hook_array['before_save'] = array();
$hook_array['before_save'][] = array(
                                    1,
                                    'setRelationship',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'setRelationship'
                                );

$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(
                                    1,
                                    'copyAttachments',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'copyAttachments'
                                );
$hook_array['after_save'][] = array(
                                    2,
                                    'saveMiddleDates',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'saveMiddleDates'
                                );
$hook_array['after_save'][] = array(
                                    3,
                                    'createTasks',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'createTasks'
                                );
$hook_array['after_save'][] = array(
                                    4,
                                    'trackPeopleChanges',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'trackPeopleChanges'
                                );
$hook_array['after_save'][] = array(
                                    5,
                                    'setProjectTeams',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'setProjectTeams'
                                );
$hook_array['after_save'][] = array(
                                    6,
                                    'colorFee',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'colorFee'
                                );
$hook_array['after_save'][] = array(
                                    7,
                                    'addRelationshipsWithUsers',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'addRelationshipsWithUsers'
                                );
$hook_array['after_save'][] = array(
                                    8,
                                    'putProjectFiles',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'putProjectFiles'
                                );
$hook_array['after_save'][] = array(
                                    9,
                                    'setArchivingDate',
                                    'custom/modules/Project/customlogic.php',
                                    'Project_Customlogic',
                                    'setArchivingDate'
                                );