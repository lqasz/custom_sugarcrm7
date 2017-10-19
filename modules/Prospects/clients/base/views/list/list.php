<?php
$viewdefs['Prospects'] = 
array (
  'base' => 
  array (
    'view' => 
    array (
      'list' => 
      array (
        'panels' => 
        array (
          0 => 
          array (
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'full_name',
                'type' => 'fullname',
                'fields' => 
                array (
                  0 => 'last_name',
                  1 => 'first_name',
                ),
                'link' => true,
                'css_class' => 'full-name',
                'label' => 'LBL_LIST_NAME',
                'enabled' => true,
                'default' => true,
              ),
              1 => 
              array (
                'name' => 'accounts_prospects_1_name',
                'label' => 'LBL_ACCOUNTS_PROSPECTS_1_FROM_ACCOUNTS_TITLE',
                'enabled' => true,
                'id' => 'ACCOUNTS_PROSPECTS_1ACCOUNTS_IDA',
                'link' => true,
                'sortable' => false,
                'default' => true,
                'width' => 'medium',
              ),
              2 => 
              array (
                'name' => 'company_type_c',
                'enabled' => true,
                'default' => true,
              ),
              3 => 
              array (
                'name' => 'rsc_c',
                'enabled' => true,
                'default' => true,
                'readonly' => true,
                'sortable' => false,
              ),
              4 => 
              array (
                'name' => 'pkig_c',
                'enabled' => true,
                'default' => true,
                'readonly' => true,
                'sortable' => false,
              ),
              5 => 
              array (
                'name' => 'ats_c',
                'enabled' => true,
                'default' => true,
                'readonly' => true,
                'sortable' => false,
              ),
              6 => 
              array (
                'name' => 'rwt_c',
                'enabled' => true,
                'default' => true,
                'readonly' => true,
                'sortable' => false,
              ),
              7 => 
              array (
                'name' => 'rdsg_c',
                'enabled' => true,
                'default' => true,
                'readonly' => true,
                'sortable' => false,
              ),
              8 => 
              array (
                'name' => 'assigned_user_name',
                'label' => 'LBL_ASSIGNED_TO',
                'enabled' => true,
                'id' => 'ASSIGNED_USER_ID',
                'link' => false,
                'default' => true,
              ),
              9 => 
              array (
                'name' => 'email',
                'label' => 'LBL_LIST_EMAIL_ADDRESS',
                'enabled' => true,
                'default' => false,
              ),
              10 => 
              array (
                'name' => 'linkedin_c',
                'label' => 'LBL_LINKEDIN',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
        ),
        'orderBy' => array (
          'field' => 'date_of_last_comment_c',
          'direction' => 'desc',
        ),
      ),
    ),
  ),
);
