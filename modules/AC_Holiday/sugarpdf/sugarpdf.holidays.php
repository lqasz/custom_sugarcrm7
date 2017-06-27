<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

ob_start();
include ('include/MVC/preDispatch.php');
require_once('include/entryPoint.php');
require_once('include/MVC/SugarApplication.php');

$app = new SugarApplication();
$app->startSession();
$app->execute();
ob_clean();

require_once('include/Sugarpdf/Sugarpdf.php');

class HD_HolidaySugarpdfurlopy extends Sugarpdf
{
    public $rok = 0;
    public $miesiac = 0;
    public $miesiace = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
    public $miesiaceLiczbowo = [1,2,3,4,5,6,7,8,9,10,11,12];
    public $swieta = array();

    /**
     * Override
     */
    function process() {
        $this->preDisplay();
        $this->buildFileName();
        $this->Output();
    }

    /**
     * Custom header
     */
    public function Header()
    {
        global $db;

        if((isset($_GET['month'])) && (isset($_GET['year']))) {
            if(($_GET['month'] == 0) && ($_GET['year'] == 0)) {
                $this->rok = date("Y");
                $this->miesiac = date("n");
            } else if(($_GET['month'] == 0) || ($_GET['year'] == 0)) {

                if($_GET['month'] == 0) $this->miesiac = (int)(date("n"));
                else $this->miesiac = $_GET['month'];

                if($_GET['year'] == 0)  $this->rok = (int)date("Y");
                else $this->rok = $_GET['year'];

            } else {
                $this->rok = $_GET['year'];
                $this->miesiac = $_GET['month']; 
            }

            $page = file_get_contents('http://www.kalendarzswiat.pl/swieta/wolne_od_pracy/'.$this->rok);
            $matches = array();

            preg_match_all('#data-date *= *["\']?([^"\']*)#is', $page, $matches);

            foreach ($matches[1] as $key => $value) {
                if(date("n", strtotime($value)) == $this->miesiac) {
                    $this->swieta[] = date("j", strtotime($value));
                } // end if
            } // end for
        } else {
            echo 'Podaj miesiąc i rok';
            die();
        } // end if
    }

    /**
     * Custom footer
     */
    public function Footer()
    {
        $this->SetFont(PDF_FONT_NAME_MAIN, '', 8);
        $this->MultiCell(0,0,'TCPDF Footeraa', 0, 'C');
    }

    /**
     * Predisplay content
     */
    function preDisplay()
    {
        $db = DBManagerFactory::getInstance();
        global $current_user;

        //Adds a predisplay page
        $this->AddPage();
        $this->SetFont('dejavusans', '', 8);
        $this->ln();        
        $this->MultiCell(0,0, $this->miesiace[$this->miesiac-1].' '.$this->rok.'r.' ,0,'C');
        $this->ln(); 

        $iloscDniMiesiaca = cal_days_in_month(CAL_GREGORIAN, $this->miesiac, $this->rok);

        $pomocnicza = array();
        $jakiDzien = array();
        $DniMiesiaca = array();

        $header = '<tr><th colspan="8">Pracownik</th>';
        $dzien = date('w',strtotime('01-'.$this->miesiac.'-'.$this->rok) );
        for($i=1; $i<=$iloscDniMiesiaca; $i++) {
            if($dzien == 7) $dzien = 0;
            $jakiDzien[$i] = $dzien;
            $dzien++;
        } // end for

        for($i=1; $i<=$iloscDniMiesiaca; $i++) {
            $DniMiesiaca[] = $i;
            $header .= '<th>'.$i.'</th>';
        } // end for

        $header .= '<th colspan="2">Suma</th></tr>';
        $ret = $db->query('SELECT users.id, users_cstm.id_c, users.first_name, users.last_name FROM users LEFT JOIN users_cstm ON (id=id_c) WHERE users_cstm.aggrement_c LIKE "%umowa_o_prace%" AND deleted=0 AND employee_status="Active" AND status="Active"');
        $tresc_tabeli = '';

        while($row = $db->fetchByAssoc($ret)) {
            
            $nowy_wiersz = '<tr>';
            $urlopy = $db->query('SELECT ac_holiday.id, ac_holiday.assigned_user_id, ac_holiday.v_from, ac_holiday.v_to, ac_holiday.board, 
                ac_holiday.supervisor, ac_holiday.sick_leave, ac_holiday_cstm.id_c, ac_holiday_cstm.withdrawal_of_leave_c FROM ac_holiday 
                LEFT JOIN ac_holiday_cstm ON (ac_holiday.id=ac_holiday_cstm.id_c) WHERE ac_holiday.assigned_user_id="'. $row['id_c'] .'" 
                AND (DATE_FORMAT(v_from,"%Y-%c") = DATE_FORMAT("'.$this->rok.'-'.$this->miesiac.'-'.$dzien.'","%Y-%c") OR 
                    DATE_FORMAT(v_to,"%Y-%c") = DATE_FORMAT("'.$this->rok.'-'.$this->miesiac.'-'.$iloscDniMiesiaca.'","%Y-%c") ) 
                AND (sick_leave=1 OR withdrawal_of_leave_c=1 OR (board=1 AND supervisor=1) ) AND deleted=0 ');

            $liczbaDniUrlopu = 0;
            if( $db->getRowCount($urlopy) > 0 ) {
                $urlopyWDniach = array();
                $choroboweWDniach = array();
                $odebraneWDniach = array();
                $dniRobocze = 0;

                while($row2 = $db->fetchByAssoc($urlopy) ){
                    $v_from = date("n", strtotime($row2['v_from']) );
                    $v_to = date("n", strtotime($row2['v_to']) );

                    $v_from_day = date("j", strtotime($row2['v_from']) );
                    $v_to_day = date("j", strtotime($row2['v_to']) );

                    // sprawdzenie czy urlop konczy sie w kolejnym siesiacu
                    if( $v_to > $this->miesiac){ $v_to_day = $iloscDniMiesiaca; }

                    // sprawdzenie czy urlop zaczyna sie w poprzednim miesiacu
                    if( $v_from < $this->miesiac){ $v_from_day = 1; }
                    //echo $v_from_day . ' ' . $v_to_day . ' <br /><br />';

                    if( $row2['sick_leave'] == 1) {
                        for($i=$v_from_day;$i<=$v_to_day;$i++){
                           if( !in_array($i, $choroboweWDniach) ){
                            $choroboweWDniach[] = $i;
                           } // end if 
                        } // end for
                    }else if( $row2['withdrawal_of_leave_c'] == 1){
                        for($i=$v_from_day;$i<=$v_to_day;$i++){
                           if( !in_array($i, $odebraneWDniach) ){
                            $odebraneWDniach[] = $i;
                           } // end if
                        } // end for
                    }else{
                        for($i=$v_from_day;$i<=$v_to_day;$i++){
                           if( !in_array($i, $urlopyWDniach) ){
                            $urlopyWDniach[] = $i;
                           } // end if
                        } // end for
                    } // end if
                } // end while

                $nowy_wiersz .= '<td  colspan="8">'.$row['first_name']. ' ' . $row['last_name'] . '</td>';

                for($i=1; $i<=$iloscDniMiesiaca; $i++) {
                    if( in_array($i, $urlopyWDniach) ){
                        if( ($jakiDzien[$i]==6 || $jakiDzien[$i]==0) || in_array($i, $this->swieta) ){
                                $nowy_wiersz .= '<td bgcolor="#CCC" align="center">W</td>';
                                $pomocnicza[$i] = 0;
                        }else{
                            $nowy_wiersz .= '<td align="center">1</td>';
                            $liczbaDniUrlopu++;
                            $pomocnicza[$i] = 1;
                        } // end if
                    }elseif( in_array($i, $choroboweWDniach) ){
                        if( ($jakiDzien[$i]==6 || $jakiDzien[$i]==0) || in_array($i, $this->swieta) ){
                            $nowy_wiersz .= '<td bgcolor="#CCC" align="center">W</td>';
                            $pomocnicza[$i] = 0;
                        }else{
                            $nowy_wiersz .= '<td align="center" color="#FF0123">Z</td>';
                            $pomocnicza[$i] = 1;
                        } // end if
                    }elseif( in_array($i, $odebraneWDniach) ){
                        if( ($jakiDzien[$i]==6 || $jakiDzien[$i]==0) || in_array($i, $this->swieta) ){
                            $nowy_wiersz .= '<td bgcolor="#CCC" align="center">W</td>';
                            $pomocnicza[$i] = 0;    
                        }else{
                            $nowy_wiersz .= '<td align="center" color="#AA0123">O</td>';
                            $pomocnicza[$i] = 1;
                        } // end if
                    }else{    
                        if( ($jakiDzien[$i]==6 || $jakiDzien[$i]==0) || in_array($i, $this->swieta) ){
                            $nowy_wiersz .= '<td bgcolor="#CCC" align="center" >W</td>';
                            $pomocnicza[$i] = 0;   
                        }else{
                            $nowy_wiersz .= '<td></td>';
                            $pomocnicza[$i] = 1;
                        } // end if
                    } // end if
                } // end for
                
                $nowy_wiersz .= '<td  colspan="2" align="center">'.$liczbaDniUrlopu.'</td></tr>';

            }else{
                // jeżeli nie ma urlopów 
                $nowy_wiersz .= '<td  colspan="8">'.$row['first_name']. ' ' . $row['last_name'] . '</td>';
                for($i=1; $i<=$iloscDniMiesiaca; $i++) {
                    if( ($jakiDzien[$i]==6 || $jakiDzien[$i]==0) || in_array($i, $this->swieta) ) {
                        $nowy_wiersz .= '<td bgcolor="#CCC" align="center">W</td>';
                        $pomocnicza[$i] = 0;   
                    }else{
                        $nowy_wiersz .= '<td></td>';
                        $pomocnicza[$i] = 1;
                    } // end if
                } // end for
                $nowy_wiersz .= '<td  colspan="2"  align="center" >0</td></tr>';
            } // end if

            $tresc_tabeli .= $nowy_wiersz;
        } // end while

        if((isset($_GET['pdf'])) && ($_GET['pdf'] == 1)) {
            echo json_encode($tresc_tabeli);
            die();
        } else {
            $html = '<table border="1" cellpadding="1">';
            $html .= $header;
            $html .= $tresc_tabeli;
            $html .= '</table>';

            $this->writeHTML($html, true, 0, true, true);
            $this->lastPage();
        } // end if
    }

    /**
     * Build filename
     */
    function buildFileName()
    {
        $this->fileName = 'urlopy.pdf';
    }

    /**
     * 
     */
    public function Output($name='upload/fv/doc.pdf', $dest='I')
    {
        $title = $miesiac;
        $name=$title;

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

$dd = new HD_HolidaySugarpdfurlopy();
$dd->process();