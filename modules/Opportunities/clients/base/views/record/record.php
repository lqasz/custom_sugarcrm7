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
                'related_fields' => 
                array (
                  0 => 'total_revenue_line_items',
                  1 => 'closed_revenue_line_items',
                ),
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
            'columns' => 2,
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
              ),
              1 => 
              array (
                'name' => 'custom_c',
                'label' => 'LBL_CUSTOM',
              ),
              2 => 
              array (
                'name' => 'aa_buildings_opportunities_1_name',
                'label' => 'LBL_AA_BUILDINGS_OPPORTUNITIES_1_FROM_AA_BUILDINGS_TITLE',
              ),
              3 => 
              array (
                'name' => 'responsible_c',
                'studio' => 'visible',
                'label' => 'LBL_RESPONSIBLE',
                'link' => false,
              ),
              4 => 
              array (
                'name' => 'floor3_c',
                'label' => 'LBL_FLOOR3',
              ),
              5 => 
              array (
                'name' => 'delegated_c',
                'studio' => 'visible',
                'label' => 'LBL_DELEGATED',
                'link' => false,
              ),
              6 => 
              array (
                'name' => 'tenant_c',
                'label' => 'LBL_TENANT',
              ),
              7 => 
              array (
                'name' => 'supervisor_c',
                'studio' => 'visible',
                'label' => 'LBL_SUPERVISOR',
                'link' => false,
              ),
              8 => 
              array (
                'name' => 'account_name',
                'related_fields' => 
                array (
                  0 => 'account_id',
                ),
              ),
              9 => 
              array (
                'name' => 'service_c',
                'label' => 'LBL_SERVICE',
              ),
              10 => 
              array (
                'name' => 'date_closed',
                'related_fields' => 
                array (
                  0 => 'date_closed_timestamp',
                ),
              ),
              11 => 'probability',
              12 => 
              array (
                'name' => 'sales_stage',
              ),
              13 => 
              array (
                'name' => 'leads_opportunities_1_name',
              ),
              14 => 
              array (
                'name' => 'description',
                'placeholder' => 'Szczegółowy opis szansy',
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
