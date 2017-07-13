<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
/*
Dashboard entrypoints
Get various data
http://rms.dev/index.php?entryPoint=getNotifications
http://developer.sugarcrm.com/2014/04/22/sugarcrm-cookbook-sugarquery-the-basics/
*/
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $current_user;

$current_user = BeanFactory::getBean('Users');
// $current_user->getSystemUser();

$db = DBManagerFactory::getInstance();
$exType = 0;
$notifications = array();
$attachments = array();
$answer=array();

if(isset($_GET['getAll']) && $_GET['getAll'] == 1){
    // echo 'jest wszystko dla kier';
    if(!isset($_GET['userID'])){
        $answer = array("status"=>'Wrong ID');
        $JSONDataSQLReport = json_encode($answer,JSON_PRETTY_PRINT);
        echo $JSONDataSQLReport;
        return;
    }

    $userID = $_GET['userID'];
    // echo '3556172b-5c1f-5ec0-f2ad-540ef4f1b8e0 kierson';
    //ac_invoices_notes_1", "ac_invoices_notes_2", "ac_invoices_notes_3", "ac_invoices_notes_4", "ac_invoices_notes_5

    //                        'date_entered', 'id', 'is_read', 'name', 'severity', 'parent_id', 'parent_type', 'confirmation', 'description',
    $q="SELECT *
        FROM notifications n
        WHERE
            n.assigned_user_id='{$userID}'
            AND n.is_read=0
            AND n.deleted=0
        ORDER BY
            n.date_entered DESC
        ";
        // echo '<br />'.print_r($q, true).'<br />';
        $r = $db->query($q);
        while($row = $db->fetchByAssoc($r)){
            if($row['parent_type']=='AC_Invoices'){
                $invoice_id = $row['parent_id'];

                $multi_row=$db->fetchByAssoc($db->query("SELECT `multiproject_c`, `multiproject_part_c` FROM `ac_invoices_cstm` WHERE `id_c`='{$invoice_id}'"));
                
                if($multi_row['multiproject_c'] == true && $multi_row['multiproject_part_c'] == true) {
                    $multi_invoice=$db->fetchByAssoc($db->query("SELECT `ac_invoices_ac_invoices_1ac_invoices_ida` FROM `ac_invoices_ac_invoices_1_c` WHERE `ac_invoices_ac_invoices_1ac_invoices_idb` = '{$invoice_id}'"));
                    $invoice_id = $multi_invoice['ac_invoices_ac_invoices_1ac_invoices_ida'];
                }

                // echo "1) ". $row['parent_id'] ."<br/>";
                // echo "2) ". $invoice_id ."<br/>";

                $q2="SELECT 'ac_invoices_notes_1' as Rel, ac_invoices_notes_1notes_idb as noteID, ac_invoices_notes_1ac_invoices_ida as invoiceID
                    FROM ac_invoices_notes_1_c nn
                    WHERE ac_invoices_notes_1ac_invoices_ida='{$invoice_id}' AND nn.deleted=0
                    UNION ALL
                    SELECT 'ac_invoices_notes_2' as Rel, ac_invoices_notes_2notes_idb as noteID, ac_invoices_notes_2ac_invoices_ida as invoiceID
                    FROM ac_invoices_notes_2_c nn
                    WHERE ac_invoices_notes_2ac_invoices_ida='{$invoice_id}' AND nn.deleted=0
                    UNION ALL
                    SELECT 'ac_invoices_notes_3' as Rel, ac_invoices_notes_3notes_idb as noteID, ac_invoices_notes_3ac_invoices_ida as invoiceID
                    FROM ac_invoices_notes_3_c nn
                    WHERE ac_invoices_notes_3ac_invoices_ida='{$invoice_id}' AND nn.deleted=0
                    UNION ALL
                    SELECT 'ac_invoices_notes_4' as Rel, ac_invoices_notes_4notes_idb as noteID, ac_invoices_notes_4ac_invoices_ida as invoiceID
                    FROM ac_invoices_notes_4_c nn
                    WHERE ac_invoices_notes_4ac_invoices_ida='{$invoice_id}' AND nn.deleted=0
                    UNION ALL
                    SELECT 'ac_invoices_notes_5' as Rel, ac_invoices_notes_5notes_idb as noteID, ac_invoices_notes_5ac_invoices_ida as invoiceID
                    FROM ac_invoices_notes_5_c nn
                    WHERE ac_invoices_notes_5ac_invoices_ida='{$invoice_id}' AND nn.deleted=0";
                // echo $q2.'<br /><br />';
                $r2 = $db->query($q2);
                while($row2 = $db->fetchByAssoc($r2)){
                    $row[$row2['Rel']][] = $row2;
                    $attachments[$row['id']][$row['parent_id']][] = $row2['noteID'];
                }

                $q3 = "SELECT IF(multiproject_c = 1 AND multiproject_part_c = 0, nett_c, nett1_c) AS netto FROM ac_invoices_cstm WHERE id_c = '{$row['parent_id']}'";
                // echo $q3 ."<br/>";

                $r3 = $db->query($q3);
                while($row2 = $db->fetchByAssoc($r3)) {
                    $row['netto'] = number_format($row2['netto'], 2);
                }

                $q4 = "SELECT name FROM accounts WHERE id IN(SELECT `account_id_c` FROM `ac_invoices_cstm` WHERE id_c = '{$row['parent_id']}')";
                $r4 = $db->query($q4);
                while($row2 = $db->fetchByAssoc($r4)){
                   $row['account'] = $row2['name'];
                }
            }


            $notifications['notifications'][] = $row;
        }
        $notifications['attachments'] = $attachments;
        
        $exType = 2;
}else{
    $answer = array("status"=>0);
    $exType = 3;
}

if($exType==1){
    foreach ($notifications as $k => $v) {
        echo $v['name'].'<br />';
    }
}elseif ($exType==2) {
    $JSONDataSQLReport = json_encode($notifications,JSON_PRETTY_PRINT);
    echo $JSONDataSQLReport;
    // $jsonData = (string) html_entity_decode($sqlJsonField);
    // $phpStdbjData = (array) json_decode($jsonData,true);
}elseif ($exType==3) {
    $JSONDataSQLReport = json_encode($answer,JSON_PRETTY_PRINT);
    echo $JSONDataSQLReport;
}
// print_r($current_user);
//http://rms2.reesco.pl/rest/v10/Notifications/filter?order_by=date_entered%3Adesc&fields=date_entered%2Cid%2Cis_read%2Cname%2Cseverity%2Cparent_id&max_num=10&my_items=1&filter%5B0%5D%5Bis_read%5D%5B%24equals%5D=false

// order_by:date_entered:desc // fields:date_entered,id,is_read,name,severity,parent_id // max_num:10 // my_items:1 // filter[0][is_read][$equals]:false

// // Prepare SQL as Text to be saved in DB
// $prepareSQL = str_replace("&#039;","'",$row['sqlquery_text']);
// // Save Json in DB
// $result = $db->query(trim($prepareSQL),true);
// $rowsJsonData = array();
