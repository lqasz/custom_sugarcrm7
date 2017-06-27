<?php
    $viewdefs['Tasks']['base']['view']['recordlist'] = array(
        'favorite' => true,
        'following' => true,
        'selection' => array(
            'type' => 'multi',
            'actions' => array(
                array(
                    'name' => 'edit_button',
                    'type' => 'button',
                    'label' => 'LBL_MASS_UPDATE',
                    'primary' => true,
                    'events' => array( 
                        'click' => 'list:massupdate:fire',
                    ),
                    'acl_action' => 'massupdate',
                ),
                array(
                    'name' => 'delete_button',
                    'type' => 'button',
                    'label' => 'LBL_DELETE',
                    'acl_action' => 'delete',
                    'primary' => true,
                    'events' => array(
                        'click' => 'list:massdelete:fire',
                    ),
                ),
            ),
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
                array(
                    'type' => 'rowaction',
                    'name' => 'edit_button',
                    'icon' => 'fa-pencil',
                    'label' => 'LBL_EDIT_BUTTON',
                    'event' => 'list:editrow:fire',
                    'acl_action' => 'edit',
                ),
                3 => 
                array (
                  'type' => 'rowaction',
                  'name' => 'record-move',
                  'label' => '+1 Day',
                  'event' => 'list:movetask:fire',
                  'acl_action' => 'edit',
                ),
                4 => 
                array (
                  'type' => 'rowaction',
                  'name' => 'record-close',
                  'label' => 'LBL_CLOSE_BUTTON_TITLE',
                  'event' => 'list:closetask:fire',
                  'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'fa-trash-o',
                    'event' => 'list:deleterow:fire',
                    'label' => 'LBL_DELETE_BUTTON',
                    'acl_action' => 'delete',
                ),  
            )
        )
    );
