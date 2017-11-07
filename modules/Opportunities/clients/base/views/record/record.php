<?php
$viewdefs['Opportunities'] = 
array (
  'base' => 
  array (
    'view' => 
    array (
      'record' => 
      array (
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
                'primary' => true,
                'acl_action' => 'edit',
              ),
              1 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:convert_fee:click',
                'name' => 'convert_fee',
                'label' => 'Convert to Fee Proposal',
              ),
              2 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:audit_button:click',
                'name' => 'audit_button',
                'label' => 'LNK_VIEW_CHANGE_LOG',
                'acl_action' => 'view',
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
        ),
        'panels' => 
        array (
          0 => 
          array (
            'name' => 'panel_header',
            'header' => true,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'size' => 'large',
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
            'columns' => 3,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'pattern_c',
                'label' => 'LBL_PATTERN',
                'readonly' => true,
                'span' => 6,
              ),
              1 => 
              array (
                'name' => 'custom_c',
                'label' => 'LBL_CUSTOM',
                'span' => 6,
              ),
              2 => 
              array (
                'name' => 'framework_c',
                'label' => 'LBL_FRAMEWORK',
                'span' => 2,
              ),
              3 => 
              array (
                'name' => 'canceled_c',
                'label' => 'LBL_CANCELED',
                'span' => 4,
              ),
              4 => 
              array (
                'name' => 'delegated_c',
                'studio' => 'visible',
                'label' => 'LBL_DELEGATED',
                'link' => false,
                'span' => 6,
              ),
              5 => 
              array (
                'name' => 'opportunities_aa_buildings_1_name',
                'label' => 'LBL_OPPORTUNITIES_AA_BUILDINGS_1_FROM_AA_BUILDINGS_TITLE',
                'span' => 6,
              ),
              6 => 
              array (
                'name' => 'floors_c',
                'label' => 'LBL_FLOORS',
                'span' => 6,
              ),
              7 => 
              array (
                'name' => 'opportunities_accounts_1_name',
                'span' => 6,
              ),
              8 => 
              array (
                'name' => 'service_c',
                'label' => 'LBL_SERVICE',
                'span' => 6,
              ),
              9 => 
              array (
                'name' => 'account_name',
                'related_fields' => 
                array (
                  0 => 'account_id',
                ),
                'span' => 6,
              ),
              10 => 
              array (
                'name' => 'leads_opportunities_1_name',
                'span' => 6,
              ),
              11 => 
              array (
                'name' => 'description',
                'span' => 6,
              ),
              12 => 
              array (
                'name' => 'probability',
                'span' => 6,
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
