<?php
$module_name = 'AB_TimeSheetQS';
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
              1 => array (
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
              0 => array (
                'name' => 'assigned_user_name',
                'span' => 3,
                'readonly' => true,
                'link' => false,
              ),
              1 => 
              array (
                'name' => 'accepted_by_tl_c',
                'label' => 'LBL_ACCEPTED_BY_TL',
                'span' => 3,
              ),
              2 => 
              array (
                'name' => 'rejected_by_tl_c',
                'label' => 'LBL_REJECTED_BY_TL',
                'span' => 3,
              ),
              3 => 
              array (
                'name' => 'absent_c',
                'label' => 'LBL_ABSENT',
                'span' => 3,
              ),
              4 => 
              array (
                'name' => 'subordinates_c',
                'span' => 12,
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
    'name' => 'accept_ts',
    'label' => 'Accept',
    'css_class' => 'btn btn-primary',
    'showOn' => 'view',
  ),
  1 =>
  array (
    'type' => 'button',
    'name' => 'cancel_button',
    'label' => 'LBL_CANCEL_BUTTON_LABEL',
    'css_class' => 'btn-invisible btn-link',
    'showOn' => 'edit',
  ),
  2 =>
  array (
    'type' => 'rowaction',
    'event' => 'button:save_button:click',
    'name' => 'save_button',
    'label' => 'LBL_SAVE_BUTTON_LABEL',
    'css_class' => 'btn btn-primary',
    'showOn' => 'edit',
    'acl_action' => 'edit',
  ),
  3 =>
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
    ),
  ),
  4 =>
  array (
    'name' => 'sidebar_toggle',
    'type' => 'sidebartoggle',
  ),
);