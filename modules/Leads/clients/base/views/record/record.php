<?php
$viewdefs['Leads'] = 
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
                'name' => 'convert_opportunitie',
                'event' => 'button:convert_opportunitie:click',
                'label' => 'Associate Opportunitie',
              ),
              2 => 
              array (
                'type' => 'rowaction',
                'name' => 'convert_fee',
                'event' => 'button:convert_fee:click',
                'label' => 'Associate Fee Proposal',
              ),
              3 => 
              array (
                'type' => 'vcard',
                'name' => 'vcard_button',
                'label' => 'LBL_VCARD_DOWNLOAD',
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
              array (
                'type' => 'divider',
              ),
              6 => 
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
                  0 => 
                  array (
                    'name' => 'salutation',
                    'type' => 'enum',
                    'enum_width' => 'auto',
                    'searchBarThreshold' => 7,
                  ),
                  1 => 'first_name',
                  2 => 'last_name',
                ),
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
                'name' => 'accounts_leads_1_name',
              ),
              1 => 
              array (
                'name' => 'client_position_c',
                'label' => 'LBL_CLIENT_POSITION',
              ),
              2 => 'website',
              3 => 'title',
              4 => 'phone_mobile',
              5 => 
              array (
                'name' => 'department',
              ),
              6 => 'phone_work',
              7 => 
              array (
                'name' => 'linkedin_c',
                'label' => 'LBL_LINKEDIN',
              ),
              8 => 'email',
              9 => 
              array (
                'type' => 'url',
                'name' => 'facebook',
                'comment' => 'The facebook name of the user',
                'label' => 'LBL_FACEBOOK',
              ),
              10 => 
              array (
                'name' => 'contacts_c',
                'label' => 'LBL_CONTACTS',
              ),
              11 => 
              array (
                'type' => 'url',
                'name' => 'twitter',
              ),
              12 => 
              array (
                'name' => 'buildings_c',
                'label' => 'LBL_BUILDINGS',
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
                    'rows' => 1,
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
                'name' => 'refered_by',
                'comment' => 'Identifies who refered the lead',
                'label' => 'LBL_REFERED_BY',
              ),
              15 => 
              array (
                'name' => 'assigned_user_name',
                'link' => false,
              ),
              16 => 
              array (
                'name' => 'birthdate',
                'comment' => 'The birthdate of the contact',
                'label' => 'LBL_BIRTHDATE',
              ),
              17 => 
              array (
                'name' => 'hobby_c',
                'label' => 'LBL_HOBBY',
              ),
              18 => 
              array (
                'name' => 'report_to_name',
                'label' => 'LBL_REPORTS_TO',
              ),
              19 => 
              array (
                'name' => 'date_entered',
                'comment' => 'Date record created',
                'studio' => 
                array (
                  'portaleditview' => false,
                ),
                'readonly' => true,
                'label' => 'LBL_DATE_ENTERED',
              ),
              20 => 
              array (
                'name' => 'description',
                'span' => 6,
              ),
              21 => 
              array (
                'name' => 'status',
                'span' => 6,
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => true,
        ),
      ),
    ),
  ),
);
