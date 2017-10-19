<?php
$module_name = 'AB_TimeSheet';
$viewdefs[$module_name] = 
array (
  'base' => 
  array (
    'view' => 
    array (
      'record' => 
      array (
        'panels' => 
        array (
          0 => 
          array (
            'name' => 'panel_header',
            'label' => 'LBL_RECORD_HEADER',
            'header' => true,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'width' => 42,
                'height' => 42,
                'dismiss_label' => true,
                'readonly' => true,
                'link' => false,
              ),
              1 => 
              array (
                'name' => 'name',
                'readonly' => true,
              ),
              2 => 
              array (
                'name' => 'favorite',
                'label' => 'LBL_FAVORITE',
                'type' => 'favorite',
                'readonly' => true,
                'dismiss_label' => true,
              ),
              3 => 
              array (
                'name' => 'follow',
                'label' => 'LBL_FOLLOW',
                'type' => 'follow',
                'readonly' => true,
                'dismiss_label' => true,
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 4,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'assigned_user_name',
                'link' => false,
                'readonly' => true,
                'span' => 6,
              ),
              1 => 
              array (
                'name' => 'department_c',
                'link' => false,
                'readonly' => true,
                'span' => 6,
              ),
              2 => 
              array (
                'name' => 'sv_c',
                'readonly' => true,
                'span' => 3,
              ),
              3 => 
              array (
                'name' => 'rejected_c',
                'readonly' => true,
                'span' => 3,
              ),
              4 => 
              array (
                'name' => 'date_start_c',
                'readonly' => true,
                'span' => 3,
              ),
              5 => 
              array (
                'name' => 'date_end_c',
                'readonly' => true,
                'span' => 3,
              ),
              6 => 
              array (
                'name' => 'rejected_text_c',
                'label' => 'LBL_REJECTED_TEXT',
                'readonly' => true,
                'span' => 12,
              ),
              7 => 
              array (
                'name' => 'subordinates_c',
                'label' => 'LBL_SUBORDINATES',
                'span' => 12,
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => false,
        ),
        'buttons' => 
        array (
          0 => 
          array (
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
          ),
          1 => 
          array (
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
          ),
          2 => 
          array (
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => 
            array (
              0 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:edit_button:click',
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON_LABEL',
                'acl_action' => 'edit',
              ),
            ),
          ),
          3 => 
          array (
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
          ),
        ),
      ),
    ),
  ),
);
