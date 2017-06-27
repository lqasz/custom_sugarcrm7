<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

ob_start();
include ('include/MVC/preDispatch.php');
$startTime = microtime(true);
require_once('include/entryPoint.php');

require_once('include/MVC/SugarApplication.php');
$app = new SugarApplication();
$app->startSession();
$app->execute();
ob_clean();

require_once('vendor/tcpdf/tcpdf.php');
require_once('include/Sugarpdf/Sugarpdf.php');

class FAInvoicesDescription extends Sugarpdf
{
    /**
     * Override
     */
    function process()
    {
        $this->preDisplay();
        $this->buildFileName();
        $this->Output();
        $this->Output2();
    }

    /**
     * Custom header
     */
    public function Header()
    {
        global $db;
        global $current_user;

        if(!$_GET['fv']) {
            echo 'Brak faktury';
            die();
        }
    }

    /**
     * Custom header
     */
    public function Footer()
    {
        $this->SetFont(PDF_FONT_NAME_MAIN, '', 12);
        $this->MultiCell(0,0,'TCPDF Footeraa', 0, 'C');
    }

    /**
     * Predisplay content
     */
    function preDisplay()
    {
        $db = DBManagerFactory::getInstance();
        $this->AddPage();

        if($_GET['multi_part'] == "false" && $_GET['multi'] == "true") {
            $multipart_query = $db->query("SELECT `ac_invoices_ac_invoices_1ac_invoices_idb` AS `part_id` 
                FROM `ac_invoices_ac_invoices_1_c` WHERE `ac_invoices_ac_invoices_1ac_invoices_ida` = '{$_GET['fv']}'");

            while($multipart_row = $db->fetchByAssoc($multipart_query)) {
                $this->buildContent($multipart_row['part_id'], $db);
            }
        } else {
            $this->buildContent($_GET['fv'], $db);
        }

        $this->ln();$this->ln();

        $table = '<table style="border: 2px solid #000; width: 50%;">
            <tr colspan="2">
                <td style="height: 50px;">OPIS</td>
                <td style="height: 50px;"></td>
            </tr>
            <tr colspan="2">
                <td style="height: 25px; border-top: 2px solid #000;">DEKRET</td>
                <td style="height: 25px; border-top: 2px solid #000;"></td>
            </tr>
            <tr colspan="2">
                <td style="height: 25px;">DT</td>
                <td style="height: 25px;"></td>
            </tr>
            <tr colspan="2">
                <td style="height: 25px;">CR</td>
                <td style="height: 25px;">VAT</td>
            </tr>
        </table>';

        $this->writeHTML($table, true, 0, true, true);
        $this->lastPage();

    }

    private function buildContent($fv_id, $db, $data = '')
    {
        $fv = BeanFactory::retrieveBean('AC_Invoices', $fv_id, array('disable_row_level_security' => true));

        $invoice_audit_query = $db->query('SELECT ia.*, u.* FROM ac_invoices_audit ia LEFT JOIN users AS u ON (ia.created_by = u.id) WHERE parent_id="'.$fv->id.'" AND field_name in ("accept1", "accept2", "accept3") ORDER BY date_created DESC ');
        while( $invoice_audit_row = $db->fetchByAssoc($invoice_audit_query) ) {
            
            if( $invoice_audit_row['after_value_string'] == 1 ) {
                $data.= $invoice_audit_row['date_created'].' - '.$invoice_audit_row['first_name'].' '.$invoice_audit_row['last_name'].' zaakceptował fakturę <br />';
            } else {
                $data.= $invoice_audit_row['date_created'].' - '.$invoice_audit_row['first_name'].' '.$invoice_audit_row['last_name'].' nie zaakceptował faktury<br />';
            }
        }

        $account_query = $db->query('SELECT `name` FROM `accounts` WHERE `id`="'. $fv->account_id_c .'"');
        $account_row = $db->fetchByAssoc($account_query);

        $agreement_query = $db->query('SELECT `name` FROM `notes` LEFT JOIN `notes_cstm` ON(`id` = `id_c`) 
                WHERE `parent_id`="'.$fv->id.'" AND `invoice_agreement_c` = "1"');
        $agreement_row = $db->fetchByAssoc($agreement_query);

        $project_query = $db->query('SELECT `name` FROM `project` 
                WHERE `id`="'.$fv->project_id_c.'"');
        $project_row = $db->fetchByAssoc($project_query);

        //Adds a predisplay page
        $this->SetFont('dejavusans', '', 12);
        $this->ln();
        $this->MultiCell(0,0, 'Faktura: '.$fv->invoice_no_c.' w systemie: '.$fv->name, 0, 'L');
        $this->MultiCell(0,0, 'Dostawca: '.$account_row['name'], 0, 'L');
        $this->MultiCell(0,0, 'Projekt: '.$project_row['name'], 0, 'L');
        $this->MultiCell(0,0, 'Umowa: '.$agreement_row['name'], 0, 'L');

        if($fv->more_project_c == 1) {
            $this->MultiCell(0,0, 'Suma netto na ten projekt: '.round($fv->nett1_c,2).'PLN' ,0,'L');
            $this->MultiCell(0,0, 'Cała suma netto : '.round($fv->nett_c,2).'PLN, brutto: '.round($fv->gross_c, 2).'PLN' ,0,'L');
        } else {
            $this->MultiCell(0,0, 'Suma netto: '.round($fv->nett_c,2).'PLN, brutto: '.round($fv->gross_c, 2).'PLN' ,0,'L');
        }

        $this->ln();

        if($fv->proform_paid_c == 1){
            $this->MultiCell(0,0, 'Zapłacono proformą',0,'L');    
        }

        $this->ln();

        $this->SetFont('dejavusans', '', 12);

        $displayFieldValues = unencodeMultienum($fv->fcplist3_c);
        // print_r($GLOBALS['app_list_strings']['fcp_list']);
        $this->MultiCell(0,0, 'LCC: ',0,'L');
        foreach($displayFieldValues as $key=>$value){
            $this->MultiCell(0,0, $GLOBALS['app_list_strings']['fcp_list'][$value] ,0,'L');
        }

        $this->ln();
        $this->ln();

        $this->Write(0, 'Historia', '', 0, 'L', true, 0, false, false, 0);
        $this->SetFont('dejavusans', '', 10);
        $res = $db->query("SELECT ia.*, u.first_name, u.last_name FROM ac_invoices_audit ia inner join users u on u.id=ia.created_by WHERE `parent_id` LIKE '{$fv->id}' ORDER by date_entered DESC");

        while ( $history_log = $db->fetchByAssoc($res) ) {
            $this->MultiCell(0,0, "{$history_log['date_created']} {$history_log['first_name']} {$history_log['last_name']}  changed {$history_log['field_name']} from {$history_log['before_value_string']} to {$history_log['after_value_string']}"  ,0,'L');
        }

        $this->writeHTML($data, true, 0, true, true);

        $komentarze = '';

        $users = array();
        $res = $db->query('SELECT id, CONCAT( first_name,  " ", last_name ) AS user_name FROM users WHERE first_name IS NOT NULL');
        while ( $user = $db->fetchByAssoc($res) ) {
            $users[$user['id']]=$user['user_name'];
        }

        $ret = $db->query('SELECT * FROM (SELECT id, data as "json", created_by, date_entered, "post" AS type FROM activities WHERE activity_type NOT LIKE "update" AND parent_id="'.$fv->id.'" AND created_by IS NOT NULL UNION ALL SELECT id, data as "json", created_by, date_entered, "komentarz" AS type FROM comments WHERE parent_id IN (SELECT id AS type FROM activities WHERE parent_id="'.$fv->id.'" ) ) A ORDER BY date_entered DESC');

        while ( $row = $db->fetchByAssoc($ret) ) {
            // echo '<br /><br />new row '.$row['json'].'<br /><br />';

            $text = explode(':',$row["json"]); $text[0] = '';
            $text = implode($text);
            $text = str_replace('"', '',$text);
            $text = str_replace('}','',$text);

            // echo '<br> e i 1 '.print_r($text, true);
            $text = explode(',',$text);
            // echo '<br> e i 2 '.print_r($text[0], true);
            $text = $text[0];
            while(strpos($text, '@[')){
                $text = explode(']', $text); $text[0] = '';
                $text = implode( $text);
            }

            $text = str_replace('"', '',$text);
            $text = str_replace('}','',$text);

            $komentarze.= $users[$row['created_by']].': '.$text.'<br />';
        }

        $this->ln();$this->ln();
        $this->Write(0, 'Komentarze', '', 0, 'L', true, 0, false, false, 0);
        $this->SetFont('dejavusans', '', 10);
        $this->writeHTML($komentarze, true, 0, true, true);
    }

    /**
     * Build filename
     */
    function buildFileName()
    {
        $this->fileName = 'example.pdf';
    }

    public function Output($name = 'upload/fv/doc.pdf', $dest='I')
    {
        if($_GET['fv']){
            $title = $_GET['fv'];
        } else { 
            $title = 'Brak faktury'; 
        }

        $name = 'upload/fv_desc/'.$_GET['fv'].'_invoice_desc_c';

        if ( $dest == 'I' || $dest == 'F') {
            ini_set('zlib.output_compression', 'Off');
        }

        return parent::Output($name,$dest);
    }

    public function Output2($name = 'upload/fv_desc/doc.pdf', $dest='I')  // I to wyświetlenie na stronie D to pobranie , f zapisuej na serwerze
    {
        $name = 'upload/fv_desc/'.$_GET['fv'].'_invoice_desc_c';

        if ( $dest == 'I' || $dest == 'F') {
            ini_set('zlib.output_compression', 'Off');
        }

        return parent::Output($name,$dest);
    }
    /**
     * This method draw an horizontal line with a specific style.
     */
    protected function drawLine()
    {
        $this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(220, 220, 220)));
        $this->MultiCell(0, 0, '', 'T', 0, 'C');
    }
}

$dd = new FAInvoicesDescription();
$dd->process();