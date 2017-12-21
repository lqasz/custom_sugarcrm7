<?php
$module_name = 'AC_Holiday';
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
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'v_from',
                'label' => 'LBL_V_FROM',
              ),
              1 => 
              array (
                'name' => 'sick_leave',
                'label' => 'LBL_SICK_LEAVE',
              ),
              2 => 
              array (
                'name' => 'v_to',
                'label' => 'LBL_V_TO',
              ),
              3 => 
              array (
                'name' => 'days_hd',
                'label' => 'LBL_DAYS_HD',
                'readonly' => true,
              ),
              4 => 
              array (
                'name' => 'supervisor',
                'label' => 'LBL_SUPERVISOR',
              ),
              5 => 
              array (
                'name' => 'board',
                'label' => 'LBL_BOARD',
              ),
              6 => 
              array (
                'name' => 'rejected_c',
                'label' => 'LBL_REJECTED_C',
              ),
              7 => 
              array (
                'name' => 'fa_c',
                'label' => 'LBL_FA_C',
              ),
              8 => 
              array (
                'name' => 'description',
                'span' => 12,
                'readonly' => true,
              ),
              9 => 
              array (
                'name' => 'assigned_user_name',
                'link' => false,
              ),
              10 => 
              array (
                'name' => 'withdrawal_of_leave_c',
                'label' => 'LBL_WITHDRAWAL_OF_LEAVE_C',
              ),
              11 => 
              array (
                'name' => 'team_name',
                'class' => 'hide',
              ),
              12 => 
              array (
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => false,
        ),
      ),
    ),
  ),
);

$viewdefs[$module_name]['base']['view']['record']['buttons'] =
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
      1 =>
      array (
        'type' => 'rowaction',
        'event' => 'button:audit_button:click',
        'name' => 'audit_button',
        'label' => 'LNK_VIEW_CHANGE_LOG',
        'acl_action' => 'view',
      ),
      2 =>
      array (
          'type' => 'divider',
      ),
      3 =>
      array (
          'type' => 'rowaction',
          'event' => 'button:delete_button:click',
          'name' => 'delete_button',
          'label' => 'LBL_DELETE_BUTTON_LABEL',
          'acl_action' => 'delete',
      ),
    ),
  ),
  3 =>
  array (
    'name' => 'sidebar_toggle',
    'type' => 'sidebartoggle',
  ),
);