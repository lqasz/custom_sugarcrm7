<?php
$viewdefs['Accounts'] = 
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
                'type' => 'shareaction',
                'name' => 'share',
                'label' => 'LBL_RECORD_SHARE_BUTTON',
                'acl_action' => 'view',
              ),
              8 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:audit_button:click',
                'name' => 'audit_button',
                'label' => 'LNK_VIEW_CHANGE_LOG',
                'acl_action' => 'view',
              ),
              10 => 
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
            'columns' => 4,
            'label' => 'LBL_PANEL_HEADER',
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
                'span' => 4,
                'events' => 
                array (
                  'keyup' => 'update:account',
                ),
              ),
              2 => array (
                'name' => 'category_service_c',
                'span' => 4,
              ),
              3 => 
              array (
                'name' => 'favorite',
                'label' => 'LBL_FAVORITE',
                'type' => 'favorite',
                'dismiss_label' => true,
              ),
              4 => 
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
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'billing_address',
                'type' => 'fieldset',
                'css_class' => 'address',
                'label' => 'LBL_BILLING_ADDRESS',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'billing_address_street',
                    'css_class' => 'address_street',
                    'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                    'rows' => 2,
                  ),
                  1 => 
                  array (
                    'name' => 'billing_address_city',
                    'css_class' => 'address_city',
                    'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                  ),
                  2 => 
                  array (
                    'name' => 'billing_address_state',
                    'css_class' => 'address_state',
                    'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                  ),
                  3 => 
                  array (
                    'name' => 'billing_address_postalcode',
                    'css_class' => 'address_zip',
                    'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                  ),
                  4 => 
                  array (
                    'name' => 'billing_address_country',
                    'css_class' => 'address_country',
                    'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                  ),
                ),
                'span' => 6,
              ),
              1 => 
              array (
                'name' => 'shipping_address',
                'type' => 'fieldset',
                'css_class' => 'address',
                'label' => 'LBL_SHIPPING_ADDRESS',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'shipping_address_street',
                    'css_class' => 'address_street',
                    'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
                    'rows' => 2,
                  ),
                  1 => 
                  array (
                    'name' => 'shipping_address_city',
                    'css_class' => 'address_city',
                    'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
                  ),
                  2 => 
                  array (
                    'name' => 'shipping_address_state',
                    'css_class' => 'address_state',
                    'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
                  ),
                  3 => 
                  array (
                    'name' => 'shipping_address_postalcode',
                    'css_class' => 'address_zip',
                    'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                  ),
                  4 => 
                  array (
                    'name' => 'shipping_address_country',
                    'css_class' => 'address_country',
                    'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                  ),
                  5 => 
                  array (
                    'name' => 'copy',
                    'label' => 'NTC_COPY_BILLING_ADDRESS',
                    'type' => 'copy',
                    'mapping' => 
                    array (
                      'billing_address_street' => 'shipping_address_street',
                      'billing_address_city' => 'shipping_address_city',
                      'billing_address_state' => 'shipping_address_state',
                      'billing_address_postalcode' => 'shipping_address_postalcode',
                      'billing_address_country' => 'shipping_address_country',
                    ),
                  ),
                ),
                'span' => 6,
              ),
              2 => 
              array (
                'name' => 'phones_office',
                'type' => 'fieldset',
                'css_class' => 'office-phone',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'phone_office',
                    'css_class' => 'phone-number',
                    'placeholder' => 'Office Phone',
                  ),
                  1 => 
                  array (
                    'name' => 'phone_office1_c',
                    'css_class' => 'phone-number',
                    'placeholder' => 'Office Phone',
                  ),
                  2 => 
                  array (
                    'name' => 'phone_office2_c',
                    'css_class' => 'phone-number',
                    'placeholder' => 'Office Phone',
                  ),
                ),
                'span' => 4,
              ),
              3 => 
              array (
                'name' => 'other_contact_data',
                'type' => 'fieldset',
                'css_class' => 'contact-data others',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'phone_fax',
                    'studio' => 'visible',
                    'css_class' => 'other-data',
                    'placeholder' => 'Fax',
                  ),
                  1 => 
                  array (
                    'name' => 'website',
                    'studio' => 'visible',
                    'css_class' => 'other-data',
                    'placeholder' => 'Website',
                  ),
                  2 => 
                  array (
                    'name' => 'email',
                    'studio' => 'visible',
                    'css_class' => 'other-data',
                  ),
                ),
                'span' => 4,
              ),
              4 => 
              array (
                'name' => 'company_data',
                'type' => 'fieldset',
                'css_class' => 'company-data',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'nip_c',
                    'label' => 'LBL_NIP',
                    'placeholder' => 'NIP',
                    'span' => 3,
                  ),
                  1 => 
                  array (
                    'name' => 'krs_c',
                    'label' => 'LBL_KRS',
                    'placeholder' => 'KRS',
                    'span' => 3,
                  ),
                  2 => 
                  array (
                    'name' => 'regon_c',
                    'label' => 'LBL_REGON',
                    'placeholder' => 'REGON',
                    'span' => 3,
                  ),
                ),
                'span' => 4,
              ),
              5 => 
              array (
                'name' => 'owner_c',
                'studio' => 'visible',
                'css_class' => 'owner',
              ),
              6 => 
              array (
                'name' => 'proxy_c',
                'studio' => 'visible',
                'css_class' => 'proxy',
              ),
              7 => 
              array (
                'name' => 'query_c',
                'studio' => 'visible',
                'css_class' => 'query',
              ),
              8 => 
              array (
                'name' => 'reesco_curator_c',
                'studio' => 'visible',
                'css_class' => 'query',
              ),
              9 => 
              array (
                'name' => 'description',
                'span' => 9,
              ),
              10 => 
              array (
                'span' => 3,
              ),
            ),
          ),
          2 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL3',
            'label' => 'PCL Comments',
            'columns' => 1,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 =>  
              array (
                'name' => 'comments',
                'label' => '',
                'span' => 12,
                'link' => false,
              ),
            ),
          ),
          3 => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL1',
            'label' => 'QS Card',
            'columns' => 7,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 =>
              array (
                'name' => 'engineers_c',
                'label' => 'LBL_ENGINEERS',
                'span' => 1,
              ),
              1 => 
              array (
                'name' => 'others_c',
                'label' => 'LBL_OTHERS',
                'span' => 1,
              ),
              2 => 
              array (
                'span' => 2,
              ),
              3 => 
              array (
                'name' => 'cilent_type_c',
                'label' => 'LBL_CILENT_TYPE',
                'span' => 4,
                'hide' => true,
              ),
              4 => 
              array (
                'name' => 'contractor_type_c',
                'label' => 'LBL_CONTRACTOR_TYPE',
                'span' => 4,
                'hide' => true,
              ),
              5 => 
              array (
                'name' => 'foremen_c',
                'label' => 'LBL_FOREMEN',
                'span' => 1,
              ),
              6 => 
              array (
                'name' => 'fitters_c',
                'label' => 'LBL_FITTERS',
                'span' => 1,
              ),
              7 => 
              array (
                'span' => 4,
              ),
              8 =>  
              array (
                'name' => 'company_tags_c',
                'label' => 'LBL_COMPANY_TAGS',
                'span' => 6,
              ),
              9 => 
              array (
                'name' => 'country_c',
                'label' => 'Country',
                'span' => 4,
              ),
              10 =>
              array (
                'span' => 2,
              ),
              11 => 
              array (
                'name' => 'lcc_c',
                'label' => 'LBL_LCC',
                'span' => 4,
              ),
              12 => 
              array (
                'name' => 'state_c',
                'label' => 'State',
                'span' => 4,
              ),
              13 =>
              array (
                'span' => 2,
              ),
              14 => 
              array (
                'name' => 'city_c',
                'label' => 'City',
                'span' => 6,
              ),
              15 => 
              array (
                'name' => 'opening_days_c',
                'label' => 'LBL_OPENING_DAYS',
                'span' => 4,
              ),
            ),
          ),
          4 => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL2',
            'label' => 'Statistics Card',
            'columns' => 7,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => 
            array (
              0 =>
              array (
                'name' => 'cash_flow_c',
                'label' => 'LBL_CASH_FLOW',
                'span' => 5,
              ),
              1 => 
              array (
                'name' => 'trade_credit_c',
                'label' => 'LBL_TRADE_CREDIT',
                'span' => 5,
              ),
              2 => 
              array (
                'name' => 'date_of_payment_c',
                'label' => 'LBL_DATE_OF_PAYMENT',
                'span' => 2,
              ),
              3 => 
              array (
                'name' => 'complited_projects_c',
                'label' => 'LBL_COMPLITED_PROJECTS_C',
                'readonly' => true,
                'span' => 2,
              ),
              4 => 
              array (
                'type' => 'currency',
                'name' => 'min_value_c',
                'label' => 'LBL_MIN_VALUE',
                'readonly' => true,
                'span' => 2,
              ),
              5 => 
              array (
                'type' => 'currency',
                'name' => 'max_value_c',
                'label' => 'LBL_MAX_VALUE',
                'readonly' => true,
                'span' => 2,
              ),
              6 => 
              array (
                'type' => 'currency',
                'name' => 'average_orders_c',
                'label' => 'LBL_AVERAGE_ORDERS',
                'readonly' => true,
                'span' => 3,
              ),
              7 => 
              array (
                'type' => 'currency',
                'name' => 'average_value_c',
                'label' => 'LBL_AVERAGE_VALUE',
                'readonly' => true,
                'span' => 3,
              ),
            ),
          ),
          5 => 
          array (
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'collapsed',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'industry',
              ),
              1 => 
              array (
                'name' => 'lcc_c',
                'label' => 'LBL_LCC',
              ),
              2 => 'parent_name',
              3 => 'account_type',
              4 => 'sic_code',
              5 => 'ticker_symbol',
              6 => 'annual_revenue',
              7 => 'employees',
              8 => 'ownership',
              9 => 'rating',
              10 => 
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
              11 => 'assigned_user_name',
              12 => 'team_name',
              13 => 
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
            ),
          ),
        ),
      ),
    ),
  ),
);
