<?php
$viewdefs['Project'] = 
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
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'estimated_start_date',
                'label' => 'LBL_DATE_START',
              ),
              1 => 
              array (
                'name' => 'project_team_c',
                'studio' => 'visible',
                'label' => 'LBL_PROJECT_TEAM',
                'link' => false,
              ),
              2 => 
              array (
                'name' => 'estimated_end_date',
                'label' => 'LBL_DATE_END',
              ),
              3 => 
              array (
                'name' => 'pm_c',
                'studio' => 'visible',
                'label' => 'LBL_PM',
                'link' => false,
              ),
              4 => 
              array (
                'name' => 'client_c',
                'studio' => 'visible',
                'label' => 'LBL_CLIENT',
              ),
              5 => 
              array (
                'name' => 'sv_c',
                'studio' => 'visible',
                'label' => 'LBL_SV',
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
                'name' => 'qs_c',
                'studio' => 'visible',
                'label' => 'LBL_QS',
                'link' => false,
              ),
              8 => 
              array (
                'name' => 'aa_buildings_project_1_name',
                'label' => 'LBL_AA_BUILDINGS_PROJECT_1_FROM_AA_BUILDINGS_TITLE',
              ),
              9 => 
              array (
                'name' => 'days_for_payment_c',
                'label' => 'LBL_DAYS_FOR_PAYMENT_C',
              ),
              10 => 
              array (
                'name' => 'archival_c',
                'label' => 'LBL_ARCHIVAL',
              ),
              11 => 
              array (
                'name' => 'project_ac_feeproposal_1_name',
                'label' => 'LBL_PROJECT_AC_FEEPROPOSAL_1_FROM_AC_FEEPROPOSAL_TITLE',
              ),
              12 => 
              array (
                'name' => 'archival_date_c',
                'label' => 'LBL_ARCHIVAL_DATE',
                'readonly' => true,
              ),
              13 => 
              array (
                'name' => 'add_folders_c',
                'label' => 'LBL_ADD_FOLDERS',
                'hide' => true,
                'css_class' => 'hide',
              ),
              14 => 
              array (
              ),
              15 => 
              array (
                'name' => 'cases_project_1_name',
              ),
            ),
          ),
          2 => 
          array (
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'team_name',
                'span' => 12,
              ),
              1 => 
              array (
                'name' => 'date_modified_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_MODIFIED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_modified',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'modified_by_name',
                  ),
                ),
              ),
              2 => 
              array (
                'name' => 'date_entered_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_ENTERED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_entered',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'created_by_name',
                  ),
                ),
              ),
              3 => 
              array (
                'name' => 'photopath_c',
                'label' => 'LBL_PHOTOPATH',
              ),
              4 => 
              array (
                'name' => 'middle_date_c',
                'studio' => 'visible',
                'label' => 'LBL_MIDDLE_DATE',
              ),
              5 => 
              array (
                'name' => 'building_c',
                'studio' => 'visible',
                'label' => 'LBL_BUILDING',
              ),
              6 => 
              array (
                'name' => 'status',
                'label' => 'LBL_STATUS',
              ),
              7 => 
              array (
                'name' => 'agreement_c',
                'studio' => 'visible',
                'label' => 'LBL_AGREEMENT_C',
              ),
              8 => 'assigned_user_name',
              9 => 
              array (
                'name' => 'cm_tasks_c',
                'label' => 'LBL_CM_TASKS',
              ),
              10 => 
              array (
                'name' => 'gc_tasks_c',
                'label' => 'LBL_GC_TASKS',
              ),
              11 => 
              array (
                'name' => 'tasks_c',
                'studio' => 'visible',
                'label' => 'LBL_TASKS',
              ),
              12 => 
              array (
                'name' => 'is_template',
                'comment' => 'Should be checked if the project is a template',
                'label' => 'LBL_IS_TEMPLATE',
              ),
              13 => 
              array (
                'name' => 'end_date_from_agreement_c',
                'label' => 'LBL_END_DATE_FROM_AGREEMENT',
              ),
              14 => 
              array (
                'name' => 'project_number_c',
                'label' => 'LBL_PROJECT_NUMBER_C',
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => false,
          'maxColumns' => '2',
        ),
        'buttons' => 
        array (
          0 => 
          array (
            'type' => 'button',
            'name' => 'project_pcl',
            'label' => 'PCL',
            'event' => 'button:project_pcl:click',
            'css_class' => 'btn btn-primary',
            'acl_action' => 'view',
          ),
          1 => 
          array (
            'type' => 'button',
            'name' => 'project_gantt',
            'label' => 'Gantt Diagram',
            'event' => 'button:project_gantt:click',
            'css_class' => 'btn btn-primary',
            'acl_action' => 'view',
          ),
          2 => 
          array (
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
          ),
          3 => 
          array (
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
          ),
          4 => 
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
                'type' => 'rowaction',
                'event' => 'button:project_invoices:click',
                'name' => 'project_invoices',
                'label' => 'Project Invoices in CSV',
                'acl_action' => 'view',
              ),
            ),
          ),
          5 => 
          array (
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
          ),
        ),
      ),
    ),
  ),
);
