<?php
/** Metdata for the add note custom popup view
 * The buttons array contains the buttons to be shown in the popu
 * The fields array can be modified accordingly to display more number of fields if required
 * */
$viewdefs['AA_Answers']['base']['view']['quick-create'] = array(
    'buttons' => array(
        // array(
        //     'name' => 'cancel_button',
        //     'type' => 'button',
        //     'label' => 'LBL_CANCEL_BUTTON_LABEL',
        //     'value' => 'cancel',
        //     'css_class' => 'btn-invisible btn-link',
        // ), 
        array(
            'name' => 'save_button', 
            'type' => 'button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'value' => 'save',
            'css_class' => 'btn-primary',
        ),
    ),
    'panels' => array(
        array(
            'fields' => array(
                0 =>
                array(
                    'name' => 'name',
                    'default' => true,
                    'enabled' => true,
                    'width' => 35,
                    'value' => 'odpowiedz',
                    'required' => true //subject is required
                ),
                1 => 
                array(
                  'name' => 'answer',
                  'default' => true,
                  'enabled' => true,
                  'width' => 5,
                ),
                2 => 
                array(
                  'name' => 'answer_time',
                  'default' => true,
                  'enabled' => true,
                  'width' => 5,
                ),
                3 => 
                array(
                  'name' => 'open_answer',
                  'default' => true,
                  'enabled' => true,
                  'width' => 35,
                  'rows' => 5,
                ),

            )
        )
    )
);