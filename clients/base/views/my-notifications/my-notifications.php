<?php
/**
 * Created using PhpStorm.
 * User: shad
 * Date: 7/30/15
 * Time: 3:38 PM
 * File Name: my-notifications.php
 * Project: Proficio
 */


$viewdefs['base']['view']['my-notifications'] = array(
    'dashlets' => array( 
        array(
            'label' => 'LBL_MY_NOTIFICATIONS_DASHLET',
            'description' => 'List of my notifications',
            'config' => array(),
            'preview' => array(
            ),
        ), 
    ),
    'dashlet_config_panels' =>
        array(
            array(
                'name' => 'panel_body',
                'columns' => 2,
                'labelsOnTop' => true,
                'placeholders' => true,
                'fields' => array(
                    array(
                        'name' => 'auto_refresh',
                        'label' => 'LBL_REPORT_AUTO_REFRESH',
                        'type' => 'enum',
                        'options' => 'sugar7_dashlet_auto_refresh_options'
                    ),
                ),
            ),
        ),
);


?>