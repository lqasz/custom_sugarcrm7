<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
            // array(
            //     'type' => 'dashletaction',
            //     "css_class" => "btn btn-invisible minify",
            //     "icon" => "fa-plus",
            //     'action' => 'createRecord',
            //     'params' => array(
            //         'module' => 'Tasks',
            //         'link' => 'tasks',
            //     ),
            //     "tooltip" => "LBL_CREATE_TASK",
            // ),
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
    // 'panels' => array(
    //     array(
    //         'name' => 'panel_body',
    //         'columns' => 2,
    //         'labelsOnTop' => true,
    //         'placeholders' => true,
    //         'fields' => array(
    //             array(
    //                 'name' => 'visibility',
    //                 'label' => 'LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY',
    //                 'type' => 'enum',
    //                 'options' => 'tasks_visibility_options',
    //             ),
    //             array(
    //                 'name' => 'limit',
    //                 'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
    //                 'type' => 'enum',
    //                 'options' => 'tasks_limit_options',
    //             ),
    //         ),
    //     ),
    // ),
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
                // array(
                //     'type' => 'unlink-action',
                //     'icon' => 'fa-chain-broken',
                //     'css_class' => 'btn btn-mini',
                //     'event' => 'tabbed-dashlet:unlink-record:fire',
                //     'target' => 'view',
                //     'tooltip' => 'LBL_UNLINK_BUTTON',
                //     'acl_action' => 'edit',
                // ),
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
