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
            'columns' => 3,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => array (
                'name' => 'assigned_user_name',
                'span' => 4,
                'readonly' => true,
                'link' => false,
              ),
              1 => 
              array (
                'name' => 'accepted_by_tl_c',
                'label' => 'LBL_ACCEPTED_BY_TL',
                'span' => 4,
                'readonly' => true,
              ),
              2 => 
              array (
                'name' => 'rejected_by_tl_c',
                'label' => 'LBL_REJECTED_BY_TL',
                'span' => 4,
                'readonly' => true,
              ),
              3 => 
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
