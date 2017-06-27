<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Opportunities_Customlogic {

    function add_number($bean, $event, $arguments)
    {
        $db = DBManagerFactory::getInstance();

        if (empty($bean->fetched_row['id'])) {
            $res = $db->query('SELECT * FROM opportunities_cstm ORDER BY fees_porposal_number_c DESC LIMIT 1');

            $res2 = $db->fetchByAssoc($res);

            // $akt_data = New DateTime('NOW');

            if( $res2['fees_porposal_number_c']=='1' || intval($res2['fees_porposal_number_c'])>= 1){
$bean->fees_porposal_number_c  = $bean->fees_porposal_number_c +1;
                // $nnn = intval(substr( $res2['fees_porposal_number_c'], 2, 10));
                // $nnn = str_pad($nnn+1, 4, 0, STR_PAD_LEFT);
                // $bean->fees_porposal_number_c = $akt_data->format('y').$nnn;

            }else{
                $bean->fees_porposal_number_c = '1';
            }


        }

    }

   function create_name($bean, $event, $arguments) {
        $db = DBManagerFactory::getInstance();

        $query = 'UPDATE opportunities fp SET fp.name = "'.$bean->fees_porposal_number_c.' '.$bean->custom_c.' '.$bean->service_c.'" WHERE fp.id="'.$bean->id.'" AND fp.deleted=0';
        $db->query($query);

    }

    function saveToFee($bean, $event, $arguments) {
        if ($arguments['isUpdate'] == 1) {

            $GLOBALS['log']->fatal('Check changes at sales stage');
            if( $arguments['dataChanges']['sales_stage']['before'] != $arguments['dataChanges']['sales_stage']['after']){
                $GLOBALS['log']->fatal('Check if save as Fee Prop');
                if($arguments['dataChanges']['sales_stage']['after'] == 'Proposal/Price Quote'){
                    $GLOBALS['log']->fatal('Save Fee Prop');
                    // SugarApplication::redirect("http://dev2.rms.reesco.pl/#AC_FeeProposal");
// $_POST['redirect_url'] = '#AC_FeeProposal/create';
//                 if(headers_sent() || strlen($redirect_url) > 2083){
//                     $GLOBALS['log']->fatal('op1');
//                     // echo 'window.location.replace("http://stackoverflow.com");'
//                     echo '<html ' . get_language_header() . '><head><title>RMS2</title></head><body>';
//                     echo '<form name="redirect" action="' .$_POST['redirect_url']. '" method="GET">';

//                     foreach($_POST as $param => $value) {
//                         if($param != 'redirect_url' ||$param != 'submit') {
//                             echo '<input type="hidden" name="'.$param.'" value="'.$value.'">';
//                         }
//                     }
//                     echo '</form><script language="javascript" type="text/javascript">document.redirect.submit();</script>';
//                     echo '</body></html>';
                // }
                // else{
                //     $GLOBALS['log']->fatal('op2');

                //     header("Location: {$redirect_url}");
                //     die();
                // }


                }
            }
        }

    }

}

?>