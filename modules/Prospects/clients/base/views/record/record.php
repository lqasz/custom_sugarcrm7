<?php
$viewdefs['Prospects'] = 
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
                'acl_action' => 'edit',
              ),
              1 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:convert_button:click',
                'name' => 'convert_button',
                'label' => 'LBL_CONVERT_BUTTON_LABEL',
                'acl_action' => 'edit',
              ),
              2 => 
              array (
                'type' => 'vcard',
                'name' => 'vcard_button',
                'label' => 'LBL_VCARD_DOWNLOAD',
                'acl_action' => 'edit',
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
                'name' => 'full_name',
                'type' => 'fullname',
                'label' => 'LBL_NAME',
                'dismiss_label' => true,
                'fields' => 
                array (
                  0 => 'salutation',
                  1 => 'first_name',
                  2 => 'last_name',
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
                'name' => 'accounts_prospects_1_name',
                'label' => 'LBL_ACCOUNTS_PROSPECTS_1_FROM_ACCOUNTS_TITLE',
              ),
              1 => 
              array (
                'name' => 'client_position_c',
                'label' => 'LBL_CLIENT_POSITION',
              ),
              2 => 
              array (
                'name' => 'website_c',
                'label' => 'LBL_WEBSITE',
                'placeholder' => 'Required',
              ),
              3 => 
              array (
                'name' => 'title',
                'placeholder' => 'Required',
              ),
              4 => 'phone_mobile',
              5 => 
              array (
                'name' => 'department',
                'placeholder' => 'Required',
              ),
              6 => 'phone_work',
              7 => 
              array (
                'name' => 'linkedin_c',
                'label' => 'LBL_LINKEDIN',
              ),
              8 => 
              array (
                'name' => 'email',
              ),
              9 => 
              array (
                'type' => 'url',
                'name' => 'facebook',
                'comment' => 'The facebook name of the user',
                'label' => 'LBL_FACEBOOK',
              ),
              10 => 
              array (
                'name' => 'buildings_c',
                'label' => 'LBL_BUILDINGS',
              ),
              11 => 
              array (
                'type' => 'url',
                'name' => 'twitter',
              ),
              12 => 
              array (
                'name' => 'birthdate',
                'label' => 'LBL_BIRTHDATE',
              ),
              13 => 
              array (
                'name' => 'primary_address',
                'type' => 'fieldset',
                'css_class' => 'address',
                'label' => 'LBL_PRIMARY_ADDRESS',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'primary_address_street',
                    'css_class' => 'address_street',
                    'placeholder' => 'LBL_PRIMARY_ADDRESS_STREET',
                    'rows' => 2,
                  ),
                  1 => 
                  array (
                    'name' => 'primary_address_city',
                    'css_class' => 'address_city',
                    'placeholder' => 'LBL_PRIMARY_ADDRESS_CITY',
                  ),
                ),
              ),
              14 => 
              array (
                'name' => 'hobby_c',
                'label' => 'LBL_HOBBY',
              ),
              15 => 
              array (
                'name' => 'assigned_user_name',
                'link' => false,
              ),
              16 => 
              array (
                'name' => 'status_c',
                'label' => 'LBL_STATUS',
              ),
              17 => 
              array (
                'name' => 'date_of_first_call_c',
                'label' => 'LBL_DATE_OF_FIRST_CALL',
              ),
              18 => 
              array (
                'name' => 'description',
                'related_fields' => 
                array (
                  0 => 'lead_id',
                ),
                'span' => 6,
              ),
              19 => 
              array (
                'name' => 'lead_was_created_c',
                'label' => 'LBL_LEAD_WAS_CREATED',
                'readonly' => true,
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
