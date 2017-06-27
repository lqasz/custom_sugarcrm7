<?php
$module_name = 'AC_FeeProposal';
$_module_name = 'ac_feeproposal';
$viewdefs[$module_name] = 
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
                'type' => 'pdfaction',
                'name' => 'download-pdf',
                'label' => 'LBL_PDF_VIEW',
                'action' => 'download',
                'acl_action' => 'view',
              ),
              2 => 
              array (
                'type' => 'pdfaction',
                'name' => 'email-pdf',
                'label' => 'LBL_PDF_EMAIL',
                'action' => 'email',
                'acl_action' => 'view',
              ),
              3 => 
              array (
                'type' => 'divider',
              ),
              4 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:delete_button:click',
                'name' => 'delete_button',
                'label' => 'LBL_DELETE_BUTTON_LABEL',
                'acl_action' => 'delete',
              ),
              5 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:audit_button:click',
                'name' => 'audit_button',
                'label' => 'LNK_VIEW_CHANGE_LOG',
                'acl_action' => 'view',
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
                'name' => 'pattern',
                'label' => 'LBL_PATTERN',
              ),
              1 => 
              array (
                'name' => 'custom_c',
                'label' => 'LBL_CUSTOM',
              ),
              2 => 
              array (
                'name' => 'ac_feeproposal_aa_buildings_1_name',
              ),
              3 => 
              array (
                'name' => 'cam',
                'studio' => 'visible',
                'label' => 'LBL_CAM',
                'link' => false,
              ),
              4 => 
              array (
                'name' => 'floors_c',
                'label' => 'LBL_FLOORS',
              ),
              5 => 
              array (
                'name' => 'responsible',
                'studio' => 'visible',
                'label' => 'LBL_RESPONSIBLE',
                'link' => false,
              ),
              6 => 
              array (
                'name' => 'tenant_c',
                'studio' => 'visible',
                'label' => 'LBL_TENANT',
              ),
              7 => 
              array (
                'name' => 'supervisor',
                'studio' => 'visible',
                'label' => 'LBL_SUPERVISOR',
                'link' => false,
              ),
              8 => 
              array (
                'name' => 'accounts_ac_feeproposal_1_name',
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
                'span' => 6,
              ),
              11 => 
              array (
                'name' => 'probability',
                'span' => 6,
              ),
              12 => 
              array (
                'name' => 'sales_stage',
              ),
              13 => 
              array (
                'name' => 'amount',
                'type' => 'currency',
                'label' => 'LBL_LIKELY',
                'related_fields' => 
                array (
                  0 => 'amount',
                  1 => 'currency_id',
                  2 => 'base_rate',
                ),
                'currency_field' => 'currency_id',
                'base_rate_field' => 'base_rate',
              ),
              14 => 
              array (
                'name' => 'date_of_implementation_c',
                'placeholder' => 'Kwartał/Data',
                'label' => 'LBL_DATE_OF_IMPLEMENTATION',
              ),
              15 => 
              array (
                'name' => 'area_c',
                'label' => 'LBL_AREA',
              ),
              16 => 
              array (
                'name' => 'description',
                'placeholder' => 'Szczegółowy opis oferty',
                'span' => 6,
              ),
              17 => 
              array (
                'name' => 'leads_ac_feeproposal_1_name',
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
