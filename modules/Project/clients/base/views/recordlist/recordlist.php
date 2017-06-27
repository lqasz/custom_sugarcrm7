<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

$module_name = 'Project';
$_module_name = 'ac_feeproposal';


$viewdefs[$module_name]['base']['view']['recordlist'] = array(
    'favorite' => false,
    'following' => false,
    'sticky_resizable_columns' => true,
    'selection' => array(
    ),
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'fa-eye',
                'acl_action' => 'view',
            ),
            // array(
            //     'type' => 'rowaction',
            //     'name' => 'edit_button',
            //     'label' => 'LBL_EDIT_BUTTON',
            //     'event' => 'list:editrow:fire',
            //     'acl_action' => 'edit',
            // ),
            // array(
            //     'type' => 'rowaction',
            //     'name' => 'delete_button',
            //     'event' => 'list:deleterow:fire',
            //     'label' => 'LBL_DELETE_BUTTON',
            //     'acl_action' => 'delete',
            // ),

        ),
    ),
    'last_state' => array(
        'id' => 'record-list',
    ),
);
