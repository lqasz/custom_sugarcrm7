<?php

/**
 * Metadata for the Case Count by Status example dashlet view
 * 
 * This dashlet is only allowed to appear on the Case module's list view
 * which is also known as the 'records' layout.
 */
$viewdefs['base']['view']['project-revenue'] = array(
    'dashlets' => array(
        array(
            //Display label for this dashlet
            'label' => 'Project Revenue',
            //Description label for this Dashlet
            'description' => 'Dashlet which shows revenue of the project',
            'config' => array(
            ),
            'preview' => array(
            ),
            //Filter array decides where this dashlet is allowed to appear
            'filter' => array( 
                //Modules where this dashlet can appear
                'module' => array(
                    'Project',
                ),
                //Views where this dashlet can appear
                'view' => array(
                    'record',
                ),
            ),
        ),
    ),
    'custom_toolbar' => array(
        "buttons" => array(
            array(
                "type" => "dashletaction",
                "css_class" => "dashlet-toggle btn btn-invisible minify",
                "icon" => "fa-chevron-up",
                "action" => "toggleMinify",
                "tooltip" => "LBL_DASHLET_TOGGLE",
            ),
            //Specified buttons in dropdown
            array(
                "dropdown_buttons" => array(
                    array(
                        "name" => "project-revenue-edit",
                        "type" => "dashletaction",
                        "action" => "editProjectData",
                        "label" => "LBL_DASHLET_CONFIG_EDIT_LABEL",
                    ),
                    array(
                        "type" => "dashletaction",
                        "action" => "refreshClicked",
                        "label" => "LBL_DASHLET_REFRESH_LABEL",
                    ),
                ),
            ),
        ),
    ),
);