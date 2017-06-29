<?php

$viewdefs['base']['view']['tasks-today'] = array(
    'dashlets' => array(
        array(
            'label' => 'Tasks - Today',
            'description' => 'RMS Tasks',
            'config' => array(
                'limit' => 50,
                'visibility' => 'user',
            ),
            'preview' => array(
                'limit' => 50,
                'visibility' => 'user',
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                    'Bugs',
                    'Cases',
                    'Contacts',
                    'Home',
                    'Leads',
                    'Opportunities',
                    'Prospects',
                    'RevenueLineItems',
                ),
                'view' => 'record',
            ),
        ),
    ),
    'custom_toolbar' => array( 
        'buttons' => array(
            array(
                "type" => "dashletaction",
                "css_class" => "dashlet-toggle btn btn-invisible minify",
                "icon" => "fa-chevron-up",
                "action" => "toggleMinify",
                "tooltip" => "LBL_DASHLET_TOGGLE",
            ),
            array(
                'dropdown_buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'editClicked',
                        'label' => 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'refreshClicked',
                        'label' => 'LBL_DASHLET_REFRESH_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'toggleClicked',
                        'label' => 'LBL_DASHLET_MINIMIZE',
                        'event' => 'minimize',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'removeClicked',
                        'label' => 'LBL_DASHLET_REMOVE_LABEL',
                    ),
                ),
            ),
        ),
    ),
    'tabs' => array(
        array(
            'active' => true,
            'filters' => array(
                'date_due' => array(
                    '$dateRange' => 'today',
                ),
                'status' => array(
                    '$in' => array("Not Started","In Progress"),
                ),
                'new_one_c' => array(
                    '$equals' => false
                ),
            ),

            'label' => 'LBL_ACTIVE_TASKS_DASHLET_DUE_NOW',
            'link' => 'tasks',
            'module' => 'Tasks',
            'order_by' => 'date_due:asc',
            'record_date' => 'date_due',
            'row_actions' => array(
                array(
                    'type' => 'rowaction',
                    'css_class' => 'btn btn-mini',
                    'event' => 'tasks-actions:move-task:fire',
                    'target' => 'view',
                    'caption' => '+1 Day',
                    'tooltip' => 'LBL_ACTIVE_TASKS_DASHLET_MOVE_TASK',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'css_class' => 'btn btn-mini',
                    'event' => 'tasks-actions:close-task:fire',
                    'target' => 'view',
                    'caption' => 'Close',
                    'tooltip' => 'LBL_ACTIVE_TASKS_DASHLET_COMPLETE_TASK',
                    'acl_action' => 'edit',
                ),
            ),
            'overdue_badge' => array(
                'name' => 'date_due',
                'type' => 'overdue-badge',
                'css_class' => 'pull-right',
            ),
            'fields' => array(
                'name',
                'assigned_user_name',
                'assigned_user_id',
                'created_by',
                'created_by_name',
                'status',
                'parent_type',
                'parent_name',
                'parent_id',
                'priority',
                'new_one_c',
                'date_due',
            ),
        ),
    ),
    'visibility_labels' => array(
        'user' => 'LBL_ACTIVE_TASKS_DASHLET_USER_BUTTON_LABEL',
        'group' => 'LBL_ACTIVE_TASKS_DASHLET_GROUP_BUTTON_LABEL',
    ),
);
