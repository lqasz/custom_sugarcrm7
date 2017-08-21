<?php
$module_name = 'AB_TimeSheetSummary';
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
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'assigned_user_name',
                'span' => 12,
              ),
              1 => 
              array (
                'name' => 'accept_by_fa_c',
                'label' => 'LBL_ACCEPT_BY_FA',
              ),
              2 => 
              array (
                'name' => 'accept_by_sv_c',
                'label' => 'LBL_ACCEPT_BY_SV',
              ),
              3 => 
              array (
                'name' => 'description',
                'span' => 12,
              ),
              4 => 
              array (
                'name' => 'responsible_dep_c',
                'label' => 'LBL_RESPONSIBLE_DEP',
                'css_class' => 'hide',
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
