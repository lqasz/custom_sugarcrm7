<?php
$module_name = 'Cases';
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
            'event' => 'button:pcl_dropdown:click',
            'name' => 'pcl_dropdown',
            'label' => 'Dropdowns',
            'css_class' => 'btn btn-primary',
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
            ),
          ),
          4 => 
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
              1 => array (
                'name' => 'name',
                'readonly' => true,
                'link' => false,
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
            'label' => '1. Zarządzanie kosztami i zakresami',
            'columns' => 6,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (),
          ),
          2 => 
          array (
            'name' => 'panel_body',
            'label' => '2. Dokumentacja projektowa',
            'columns' => 6,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (),
          ),
          3 => 
          array (
            'name' => 'panel_body',
            'label' => '3. Gwarancje',
            'columns' => 6,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (),
          ),
          4 => 
          array (
            'name' => 'panel_body',
            'label' => '4. Wizerunek projektu',
            'columns' => 6,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (),
          ),
          5 => 
          array (
            'name' => 'panel_body',
            'label' => '5. Weryfikacjia kompletności dokumentacji na serwerze / wersji papierowe',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'label5_1',
                'label' => '01 COMMUNICATION MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              1 =>  
              array (
                'name' => 'label5_2',
                'label' => '02 COST MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              2 =>  
              array (
                'name' => 'label5_3',
                'label' => '03 SCOPE MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              3 =>  
              array (
                'name' => 'label5_4',
                'label' => '04 TIME MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              4 =>  
              array (
                'name' => 'label5_5',
                'label' => '05 PROCUREMENT MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              5 =>  
              array (
                'name' => 'label5_6',
                'label' => '06 CHANGE MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              6 =>  
              array (
                'name' => 'label5_7',
                'label' => '07 RISK MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              7 =>  
              array (
                'name' => 'label5_8',
                'label' => '08 QUALITY MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
              8 =>  
              array (
                'name' => 'label5_9',
                'label' => '09 ENVIROMENTAL MANAGMENT',
                'span' => 11,
                'link' => false,
              ),
            ),
          ),
          6 => 
          array (
            'name' => 'panel_body',
            'label' => '6. PCL Comments',
            'columns' => 6,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => 
            array (),
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
