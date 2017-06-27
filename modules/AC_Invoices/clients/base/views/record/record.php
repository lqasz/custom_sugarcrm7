<?php
$module_name = 'AC_Invoices';
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
              1 => 'name',
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
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'scan_c',
                'studio' => 'visible',
                'label' => 'LBL_SCAN',
                'span' => 12,
              ),
              1 => 
              array (
                'name' => 'invoice_no_c',
                'label' => 'LBL_INVOICE_NO',
              ),
              2 => 
              array (
                'name' => 'supplier_c',
                'studio' => 'visible',
                'label' => 'LBL_SUPPLIER',
                'span' => 6,
              ),
              3 => 
              array (
                'name' => 'date_of_issue_c',
                'label' => 'LBL_DATE_OF_ISSUE',
                'span' => 3,
              ),
              4 => 
              array (
                'name' => 'owner_unknown_c',
                'label' => 'LBL_OWNER_UNKNOWN',
              ),
              5 => 
              array (
                'name' => 'without_project_c',
                'label' => 'LBL_WITHOUT_PROJECT',
              ),
              6 => 
              array (
                'name' => 'board_invoice_c',
                'label' => 'LBL_BOARD_INVOICE',
              ),
              7 => 
              array (
                'name' => 'multiproject_c',
                'label' => 'LBL_MULTIPROJECT',
              ),
              8 => 
              array (
                'name' => 'unproject_pm_c',
                'studio' => 'visible',
                'label' => 'LBL_UNPROJECT_PM',
                'span' => 3,
              ),
              9 => 
              array (
                'name' => 'without_project_comment_c',
                'label' => 'LBL_WITHOUT_PROJECT_COMMENT',
                'span' => 6,
              ),
              10 => 
              array (
                'span' => 3,
              ),
              11 => 
              array (
                'name' => 'board_member_c',
                'label' => 'LBL_BOARD_MEMBER',
                'span' => 3,
              ),
              12 => 
              array (
                'span' => 9,
              ),
              13 => 
              array (
                'name' => 'part_invoice_c',
                'label' => 'LBL_PART_INVOICE',
                'span' => 2,
              ),
              14 => 
              array (
                'name' => 'last_invoice_c',
                'label' => 'LBL_LAST_INVOICE',
                'span' => 2,
              ),
              15 => 
              array (
                'span' => 8,
              ),
              16 =>
              array (
                'name' => 'project1_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT1',
                'span' => 6,
              ),
              17 =>
              array (
                'related_fields' =>
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett1_c',
                'label' => 'LBL_NETT1',
                'span' => 3,
              ),
              18 =>
              array (
                'name' => 'vat_c',
                'label' => 'LBL_VAT_C',
                'span' => 3,
              ),

              19 => 
              array (
                'name' => 'project2_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT2',
                'span' => 6,
              ),
              20 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett2_c',
                'label' => 'LBL_NETT2',
                'span' => 3,
              ),
              21 =>
              array (
                'span' => 3,
              ),

              22 => 
              array (
                'name' => 'project3_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT3',
                'span' => 6,
              ),
              23 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett3_c',
                'label' => 'LBL_NETT3',
                'span' => 3,
              ),
              24 =>
              array (
                'span' => 3,
              ),

              25 => 
              array (
                'name' => 'project4_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT4',
                'span' => 6,
              ),
              26 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett4_c',
                'label' => 'LBL_NETT4',
                'span' => 3,
              ),
              28 =>
              array (
                'span' => 3,
              ),
              29 => 
              array (
                'name' => 'project5_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT5',
                'span' => 6,
              ),
              30 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett5_c',
                'label' => 'LBL_NETT5',
                'span' => 3,
              ),
              31 =>
              array (
                'span' => 3,
              ),
              33 => 
              array (
                'name' => 'project6_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT6',
                'span' => 6,
              ),
              34 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett6_c',
                'label' => 'LBL_NETT6',
                'span' => 3,
              ),
              35 =>
              array (
                'span' => 3,
              ),
              37 => 
              array (
                'name' => 'project7_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT7',
                'span' => 6,
              ),
              38 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett7_c',
                'label' => 'LBL_NETT7',
                'span' => 3,
              ),
              40 =>
              array (
                'span' => 3,
              ),
              41 => 
              array (
                'name' => 'description',
                'span' => 6,
              ),
              42 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'nett_c',
                'label' => 'LBL_NETT',
                'span' => 3,
              ),
              43 => 
              array (
                'related_fields' => 
                array (
                  0 => 'currency_id',
                  1 => 'base_rate',
                ),
                'name' => 'gross_c',
                'label' => 'LBL_GROSS',
                'span' => 3,
              ),
              44 => 
              array (
                'name' => 'archived_c',
                'label' => 'LBL_ARCHIVED',
              ),
              45 => 
              array (
                'name' => 'customid_c',
                'label' => 'LBL_CUSTOMID',
                'cell_css_class' => 'vis_action_hidden',
                'span' => 1,
              ),
              46 => 
              array (
                'name' => 'multiproject_part_c',
                'label' => 'LBL_MULTIPROJECT_PART',
                'cell_css_class' => 'vis_action_hidden',
                'span' => 1,
              ),
              47 => 
              array (
                'name' => 'ac_invoices_ac_invoices_1_name',
                'cell_css_class' => 'vis_action_hidden',
                'span' => 1,
              ),
              48 => 
              array (
                'name' => 'assigned_user_name',
                'cell_css_class' => 'vis_action_hidden',
                'link' => false,
                'span' => 1,
              ),
              49 => 
              array (
                'name' => 'team_name',
                'cell_css_class' => 'vis_action_hidden',
                'span' => 1,
              ),
            ),
          ),
          2 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL5',
            'label' => 'LBL_RECORDVIEW_PANEL5',
            'labels' => true,
            'columns' => 4,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'package_no_c',
                'label' => 'LBL_PACKAGE_NO',
                'span' => 2,
              ),
              1 => 
              array (
                'name' => 'proform_paid_c',
                'label' => 'LBL_PROFORM_PAID',
                'span' => 2,
              ),
              2 => 
              array (
                'name' => 'agreement_link_c',
                'studio' => 'visible',
                'label' => 'LBL_AGREEMENT_LINK',
                'span' => 2,
              ),
              3 => 
              array (
                'name' => 'fcplist3_c',
                'label' => 'LBL_FCPLIST3',
                'span' => 6,
              ),
            ),
          ),
          4 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL1',
            'label' => 'LBL_RECORDVIEW_PANEL1',
            'columns' => 4,
            'labels' => true,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'agreement_na_c',
                'label' => 'LBL_AGREEMENT_NA_C',
              ),
              1 => 
              array (
                'name' => 's_safety_na_c',
                'label' => 'LBL_S_SAFETY_NA_C',
              ),
              2 => 
              array (
                'name' => 'warranty_na_c',
                'label' => 'LBL_WARRANTY_NA_C',
              ),
              3 => 
              array (
                'name' => 'work_completed_na_c',
                'label' => 'LBL_WORK_COMPLETED_NA_C',
              ),
              4 => 
              array (
                'name' => 'na_all_c',
                'label' => 'LBL_NA_ALL',
                'span' => 12,
              ),
            ),
          ),
          5 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL2',
            'label' => 'LBL_RECORDVIEW_PANEL2',
            'columns' => 4,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'accept1_c',
                'label' => 'LBL_ACCEPT1',
              ),
              1 => 
              array (
                'name' => 'accept2_c',
                'label' => 'LBL_ACCEPT2',
              ),
              2 => 
              array (
                'name' => 'accept3_c',
                'label' => 'LBL_ACCEPT3',
              ),
              3 => 
              array (
                'name' => 'accept4_c',
                'label' => 'LBL_ACCEPT4',
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
        'type' => 'shareaction',
        'name' => 'share',
        'label' => 'LBL_RECORD_SHARE_BUTTON',
        'acl_action' => 'view',
      ),
      2 =>
      array (
        'type' => 'divider',
      ),
      3 =>
      array (
        'type' => 'rowaction',
        'event' => 'button:show_description:click',
        'name' => 'show_description',
        'label' => 'Invoice description',
        'acl_action' => 'view',
      ),
      4 =>
      array (
        'type' => 'rowaction',
        'event' => 'button:audit_button:click',
        'name' => 'audit_button',
        'label' => 'LNK_VIEW_CHANGE_LOG',
        'acl_action' => 'view',
      ),
      5 =>
      array(
          'type' => 'divider',
      ),
      6 =>
      array(
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