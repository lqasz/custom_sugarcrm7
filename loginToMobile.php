<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Example Mobile API Logic Hooks class
 **/
class MobileApiLogicHook
{
    /**
     * Logic hook function tied to 'after_routing' event
     */
    function logMobileAfterRouting($event, $arguments)
    {
        if( !empty($arguments['api']->user->id) ){
            $db = DBManagerFactory::getInstance();
            $user_id = $arguments['api']->user->id;
            $current_date = date('Y') .'-'. date('m') .'-'. date('d');

            //If request came from a Mobile platform
            if($arguments['api']->platform == "mobile") {

                if(!empty($user_id) && $user_id != 1) {
                	$db->query("INSERT IGNORE INTO `tracker_mobile` (`id`, `date_entered`, `mobile`) VALUES ('{$user_id}', '$current_date', 1)");
                }
                // $GLOBALS['log']->error("-------------------------------------------------");
                // $GLOBALS['log']->error($arguments);
            } else {
                if(!empty($user_id) && $user_id != 1) {
                    $db->query("INSERT IGNORE INTO `tracker_mobile` (`id`, `date_entered`, `mobile`) VALUES ('{$user_id}', '$current_date', 0)");
                }
            }
        }
    }
}
