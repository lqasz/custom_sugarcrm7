<?php
// error_reporting(E_ALL ^ E_DEPRECATED);
// ini_set("display_errors", 1);

/*
    zwykła faktura:
        AS:
            - current = pm/sv/sj i pole qs nie kliknięte => zwraca fakturę do qs z notyfikacją na temat zwrotu
        QS:
            - numer pakietu
            - lista fcp
            - pola n/a - jeżeli wszystkie to nie robi nic else agnieszka
            - wszystko co powyzej plus qs akcept -> pm
            - jezeli qs = pm to idzie do sv
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class InvoiceCustomLogic
{
    // Static final id's
    const SYLWIA = '137a88d7-df78-8f89-9c4c-540f4ad585e4';
    const MALGOSIA = '4f8dce84-18ed-b30b-9658-5807486740a0';
    const AGNIESZKA = '9122d6b9-46e5-9013-99f7-540f4beb464e';
    const MILENA = '3437dc50-5512-c6ef-38f3-57fcfa6c6394';
    const JAKUB = '144c39bf-ccc3-65ec-2023-5407f7975b91';
    const ARTUR = 'e07026a9-691a-67e7-32a6-5407f619ae5b';
    const OLGA = '85ac3697-84bc-9400-07f9-5770d2e0c12e';
    const TOMASZ = 'd8c6bac7-eb79-5cc3-6580-5770ce45b719';

    public $pQS = '';
    public $pPM = '';
    public $pSV = '';
    public $current_user = '';
    public $project_name = '';
    public $pOwnerUnknown = array();
    public $na_array = array(   "agreement_na_c" => 'ac_invoices_notes_5',
                                "warranty_na_c" => 'ac_invoices_notes_2',
                                "s_safety_na_c" => 'ac_invoices_notes_4',
                                "work_completed_na_c" => 'ac_invoices_notes_3'
                            );
    private $accepts_array = array("array1_c","array2_c","array3_c","array4_c");

    public $project = null;

    public function loadProject(&$bean){
        if(!empty($bean->project_id_c) && empty($this->project) ) {
            // czy to się kiedykolwiek robi?!!!
          $GLOBALS['log']->fatal('1e. Wyciągnięcie projektu z pola `project_id_c`');
          $this->project = BeanFactory::retrieveBean('Project', $bean->project_id_c , array('disable_row_level_security' => true));
          $GLOBALS['log']->fatal($this->project->id);
        }
    }
    /*
     * Function gives name to the invoice in case of invoice type
     * F-UP unproject invoice
     * F-B board invoice
     * F-MULTI multiproject inovoice
     * F-NW no owner invoice
     * Normal invoice F-(M - mulit)-(project number)-(package number)-(autoinc number)
    */
    public function updateInvoiceName(&$bean, $arguments)
    {
        $GLOBALS['log']->fatal('1d. funkcja updateInvoiceName, nazwa: '.$bean->name);

        $invoice_name = '';
        $db = DBManagerFactory::getInstance();

        $this->loadProject($bean);

        if($bean->without_project_c == true) { $invoice_name .= 'F-UP-'. $bean->invoice_no_c;
            $GLOBALS['log']->fatal(' - generowanie nazwy faktury bezprojektowej');}
        elseif($bean->board_invoice_c == true) { $invoice_name .= 'F-B-'. $bean->invoice_no_c;
            $GLOBALS['log']->fatal(' - generowanie nazwy faktury boardowej ');
            // poprawa pól do usuniecia w listopadzie
            $bean->agreement_na_c=1;
            $bean->warranty_na_c=1; $bean->s_safety_na_c=1;$bean->work_completed_na_c=1;
            $bean->na_all_c=1;$bean->accept1_c=1;$bean->accept2_c=1;
        }elseif($bean->multiproject_c == true && $bean->multiproject_part_c == false) { $invoice_name .= 'F-MULTI-'. $bean->invoice_no_c;
        $GLOBALS['log']->fatal('- generowanie nazwy faktury multiprojektowe głównej');
        } elseif($bean->owner_unknown_c == true) {
          $invoice_name .= 'F-NW';
          $GLOBALS['log']->fatal(' - generowanie nazwy faktury NW ');
        } else {
            $GLOBALS['log']->fatal('- generowanie nazwy zwykłej faktury ');
            //////////////zbieranie do tablicy i na koncu implode////////////////////////////
            $invoice_name .= 'F-'. $this->project->project_number_c;

                if (!isset($bean->fetched_row['id'])){
                    $GLOBALS['log']->fatal('1f. Nowy rekord');

                }elseif($bean->fetched_row['package_no_c'] != $bean->package_no_c) {
                    $GLOBALS['log']->fatal('1g. Update pola `package_no_c`');

                    if(!empty($bean->package_no_c)){
                        $GLOBALS['log']->fatal('1h. Wartość pola `package_no_c` jest zmieniona, ale nie usunięta');
                        $invoice_name .= '-'.$bean->package_no_c; // dodajemy numer zakresu do nazwy
                        // wczytanie ilości faktur z tego projektu i z tego zakresu
                        $qtask = $db->query("SELECT COUNT(*) as 'fv_z_zakresu'
                        FROM  ac_invoices i
                            LEFT JOIN ac_invoices_cstm ic ON (i.id=ic.id_c)
                        WHERE
                            i.deleted=0 AND ic.project_id_c LIKE  '{$this->project->id}' AND ic.package_no_c={$bean->package_no_c} AND ic.multiproject_c=0");

                        $rtask = $db->fetchByAssoc($qtask);
                        $GLOBALS['log']->fatal('1i. Zmiana nazwy faktury na '.$invoice_name );
                        // sprawdzamy przed zapisaniem faktury
                        $rtask['fv_z_zakresu']++;
                        $invoice_name .= ($rtask['fv_z_zakresu']>1) ? '-'.$rtask['fv_z_zakresu']:"";
                        $GLOBALS['log']->fatal('1j. Zmiana nazwy faktury na '.$invoice_name );
                    }else{
                        $GLOBALS['log']->fatal('1k. Usunięta wartość pola `package_no_c`, zmianna nazwy faktury na "F-numer_projektu"');
                        $invoice_name .= 'F-'. $this->project->project_number_c;
                    }

                }else{
                    $GLOBALS['log']->fatal('1l. Update bez zmiany wartości pola `package_no_c`');
                    $invoice_name.= (!empty($bean->package_no_c)) ? '-'. $bean->package_no_c : "";
                }

            } // if rozróżniający faktury

        str_replace("/", "-", $invoice_name);
        $GLOBALS['log']->fatal(" - INVOICE - Nazwa faktury ". $invoice_name);
        $bean->name = $invoice_name;
    }

    ////////////////////////
    // Main before save function
    ////////////////////////////////////////////////////////////
    public function invoiceProcessing($bean, $event, $arguments)
    {
        // if ($event == 'before_save') {
        // if action => import - do nothing
        if($bean->import != true) {

            $GLOBALS['log']->fatal('----------------------------------------------------------------------------------------');
            $GLOBALS['log']->fatal('1. BEFORE SAVE - funkcja invoiceProcessing');
            $GLOBALS['log']->fatal("1a. Przed zapisem faktury");
            if (!empty($bean->customid_c)) {
                $bean->new_with_id = true;
                $bean->id = $bean->customid_c;
            } else {
                // echo 'Zapomiałaś dodać dokumentu do faktury';
                $GLOBALS['log']->fatal('1b. Nie dodano dokumentu do faktury');
                // die();
            }

            $GLOBALS['log']->fatal('1c Wylistowanie argumentów:');
            $GLOBALS['log']->fatal(print_r($arguments, true));
            // Gets current user id
            // #PHP_current_user
            $this->current_user = $GLOBALS['current_user'];

            // 2a. now we update invoice name
            $this->updateInvoiceName($bean, $arguments);
            // 2b. checkInvoice - Check users responsibility for this invoice

            if ($arguments['isUpdate'] != 1) {
                $GLOBALS['log']->fatal('1ł Nowy rekord, sprawdzenie faktury');
                $this->checkInvoice($bean);
                $GLOBALS['log']->fatal('1n - Użytkownicy: QS '. $this->pQS .' PM '. $this->pPM .' SV '. $this->pSV);

                $this->newInvoice($bean); // new record
            } else {
                $GLOBALS['log']->fatal('1u. Zmiany w zapisywanej fakturze');
            }
        }
    }

    /*
     * Function fetch responsible people in case of invoice type
     * if project invoice then
     *      get project qs, pm, sv
     *  else if multiproject invoice then
     *  else if invoice without owner then
     *      fetch all active users to the array
     *  else if without prject invoice then
     *      qs/pm = unproject pm
     *      sv = JJ
     *  else if board invoice then
     *      sv = JJ or AW
    */
    public function checkInvoice(&$bean)
    {
        $GLOBALS['log']->fatal('1m./3a BEFORE lub AFTER SAVE - funkcja checkInvoice');
        $this->loadProject($bean);

        if ($bean->multiproject_c == true && $bean->multiproject_part_c == false) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - multiproject głównej');
        } elseif ($bean->multiproject_c == true && $bean->multiproject_part_c == true) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - multiproject part');
            $this->pPM = $this->project->user_id_c;
            $this->pQS = $this->project->user_id1_c;
            $this->pSV = $this->project->user_id2_c;
        } elseif (!empty($this->project)) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - projektowej');
            $this->pPM = $this->project->user_id_c;
            $this->pQS = $this->project->user_id1_c;
            $this->pSV = $this->project->user_id2_c;
        } elseif ($bean->owner_unknown_c == true) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - owner unknown');
            // wybranie wszystkich użytkowników z dostępem do faktur
            $db = DBManagerFactory::getInstance();
            $sql = $db->query('SELECT `user_id` FROM `acl_roles_users`
                WHERE `role_id` IN ("bbe63021-b698-35a8-0eb5-5722a7be5366", "5207bc65-047d-bc16-6e9a-5722a6feae93")
                    AND `user_id` NOT IN (
                        SELECT `aa_departments_users_1users_idb` FROM `aa_departments_users_1_c`
                            WHERE `aa_departments_users_1aa_departments_ida` = "8b636633-dae5-6af9-15ae-56a5b82126b7" )');
            // zebranie wszystkich osob, ktore oczekują na powiadomienie o NW
            while($row = $db->fetchByAssoc($sql)) {
                $this->pOwnerUnknown[] = $row['user_id'];
            }
            // w ramach porządku usunięcie wartości QS PM SV,
            $this->pPM='';$this->pQS='';$this->pSV='';

        } elseif ($bean->without_project_c == true) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - without project');
            $this->pPM = $bean->user_id_c; // unproject_pm_c - to pole z imieniem i nazwiskiem
            $this->pQS = $bean->user_id_c;
            $this->pSV = self::JAKUB;
        } elseif ($bean->board_invoice_c == true) {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - board invoice/uzupełnienie pól');
            if (trim($bean->board_member_c) == "jakub") {
                $this->pSV = self::JAKUB;
            } else {
                $this->pSV = self::ARTUR; }

            $bean->agreement_na_c=1;
            $bean->warranty_na_c=1; $bean->s_safety_na_c=1;$bean->work_completed_na_c=1;
            $bean->na_all_c=1;$bean->accept1_c=1;$bean->accept2_c=1;


        } else {
            $GLOBALS['log']->fatal(' - SPRAWDZENIE POPRAWNOŚCI - reszta');
        }
    }

    /*
     * Function sets stage to every invoice type
     * and add notification to AG
    */
    public function newInvoice(&$bean)
    {
        $GLOBALS['log']->fatal('1o. BEFORE SAVE - funkcja newInvoice');
        // $GLOBALS['log']->fatal('Dane do newInvoice2 '.$bean->last_invoice_c.' '.$bean->multiproject_c.' pojedynczy '.$bean->project_id_c);
        // nie wiem ale $bean->project_id_c ma dane z 1_c, netto się zgadza

        $stages = array();
        $stages[] = 'AG';
        $bean->description = "Proszę o uzupełnienie informacji o LCC i dopisanie samej faktury do LCC.";

        if ($bean->last_invoice_c) {
            $GLOBALS['log']->fatal(' - INVOICE - Ostatnia faktura częściowa'); // projektowa
            $stages[] = 'add_normal_invoice'; // etap startowy
        } elseif ($bean->multiproject_c == true && $bean->multiproject_part_c == false) { //
            $GLOBALS['log']->fatal(' - INVOICE - Multiprojekt'); // multi projektowa - zwykła
            $stages[] = 'add_normal_invoice'; // etap startowy
        } elseif ($bean->multiproject_c == true && $bean->multiproject_part_c == true) {
            $GLOBALS['log']->fatal(' - INVOICE - Multiprojekt part'); // multi projektowa - zwykła
            $stages[] = 'add_normal_invoice'; // etap startowy
            $stages[] = 'MI';$stages[] = 'toQS';
        } elseif ($bean->without_project_c) {
            $GLOBALS['log']->fatal(' - INVOICE - Without Project'); // bez projektowa
            $stages[] = 'add_normal_invoice'; // etap startowy
            $stages[] = 'toQS';
        } elseif($bean->owner_unknown_c == true) {
          $GLOBALS['log']->fatal(' - INVOICE - faktura NW'); // bordowa
          $stages[] = 'add_nw_invoice'; // etap startowy
          $stages[] = 'AG';$stages[] = 'MI';$stages[] = 'toQS';
        } elseif ($bean->part_invoice_c) {
            $GLOBALS['log']->fatal(' - INVOICE - Part Invoice'); // projektowa partowa
            $stages[] = 'add_normal_invoice'; // etap startowy
            $stages[] = 'AG';$stages[] = 'MI';$stages[] = 'toQS';
        } elseif ($bean->board_invoice_c) {
            $GLOBALS['log']->fatal(' - INVOICE - Board Invoice'); // bordowa
            $stages[] = 'add_board_invoice'; // etap startowy
            $stages[] = 'AG';$stages[] = 'MI';$stages[] = 'toSV';
        } elseif (!empty($bean->project_id_c) && $bean->nett_c>0) {
            $GLOBALS['log']->fatal(' - INVOICE - Normal Invoice'); // projektowa
            $stages[] = 'add_normal_invoice'; // etap startowy
            $stages[] = 'AG';$stages[] = 'MI';$stages[] = 'toQS';
        } else {
            $GLOBALS['log']->fatal(' - BŁĄD (newInvoice) - Brak możliwości skategoryzowania tej faktury');
        }

        $GLOBALS['log']->fatal('1p BEFORE SAVE - Dodano etapy (stages) dla nowej faktury');
        $this->addNotification($bean, $stages);
    }

    ////////////////////////
    // Main after save function
    ////////////////////////////////////////////////////////////
    public function invoiceProcessingAfterSave($bean, $event, $arguments)
    {
        // if action => import - do nothing
        if($bean->import != true) {
            $this->current_user = $GLOBALS['current_user'];
            // $GLOBALS['log']->fatal('3 AfterSave');
            // $GLOBALS['log']->fatal(print_r($arguments, true));

            /*
             * if it isn`t new invoice then
             *      if something had been changed then
             *           go to update invoice function
             * else then
             *      update document relationships
            */
            if($arguments['isUpdate'] == 1 || ($bean->multiproject_c == true && $bean->multiproject_part_c == true && $arguments['isUpdate'] == 1)) {
                $GLOBALS['log']->fatal('- ETAP - Update w AFTER SAVE');
                if(isset($arguments['dataChanges']) ){
                    $GLOBALS['log']->fatal('- ETAP - Zmiany:');
                    $GLOBALS['log']->fatal($arguments);
                    $this->updateInvoice($bean, $arguments['dataChanges']);
                } else {
                    $GLOBALS['log']->fatal('- ETAP - Faktura nie została zmieniona');
                }
            } else {
                if(($bean->multiproject_c == true) && ($bean->multiproject_part_c == false)) {
                    $db = DBManagerFactory::getInstance();

                    $invoice_count = $db->query("SELECT `id` FROM `ac_invoices_ac_invoices_1_c`
                                                WHERE `ac_invoices_ac_invoices_1ac_invoices_ida` = '{$bean->id}' AND `deleted` = 0");

                    if($db->getRowCount($invoice_count) == 0) {
                        $bean->load_relationship('ac_invoices_ac_invoices_1');

                        $GLOBALS['log']->fatal('- ETAP - Multiprojectowa faktura dodanie faktur partowych');
                        for($i = 0; $i < 7; $i++) {
                            $j = ($i == 0) ? "" : $i;
                            $related_project = "project_id".$j."_c";

                            if(!empty($bean->{$related_project})) {
                                $invoice_bean = BeanFactory::newBean("AC_Invoices", array('disable_row_level_security' => true));
                                $invoice_bean->new_with_id = true;
                                $invoice_bean->id = create_guid();
                                $invoice_bean->project_id_c = $bean->{$related_project};
                                $invoice_bean->nett1_c = $bean->{'nett'.($i+1).'_c'};
                                $invoice_bean->invoice_no_c = $bean->invoice_no_c;
                                $invoice_bean->date_of_issue_c = $bean->date_of_issue_c;
                                $invoice_bean->gross_c = number_format($invoice_bean->nett1_c * $bean->vat_c, 2);
                                $invoice_bean->vat_c = $bean->vat_c;
                                $invoice_bean->multiproject_c = true;
                                $invoice_bean->multiproject_part_c = true;
                                $invoice_bean->account_id_c = $bean->account_id_c;
                                $invoice_bean->save();

                                $bean->set_relationship("ac_invoices_ac_invoices_1_c", array('ac_invoices_ac_invoices_1ac_invoices_ida' => $bean->id ,'ac_invoices_ac_invoices_1ac_invoices_idb' => $invoice_bean->id), true, true);
                                unset($invoice_bean);
                                $invoice_bean = null;
                            }
                        }
                    }
                }

                // update document relationships
                $this->updateDocumentRelation($bean);
            }
        }
    }

    /*
     * Function returns current stage of invoice accepts
    */
    public function whichStage(&$bean, $arguments)
    {
        $stages = array();
        $db = DBManagerFactory::getInstance();
        $GLOBALS['log']->fatal('3b. AFTER SAVE - funkcja Which stage');
        // $GLOBALS['log']->fatal('a. get current user');
        $this->current_user = $GLOBALS['current_user'];

        // sprawdzenie kim jest zalogowany uzytkownik
        if($this->current_user->id == $this->pPM) { $stages[] = 'PM'; }
        if($this->current_user->id == $this->pQS) { $stages[] = 'QS'; }
        if($this->current_user->id == $this->pSV) { $stages[] = 'SV'; }
        if($this->current_user->id == self::JAKUB) { $stages[] = 'JJ'; }
        if($this->current_user->id == self::ARTUR) { $stages[] = 'AW'; }
        if($this->current_user->id == self::SYLWIA) { $stages[] = 'SLW'; }
        if($this->current_user->id == self::MALGOSIA) { $stages[] = 'MAL'; }
        if($this->current_user->id == self::AGNIESZKA) { $stages[] = 'AG'; }
        if($this->current_user->id == self::MILENA || $this->current_user->id == self::OLGA || $this->current_user->id == self::TOMASZ) { $stages[] = 'MI'; }

        // jeżeli faktura potwierdzana przez nieuprawnioną osobę
        if(count($stages)==0 && (!empty($arguments['owner_unknown_c'])) && ($arguments['owner_unknown_c']['before']==0 && $arguments['owner_unknown_c']['after']==0)){
            $GLOBALS['log']->fatal(' - ETAP - Nieuprawniony zapis faktury'.print_r($stages, true));
            $GLOBALS['log']->fatal("Zmiany: ");
            $GLOBALS['log']->fatal($arguments);

            $bean->import=true;
            foreach($arguments as $key => $value) {
                $bean->$key = $value['before'];
            }
            $bean->save();
            exit(0);
        } else if((!empty($arguments['owner_unknown_c'])) && $arguments['owner_unknown_c']['before']==1 && $arguments['owner_unknown_c']['after']==0) {
            $GLOBALS['log']->fatal(' - ETAP - NW staje się normalną fakturą usunięcie notyfikacji');
            $db = DBManagerFactory::getInstance();
            $db->query("UPDATE `notifications` SET `is_read` = 1, `deleted` = 1 WHERE `parent_id`='{$bean->id}'");
            $GLOBALS['log']->fatal('3c. Dodanie jako nowa faktura');
            $this->newInvoice($bean);
        }
        // $GLOBALS['log']->fatal('INVOICE - People: QS: '. $this->pQS .' PM: '. $this->pPM .' SV: '. $this->pSV);
        //--- spr co się stało

        // dodanie do argumentów dodatkowej akcji kiedy agnieszka potwierdza fakturę po dodaniu wszystkich dokumentów
        if(  (in_array('MI', $stages) || in_array('AG', $stages)) && $bean->accept1_c == 1 && empty($bean->accept2_c) ){
            $arguments['accept1_c']['before']=0;
            $arguments['accept1_c']['after']='agnieszka';
        }

        // poprawa starcyh boardowych faktur do usunięcia w listopadzie
        if( (in_array('AG', $stages) || in_array('MI', $stages)) && $bean->board_invoice_c==1 && $bean->accept1_c==0 ){
                            // aktualizowana faktura boardowska przez AG lub MI dla poprawnego działania zle dodanych faktur
                            $stages[] = 'toSV';$stages[] = 'add_board_invoice';
        }

        foreach($arguments as  $key => $value) {
            $GLOBALS['log']->fatal(' - ETAP - Analizowanie pole: '.$key);
            switch ($key) {
                    case 'accept1_c' :
                        $GLOBALS['log']->fatal('accept1_c');
                        $GLOBALS['log']->fatal('Stages: '.print_r($stages, true).' Wartość przed zmianą: '.$value['before']);

                            if($value['before'] == 0 &&
                                (   in_array('QS', $stages) ||
                                    (in_array('PM', $stages) && $bean->without_project_c==1) ||
                                    $value['after']=='agnieszka'
                                )
                            ){
                                $GLOBALS['log']->fatal('Zaakceptowany akcept1_c');

                                    // jeżeli brak dokumentów
                                    if(($bean->agreement_na_c == 1 && $bean->warranty_na_c == 1 && $bean->s_safety_na_c == 1 &&
                                        $bean->work_completed_na_c == 1) || $bean->na_all_c==1 ) {

                                        $GLOBALS['log']->fatal(' - ETAP - Notyfikacja dla PMa lub SVa');
                                        if($this->pPM == $this->pQS) { $stages[] = 'toSV';$stages[] = 'acceptedByPM&QS';$stages[] = 'acceptedByQS'; } else { $stages[] = 'toPM';$stages[] = 'acceptedByQS'; }
                                    } else {
                                        $GLOBALS['log']->fatal(' - ETAP - Sprawdzenie czy wszystkie dokumenty są uzupełnione ');
                                        $GLOBALS['log']->fatal(' - ETAP - N/A wartości: '.print_r($this->na_array,true) );
                                        $to_AG = false;

                                        foreach ($this->na_array as $na => $relation) {

                                            if(!$bean->$relation){  $bean->load_relationships($relation); }

                                            $fff =$bean->$relation->getBeans();
                                            $GLOBALS['log']->fatal(' - ETAP - sprawdzenie wartości '.print_r($fff) );
                                            // brak dokumentow ktore nie mają NA zaznaczonego na checkboxie
                                            if($bean->$na == 0 && count($fff) == 0 || $bean->$na == 1 && count($fff) == 1) {
                                                $to_AG = true;
                                            }
                                        }

                                        if($to_AG == true) { $stages[] = 'toAG';$stages[] = 'addDocuments';
                                            $GLOBALS['log']->fatal(' - ETAP - etap: toAG ');
                                        }
                                        else {
                                            $GLOBALS['log']->fatal(' - ETAP - etap: SV lub PM ');
                                            if($this->pPM == $this->pQS) { $stages[] = 'toSV';$stages[] = 'acceptedByPM&QS';$stages[] = 'acceptedByQS'; }
                                            else { $stages[] = 'toPM';$stages[] = 'acceptedByQS'; }
                                        }
                                    }
                            }elseif($value['before'] == 1){
                                $stages[] = 'rejected';
                                $bean->accept2_c = false;
                                $bean->accept3_c = false;
                                $bean->accept4_c = false;
                                if(in_array('QS', $stages)){ $stages[] = 'rejectedByQS';$stages[] = 'toAG';
                                }elseif( in_array('AG', $stages) ){ $stages[] = 'rejectedByAG';$stages[] = 'toQS';
                                }elseif( in_array('PM', $stages) && $bean->without_project_c==1){ $stages[] = 'rejectedByQS&PM';$stages[] = 'toQS';
                                }elseif( in_array('PM', $stages) ){ $stages[] = 'rejectedByPM';$stages[] = 'toQS';
                                }elseif( in_array('SV', $stages) ){ $stages[] = 'rejectedBySV';$stages[] = 'toQS';
                                }elseif( in_array('SLW', $stages) ){ $stages[] = 'rejectedBySLW';$stages[] = 'toQS';
                                }elseif( in_array('MAL', $stages) ){ $stages[] = 'rejectedByMAL';$stages[] = 'toQS'; }
                            }else{
                                // zmiana na niewiadomo co
                                $GLOBALS['log']->fatal(' - ETAP - Nie wiadomo jaka wartość '.print_r($value['before'], true) );
                            }
                        break;
                    case 'accept2_c' :
                        $GLOBALS['log']->fatal('accept2_c');
                        if($value['before'] == 0 && in_array('PM', $stages)){ // zaakceptowane
                            $stages[] = 'toSV';$stages[] = 'PMok';
                        }elseif( $value['before'] == 0 && (in_array('AG', $stages) || in_array('MI', $stages)) ){
                            // aktualizowana faktura boardowska przez AG lub MI dla poprawnego działania zle dodanych faktur
                            $stages[] = 'toSV';$stages[] = 'add_board_invoice';
                        }elseif($value['before'] == 1){
                                $stages[] = 'rejected';
                                $bean->accept3_c = false;
                                $bean->accept4_c = false;
                                if( in_array('PM', $stages) ){ $stages[] = 'PMrejectedByHimSelf';
                                    }elseif( in_array('SV', $stages) ){ $stages[] = 'rejectedBySV';$stages[] = 'toPM';
                                    }elseif( in_array('SLW', $stages) ){ $stages[] = 'rejectedBySLW';$stages[] = 'toPM';
                                    }elseif( in_array('MAL', $stages) ){ $stages[] = 'rejectedByMAL';$stages[] = 'toPM'; }
                        }else{
                            // zmiana na niezaakceptowane
                            $GLOBALS['log']->fatal('Nie wiadomo co');
                        }
                        break;
                    case 'accept3_c' :
                        if(!is_array($this->accepts_array) || !is_array($arguments) ){
                            break;
                        }

                        $GLOBALS['log']->fatal('accept3_c');
                        if($value['before'] == 0 && (in_array('SV', $stages) || in_array('SLW', $stages)) ){
                            // zaakceptowane
                            $stages[] = 'toSLW';$stages[] = 'SVok';
                        }elseif($value['before'] == 0 && (in_array('SV', $stages) || in_array('MAL', $stages)) ){
                            // zaakceptowane
                            $stages[] = 'toMAL';$stages[] = 'SVok';
                        }elseif( $value['before'] == 1 ){
                            $stages[] = 'rejected';
                            $bean->accept4_c = false;
                            if( in_array('SV', $stages) ){ $stages[] = 'SVrejectedByHimSelf';
                            }elseif( in_array('SLW', $stages) ){ $stages[] = 'rejectedBySLW';$stages[] = 'toSV';
                            }elseif( in_array('MAL', $stages) ){ $stages[] = 'rejectedByMAL';$stages[] = 'toSV';}
                        }else{
                            // zmiana na niezaakceptowane
                            $GLOBALS['log']->fatal('Nie wiadomo co');
                        }
                        break;
                    case 'accept4_c' :
                        $GLOBALS['log']->fatal('accept4_c');
                        if($value['before'] == 0 && in_array('SLW', $stages)){
                            // zaakceptowane
                            $stages[] = 'SLW';$stages[] = 'SLWok';
                            $bean->archived_c = 1;
                        }elseif($value['before'] == 0 && in_array('MAL', $stages)){
                            // zaakceptowane
                            $stages[] = 'MAL';$stages[] = 'MALok';
                            $bean->archived_c = 1;
                        }elseif( $value['before'] == 1 ){ $stages[] = 'rejected';
                            if( in_array('SLW', $stages) ){ $stages[] = 'SLWrejectedByHimSelf';$stages[] = 'toSLW';
                            }elseif( in_array('MAL', $stages) ){ $stages[] = 'MALrejectedByHimSelf';$stages[] = 'toMAL'; }
                        }else{
                            // zmiana na niezaakceptowane
                            $GLOBALS['log']->fatal('Nie wiadomo co');
                        }
                        break;
            }

            if($key == 'project_id_c') {
                $stages[] = 'changedProject';$stages[] = 'toQS';
            }
        } // koniec foreach po zmianach

        if(($bean->multiproject_c == true) && ($bean->multiproject_part_c == true) && ($bean->accept3_c == true)) {
            $GLOBALS['log']->fatal(' - ETAP - Multiprojektowa partowa i akcept 3');
            $accepts3 = true;
            $multi_id = "";
            $accept3_parts_query = $db->query("SELECT `id_c`, `accept3_c`, `multiproject_part_c`, `multiproject_c`
                                                    FROM `ac_invoices_cstm` LEFT JOIN `ac_invoices` ON(`id` = `id_c`)
                                                    WHERE `invoice_no_c` = '{$bean->invoice_no_c}'
                                                        AND `deleted` = 0 AND `account_id_c` = '{$bean->account_id_c}'");

            while($invoice = $db->fetchByAssoc($accept3_parts_query)) {
                if($invoice['accept3_c'] != true && $invoice['multiproject_part_c'] == true) {
                    $accepts3 = false;
                    break;
                } elseif ($invoice['multiproject_part_c'] == false && $invoice['multiproject_c'] == true) {
                    $multi_id = $invoice['id_c'];
                }
            }

            if($accepts3 == true) {
                $GLOBALS['log']->fatal(' - ETAP - Wszystkie multiprojektowe partowe mają akcept 3 zaznaczony, główna faktura multi też: '. $multi_id);
                $GLOBALS['log']->fatal("UPDATE `ac_invoices_cstm` SET `accept1_c` = 1, `accept2_c` = 1, `accept3_c` = 1 WHERE `id_c` = '{$multi_id}'");
                $db->query("UPDATE `ac_invoices_cstm` SET `accept1_c` = 1, `accept2_c` = 1, `accept3_c` = 1 WHERE `id_c` = '{$multi_id}'");
                $stages[] = "allMultipartAcceptedBySV";
            } else {
                $stages[] = "deleteNotificationToSLW";
            }
        } else if(($bean->multiproject_c == true) && ($bean->multiproject_part_c == false) && ($bean->accept4_c == true)) {
            $db->query("UPDATE `ac_invoices_cstm` SET `accept4_c` = 1 WHERE `invoice_no_c` = '{$bean->invoice_no_c}' AND `account_id_c` = '{$bean->account_id_c}' AND `date_of_issue_c` = '{$bean->date_of_issue_c}'");
            $stages[] = "multipartAcceptedByKsiegowa";
            $GLOBALS['log']->fatal(' - ETAP - Multiprojektowa ma akcept 4 zaznaczony, więc partowe też');
        }

        $GLOBALS['log']->fatal('3d. atrybuty do etapów: '.print_r($stages, true));
        return $stages;
    }

    public function updateDocumentRelation($bean)
    {
        $GLOBALS['log']->fatal('3e funkcja updateDocumentRelation');
        $notesRelationships = array(
            "invoice_scan_c" => "ac_invoices_notes_1",
            "invoice_agreement_c" => "ac_invoices_notes_5",
            "invoice_bhpbioz_c" => "ac_invoices_notes_4",
            "invoice_warranty_c" => "ac_invoices_notes_2",
            "invoice_work_completed_c" => "ac_invoices_notes_3"
        );

        foreach ($notesRelationships as $field => $relation) {
            $sugarQuery = new SugarQuery();
            $nr = substr($relation, 18, 1);

            if($bean->load_relationship($relation) ) {
                // fetch related to beans
                $relatedNotes = $bean->$relation->getBeans(); //------ jedno
                $relatedBeans = BeanFactory::newBean('Notes');
                $sugarQuery->select(array('id', 'name'));
                $sugarQuery->from($relatedBeans, array('team_security' => false));
                $sugarQuery->where()->queryAnd()->equals('parent_id', $bean->id)->equals('deleted', 0)->equals($field, 1);
                // $GLOBALS['log']->fatal($sugarQuery->compileSql());
                $notesRelatedToBean = $sugarQuery->execute('array');  // --- drugie
                // $GLOBALS['log']->fatal('relacje wczytane');
                // podzielić później na faktury i inne dokumneyt oddzielnie

                if (count($notesRelatedToBean) > 0 && count($notesRelatedToBean)!== count($relatedNotes)) {
                    // $GLOBALS['log']->fatal("WYLICZYLIŚMY WIĘCEJ JAK ZERO");
                    foreach ($notesRelatedToBean as $noteRelatedToBean) {
                        $ifExist = false;
                        foreach ($relatedNotes as $relatedNote) {
                            // $GLOBALS['log']->fatal('porownuje');
                            // $GLOBALS['log']->fatal($relatedNote->id);
                            // $GLOBALS['log']->fatal($noteRelatedToBean->id);

                            if ($relatedNote->id == $noteRelatedToBean['id']) {
                                $ifExist = true;
                                break;
                            }
                        }
                        if ($ifExist == false) {
                            $bean->set_relationship($relation."_c", array('ac_invoices_notes_'.$nr.'ac_invoices_ida'=>$bean->id ,'ac_invoices_notes_'.$nr.'notes_idb'=> $noteRelatedToBean['id']), true, true);
                            $GLOBALS['log']->fatal(' - ETAP - Dodanie ralacji '. $relation."_c" ." do faktury ". $bean->name);
                            // $contact->set_relationship('opportunities_contacts', array('contact_id'=>$contact->id ,'opportunity_id'=> $opportunity_ids[$opportunity_key], 'contact_role'=>$app_list_strings['opportunity_relationship_type_default_key']), false);
                        }
                    }
                }
            } else {
                $GLOBALS['log']->fatal(' - ETAP - Dodanie wszystkich relacji z dokumentami do faktury '. $bean->name);

                $relatedBeans = BeanFactory::newBean('Notes');
                $sugarQuery->select(array('id', 'name'));
                $sugarQuery->from($relatedBeans, array('team_security' => false));
                // dodać where by rozróżnić które do czego dokumenty są
                $sugarQuery->where()->queryAnd()->equals('parent_id', $bean->id)->equals('deleted', 0)->equals($field, 1);
                // $GLOBALS['log']->fatal($sugarQuery->compileSql());
                $notesRelatedToBean = $sugarQuery->execute();
                    // $GLOBALS['log']->fatal('relacje wczytane');
                if (count($notesRelatedToBean)>0) {
                    foreach ($notesRelatedToBean as $noteRelatedToBean) {
                        $bean->set_relationship($relation."_c", array('ac_invoices_notes_'. $nr .'ac_invoices_ida'=>$bean->id ,'ac_invoices_notes_'. $nr .'notes_idb'=> $noteRelatedToBean['id']), true, true);
                        // $GLOBALS['log']->fatal('dodalem relacje');
                    }
                }
            } //else if

            if(($bean->multiproject_c == true) && ($bean->multiproject_part_c == false)) {
                $GLOBALS['log']->fatal(' - ETAP - Dodanie relacji faktura faktura w multiprojektowej partowej fakturze');
                $db = DBManagerFactory::getInstance();

                $multipart_query = $db->query("SELECT `id_c` FROM `ac_invoices_cstm`
                                                    WHERE `multiproject_part_c` = 1 AND `invoice_no_c` = '{$bean->invoice_no_c}'");

                while($multipart_row = $db->fetchByAssoc($multipart_query)) {
                    $part_bean = BeanFactory::getBean('AC_Invoices', $multipart_row['id_c']);

                    foreach ($notesRelationships as $field => $relation) {
                        if($bean->load_relationship($relation) ) {
                            $nr = substr($relation, 18, 1);

                            $related_notes = $bean->$relation->getBeans();
                            $notes_related = $db->fetchByAssoc($db->query("SELECT `ac_invoices_notes_".$nr."notes_idb` FROM ". $relation."_c
                                                    WHERE `deleted` = 0 AND `ac_invoices_notes_".$nr."ac_invoices_ida` = '{$multipart_row['id_c']}'"));

                            if($db->getRowCount($note_related) == 0 && count($related_notes) > 0) {
                                $notes_array = array();
                                $part_bean->load_relationship($relation);

                                foreach ($related_notes as $note) {
                                    // $note_bean = BeanFactory::retrieveBean("Notes", $note->id, array('disable_row_level_security' => true));
                                    // $note_bean->new_with_id = true;
                                    // $note_bean->id = create_guid();
                                    // $note_bean->parent_id = $part_bean->id;
                                    // $note_bean->name = $part_bean->name;
                                    // $note_bean->save();
                                    $GLOBALS['log']->fatal(' - ETAP - set_relationship '.$relation."_c 'ac_invoices_notes_'". $nr ."'ac_invoices_ida' => ".$part_bean->id .",'ac_invoices_notes_'. ".$nr ."'notes_idb' => ".$note->id."  <br />");
                                    // $bean->set_relationship($relation."_c", array('ac_invoices_notes_'. $nr .'ac_invoices_ida'=>$bean->id ,'ac_invoices_notes_'. $nr .'notes_idb'=> $noteRelatedToBean['id']), true, true);
                                    $part_bean->set_relationship($relation."_c", array('ac_invoices_notes_'. $nr .'ac_invoices_ida' => $part_bean->id ,'ac_invoices_notes_'. $nr .'notes_idb' => $note->id), true, true);

                                    // unset($note_bean);
                                    // $note_bean = null;
                                }
                            }
                        }
                    }

                    unset($part_bean);
                    $part_bean = null;
                }
            }

            $sugarQuery = null;
            unset($sugarQuery);
        }
    }

    /*
     * Function adds notification to the AG
     * Stages AG | QS | PM | SV | SLW  -- is created by
    *         toAG | toQS | toPM | toSV | toSLW | SandP
    */
    public function addNotification(&$bean, $stages)
    {
        $ifSave = 0;
        $addForMilena = false;
        $db = DBManagerFactory::getInstance();

        $GLOBALS['log']->fatal('1r. Dodanie/Aktualizacja notyfikacji');

        $q="SELECT * FROM notifications n WHERE n.parent_id='{$bean->id}' AND (n.deleted=0 OR n.is_read=0) AND severity='invoice'";
        $r = $db->query($q);

            if($db->getRowCount($r) == 1){
                $GLOBALS['log']->fatal('- NOTIFICATIONS - Istnieje notyfikacja do tej faktury');
                $row = $db->fetchByAssoc($r);
                $notif = BeanFactory::getBean('Notifications', $row['id']);
            }elseif($bean->owner_unknown_c == true) {
                if($db->getRowCount($r)==0){
                  $GLOBALS['log']->fatal('- NOTIFICATIONS - Zapisywanie nowej notyfikacji do faktury NW');
                  foreach($this->pOwnerUnknown as $key=>$value){
                    $notif = BeanFactory::newBean('Notifications');
                    $notif->parent_type = 'AC_Invoices';
                    $notif->parent_id = $bean->id; $notif->severity = "invoice";
                    $notif->is_read = 0;
                    $notif->confirmation = 0; $notif->deleted = 0;
                    $notif->name = $bean->name; $notif->assigned_user_id = $value;
                    $notif->description = 'Proszę zobaczyć czy aby nie nalezy do ona do Ciebie?';
                    $notif->confirmation = 1;
                    $notif->save(); $notif = null; unset($notif);
                  }
                }else{
                    $GLOBALS['log']->fatal('- NOTIFICATIONS - Zapisywanie zmodyfikowanej notyfikacji do faktury NW');
                  $qq="UPDATE notifications n SET n.name='{$bean->name}' WHERE n.parent_id='{$bean->id}' AND (n.deleted=0 OR n.is_read=0)";
                  $rr = $db->query($qq);
                }

                return true;
            }elseif($db->getRowCount($r) > 10){
              $GLOBALS['log']->fatal('- NOTIFICATIONS - Update notyfikacji na NW przy duzej ich liczbie w bazie dnaych');
              $qq="UPDATE notifications n SET n.name='{$bean->name}' WHERE n.parent_id='{$bean->id}' AND (n.deleted=0 OR n.is_read=0)";
              $rr = $db->query($qq);


                exit(0);
            }elseif($db->getRowCount($r) > 1){
                $GLOBALS['log']->fatal('- NOTIFICATIONS - Istnieje więcej niż 1 notyfikacja, usunięcie notyfikacji przypisanej do tego rekordu, stworzenie nowej');

                $qq="UPDATE notifications n SET n.deleted=1, n.is_read=1 WHERE n.parent_id='{$bean->id}' AND (n.deleted=0 OR n.is_read=0)";
                $rr = $db->query($qq);

                $notif = BeanFactory::newBean('Notifications');
            }else{
                $GLOBALS['log']->fatal('- NOTIFICATIONS - Stworzenie nowej notyfikacji');
                $notif = BeanFactory::newBean('Notifications');
            }

            $notif->parent_type = 'AC_Invoices'; $notif->parent_id = $bean->id; $notif->severity = "invoice";$notif->is_read=0; $notif->confirmation = 0; $notif->deleted = 0; $notif->name = $bean->name;
            $GLOBALS['log']->fatal("*********************************************************");
            $GLOBALS['log']->fatal(print_r($notif, true));
        // modyfikujemy tylko assign user name i description notyfikacji, faktura dostosuje się do tego
        /***********************************************************/
        $GLOBALS['log']->fatal('1s. Zarejestrowane etapy: '.print_r($stages, true));
        /***********************************************************/
        if (in_array('add_normal_invoice', $stages) || in_array('changedProject', $stages)){
            if (in_array('AG', $stages) || in_array('MI', $stages)) {
                if (in_array('toQS', $stages)) {
                    $notif->assigned_user_id = $this->pQS;
                    $notif->description = 'Proszę o uzupełnienie informacji o LCC i dopisanie samej faktury do LCC.';
                    $ifSave=1;
                }
            }
        }elseif( in_array('add_board_invoice', $stages) ){
            if (in_array('AG', $stages) || in_array('MI', $stages)) {
                if (in_array('toSV', $stages)) {
                    $notif->assigned_user_id = $this->pSV;
                    $notif->description = 'Faktura boardowa, prosimy o zaakceptowanie.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }
            }
        }
        /***********************************************************/
        if (in_array('addDocuments', $stages)){
             $GLOBALS['log']->fatal(' - ETAP - Uzupełnienie wymaganych dokumentów');
            if (in_array('QS', $stages)) {
                if (in_array('toAG', $stages)) {
                    $notif->assigned_user_id = self::AGNIESZKA;
                    $notif->description = 'Proszę o uzupełnienie wymaganych dokumentów.';
                    $ifSave=1;
                }
            }elseif(in_array('AG', $stages)){
                if (in_array('toAG', $stages)) {
                    $notif->assigned_user_id = self::AGNIESZKA;
                    $notif->description = 'Agnieszko sprawdź raz jeszcze czy poprawnie wszystko uzupełniłaś';
                    $ifSave=1;
                }
            }
        }
        /***********************************************************/
        if (in_array('acceptedByQS', $stages)){
             $GLOBALS['log']->fatal(' - ETAP - Dodano dokumenty, notyfikacja wędruje do:');
            if (in_array('AG', $stages)) {
                if (in_array('toPM', $stages)) {
                    $GLOBALS['log']->fatal('PMa');
                    $notif->assigned_user_id = $this->pPM;
                    // dodanie imienia użytkownika
                    $notif->description = 'Proszę o zaakceptowanie faktury przez PM.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }elseif(in_array('toSV', $stages)){
                    $GLOBALS['log']->fatal('SVa');
                    $notif->assigned_user_id = $this->pSV;
                    // dodanie imienia użytkownika
                    $notif->description = 'Proszę o zaakceptowanie faktury przez SV.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }else{
                    $GLOBALS['log']->fatal('- BŁAD (addNotification) - nie wiadomo');
                }
            }elseif( in_array('acceptedByPM&QS', $stages) ){
                if (in_array('toSV', $stages)) {
                    $GLOBALS['log']->fatal('SVa');
                    $notif->assigned_user_id = $this->pPM;
                    // dodanie imienia użytkownika, zaakceptowane przez QSa i PMa bo bezprojektowa
                    $notif->description = 'Proszę o zaakceptowanie bezprojektowej faktury przez SV.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }
            }elseif( in_array('QS', $stages) ){
                if (in_array('toPM', $stages)) {
                    $GLOBALS['log']->fatal('PMa');
                    $notif->assigned_user_id = $this->pPM;
                    // dodanie imienia użytkownika, zaakceptowane przez QSa
                    $notif->description = 'Proszę o zaakceptowanie faktury przez PM.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }
            }
        }
        /***********************************************************/
        if (in_array('PMok', $stages)){
            if (in_array('PM', $stages)) {
                if (in_array('toSV', $stages)) {
                    $GLOBALS['log']->fatal(' - ETAP - PM zaakceptował idzie do SVa');
                    $notif->assigned_user_id = $this->pSV;
                    $notif->description = 'Proszę o zaakceptowanie faktury przez SV.';
                    $notif->confirmation = 1;
                    $ifSave=1;
                }
            }
        }
        /***********************************************************/
        if (in_array('SVok', $stages)){
            if (in_array('SV', $stages)) {
                $GLOBALS['log']->fatal(' - ETAP - SV zaakceptował idzie do Księgowej');
                // zamiast if po prostu current user TODO
                if (in_array('toSLW', $stages)) {
                    // $bean->assigned_user_id = self::SYLWIA;
                    $notif->assigned_user_id = self::SYLWIA;
                    $notif->description = 'Proszę o zaakceptowanie faktury przez Małgosię'; // Sylwie
                    $notif->confirmation= 0;
                    $ifSave=1;

                    if(in_array('deleteNotificationToSLW', $stages)) {
                        $notif->deleted = 1;
                        $notif->is_read = 1;
                    }
                }elseif (in_array('toMAL', $stages)) {
                    // $bean->assigned_user_id = self::MALGOSIA;
                    $notif->assigned_user_id = self::MALGOSIA;
                    $notif->description = 'Proszę o zaakceptowanie faktury przez Małgosię'; // Sylwie
                    $notif->confirmation= 0;
                    $ifSave=1;
                }
            }
        }
        /***********************************************************/
        if (in_array('SLWok', $stages)){
            if (in_array('SLW', $stages)) {
                    $GLOBALS['log']->fatal(' - ETAP - Księgowa zaakceptowała');
                    // $bean->assigned_user_id = self::SYLWIA;
                    $notif->assigned_user_id = self::SYLWIA;
                    $notif->description = 'Faktura zaakceptowana';
                    $notif->deleted = 1;
                    $notif->is_read = 1;
                    // $ch = curl_init('http://'. $_SERVER['HTTP_HOST'] .'/index.php?entryPoint=invoiceDescription&fv=' . $invoice_id);
                    $ifSave=1;
            }
        }
        /***********************************************************/
        if (in_array('MALok', $stages)){
            if (in_array('MAL', $stages)) {
                    $GLOBALS['log']->fatal(' - ETAP - Księgowa zaakceptowała');
                    // $bean->assigned_user_id = self::MALGOSIA;
                    $notif->assigned_user_id = self::MALGOSIA;
                    $notif->description = 'Faktura zaakceptowana';
                    $notif->deleted = 1;
                    $notif->is_read = 1;
                    // $ch = curl_init('http://'. $_SERVER['HTTP_HOST'] .'/index.php?entryPoint=invoiceDescription&fv=' . $invoice_id);
                    $ifSave=1;
            }
        }
        /***********************************************************/
        if (in_array('allMultipartAcceptedBySV', $stages)){
            if (in_array('JJ', $stages) || in_array('AW', $stages) || in_array('SV', $stages)) {
                    $multiproject_row = $db->fetchByAssoc($db->query("SELECT `name`, `id_c`, `nett_c` FROM `ac_invoices_cstm`
                                                LEFT JOIN `ac_invoices` ON(`id` = `id_c`)
                                                    WHERE `multiproject_part_c` = 0 AND `multiproject_c` = 1
                                                        AND `invoice_no_c` = '{$bean->invoice_no_c}' AND `deleted` = 0
                                                        AND `account_id_c` = '{$bean->account_id_c}'"));

                    $notif->name = $multiproject_row['name'];
                    $notif->parent_id = $multiproject_row['id_c'];
                    $notif->assigned_user_id = self::MALGOSIA;
                    $notif->description = 'Proszę o zaakceptowanie faktury przez Małgosię';
                    $notif->confirmation = 0;
                    $notif->deleted = 0;
                    $notif->is_read = 0;
                    $ifSave = 1;
                    $addForMilena = true;

                    $GLOBALS['log']->fatal(' - ETAP - Wszystkie multiprojektowe partowe zaakceptowane przez SVa');
            }
        }
        /***********************************************************/
        if (in_array('multipartAcceptedByKsiegowa', $stages)){
            if (in_array('MAL', $stages) || in_array('MI', $stages)) {
                    // $bean->assigned_user_id = self::MALGOSIA;
                    $notif->assigned_user_id = self::MALGOSIA;
                    $notif->description = 'Faktura zaakceptowana';
                    $notif->deleted = 1;
                    $notif->is_read = 1;
                    // $ch = curl_init('http://'. $_SERVER['HTTP_HOST'] .'/index.php?entryPoint=invoiceDescription&fv=' . $invoice_id);
                    $ifSave=1;
                    $GLOBALS['log']->fatal(' - ETAP - Multiprojektowa główna zaakceptowana przez księgową');

                    $db->query("UPDATE `notifications` SET `is_read` = 1, `deleted` = 1, `description` = 'Faktura zaakceptowana' WHERE `parent_id` = '{$bean->id}'");
            }
        }
        /***********************************************************/
        if( in_array('rejected', $stages) ){
            $GLOBALS['log']->fatal('- ETAP - Faktura odrzucona:');

            if( in_array('rejectedByAG', $stages) ){ $notif->description = 'Agnieszka prosi o weryfikację dokumentów.';
            }elseif(in_array('rejectedByQS', $stages) ){ $notif->description = 'QS prosi o poprawę faktury';
            }elseif(in_array('rejectedByQS&PM', $stages) ){ $notif->description = 'PM bezprojektowej faktury prosi o jej poprawę';
            }elseif(in_array('rejectedByPM', $stages) ){ $notif->description = 'Zwrócone do poprawy przez PMa';
            }elseif(in_array('rejectedBySV', $stages) ){ $notif->description = 'Zwrócone do poprawy przez Supervisora';
            }elseif(in_array('rejectedBySLW', $stages) ){ $notif->description = 'Zwrócone do poprawy przez księgową Sylwie';
            }elseif(in_array('rejectedByMAL', $stages) ){ $notif->description = 'Zwrócone do poprawy przez księgową Małgorzatę';
            }elseif(in_array('PMrejectedByHimSelf', $stages) ){$notif->description = 'Czeka na ponowne potwierdzenie PMa';
            }elseif(in_array('SVrejectedByHimSelf', $stages) ){$notif->description = 'Czeka na ponowne potwierdzenie SVa';
            }elseif(in_array('SLWrejectedByHimSelf', $stages) ){$notif->description = 'Czeka na ponowne potwierdzenie księgowej';
            }elseif(in_array('MALrejectedByHimSelf', $stages) ){$notif->description = 'Czeka na ponowne potwierdzenie księgowej - Małgorzaty'; }

            // odpowiednie przypisanie osoby
            if( in_array('toAG', $stages) ){ $notif->assigned_user_id = self::AGNIESZKA;
                }elseif(in_array('toQS', $stages) ){ $notif->assigned_user_id = $this->pQS;
                }elseif(in_array('toPM', $stages) ){ $notif->assigned_user_id = $this->pPM;
                }elseif(in_array('toSV', $stages) ){ $notif->assigned_user_id = $this->pSV;
                }elseif(in_array('toSLW', $stages) ){ $notif->assigned_user_id = self::SYLWIA;
                }elseif(in_array('toMAL', $stages) ){ $notif->assigned_user_id = self::MALGOSIA; }

                    $notif->confirmation = 0;
                    $ifSave=1;

            $GLOBALS['log']->fatal(print_r($stages, true));
        }

        // check if assigned person changed
        if($notif->assigned_user_id!==$bean->assigned_user_id){
            $ret = $GLOBALS['db']->query("update ac_invoices set assigned_user_id='{$notif->assigned_user_id}', description='{$notif->description}' where id='{$bean->id}' ");
            if ($row = $GLOBALS['db']->fetchByAssoc($ret)) {
                // $GLOBALS['log']->fatal('INVOICE - After notifications: invoice assign to changed to {$notif->assigned_user_id}');
            }
        }

        if($bean->description!==$notif->description){
            $ret = $GLOBALS['db']->query("update ac_invoices set description='{$notif->description}' where id='{$bean->id}' ");
            if ($row = $GLOBALS['db']->fetchByAssoc($ret)) {
                // $GLOBALS['log']->fatal('INVOICE - After notifications: invoice description changed to {$notif->description}');
            }
        }

        if($ifSave==1) {
            $GLOBALS['log']->fatal(' - ETAP - Opis zapisywanej akcji: '.$notif->description);
            $notif->save();

            if($addForMilena == true) {
                $MilenaNotif = BeanFactory::newBean('Notifications');
                $MilenaNotif->parent_type = 'AC_Invoices';
                $MilenaNotif->severity = "invoice";
                $MilenaNotif->is_read = 0;
                $MilenaNotif->confirmation = 0; $MilenaNotif->deleted = 0;
                $MilenaNotif->name = $notif->name;
                $MilenaNotif->parent_id = $notif->parent_id;
                $MilenaNotif->assigned_user_id = self::MILENA;
                $MilenaNotif->description = 'Proszę o zaakceptowanie faktury przez Milenę';
                $MilenaNotif->save();
                $MilenaNotif = null;
                unset($MilenaNotif);
            }

            $notif = null;
            unset($notif);
        }else{
            $GLOBALS['log']->fatal(' - ETAP - Nie dokonano zmian, nie generujemy notyfikacji');
        }

        $GLOBALS['log']->fatal('1t Koniec dodawania notyfikacji');
    }

    /*
     * Function manage the invoice information
    */
    public function updateInvoice(&$bean, $arguments)
    {
        // $GLOBALS['log']->fatal($arguments);

        // who accept the invoice
        $this->checkInvoice($bean);
        $GLOBALS['log']->fatal("QS: ". $this->pQS .' PM: '.$this->pPM .' SV: '.$this->pSV .' Aktualnie zalogowany: '.$this->current_user->user_name .' Nazwa projektu: '.$this->project_name .' ');

        $stages = $this->whichStage($bean, $arguments);

        // 3b. update document relation
        $this->updateDocumentRelation($bean);

        $GLOBALS['log']->fatal('Update after - mamy w stage '.print_r($stages, true));
        // add a notification
        $this->addNotification($bean, $stages);
        $GLOBALS['log']->fatal('Update after2 - mamy w stage '.print_r($stages, true));
        // save documents


    }

    public function setTeams(&$bean, $arguments)
    {
        require_once('modules/Teams/TeamSet.php');
        $assigned_teams = array();
        $bean->load_relationship('teams');

        $GLOBALS['log']->fatal('2a Ustawienie zespołów dla faktury:');
        // owner unknown
        if($bean->owner_unknown_c == true) {
          $assigned_teams[] = 1;
          $bean->team_id = "1";
          $GLOBALS['log']->fatal(' - NW');
        } else {
            $teamSetBean = new TeamSet();
            $assigned_teams[] = $teamSetBean->getTeamViaDepartment("8b636633-dae5-6af9-15ae-56a5b82126b7"); // FA
            $assigned_teams[] = $teamSetBean->getUserPrivateTeam("85ac3697-84bc-9400-07f9-5770d2e0c12e"); // add Lukasik private team
            $assigned_teams[] = $teamSetBean->getUserPrivateTeam("d8c6bac7-eb79-5cc3-6580-5770ce45b719"); // add Gryzewski private team
            $bean->team_id = $teamSetBean->getTeamViaDepartment("4d4281ae-106b-b8dd-d888-56a5b788ca8d"); // Board

            // (not multi or multi but part invoice) and not board invoice
            if( !($bean->multiproject_c == true && $bean->multiproject_part_c == false) && $bean->board_invoice_c == false) {
                // without project
                if(!empty($bean->user_id_c) && $bean->without_project_c == true) {
                    $GLOBALS['log']->fatal(' - bezprojektowej');
                    $assigned_teams[] = $teamSetBean->getUserPrivateTeam($bean->user_id_c);
                } else { // project invoice or multi project part invoice
                    $GLOBALS['log']->fatal(' - projektowej/partowej');
                    $project_id = $bean->project_id_c;

                    if(!empty($project_id)) {
                        $project_bean = BeanFactory::getBean("Project", $project_id);
                        $project_bean->load_relationship('teams');

                        //Retrieve the teams from the team_set_id
                        $project_teams = $teamSetBean->getTeams($project_bean->team_set_id);
                        foreach($project_teams as $team) {
                            $assigned_teams[] = $team->id;
                        }

                        unset($project_bean);
                        $project_bean = null;
                    }
                }
            } else {
                $GLOBALS['log']->fatal(' - multiprojektowej/boardowej');
            }
        }

        $GLOBALS['log']->fatal(print_r($assigned_teams, true));
        $bean->teams->replace($assigned_teams);
    }

    public function deleteNotification($bean, $event, $arguments)
    {
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE `notifications` SET `deleted` = 1, `is_read` = 1 WHERE parent_id = '". $bean->id ."'");

        $GLOBALS['log']->fatal('4a Usunięcie notyfikacji i faktury '. $bean->name);
        if($bean->multiproject_c == true && $bean->multiproject_part_c == false) {
            $multi_part_query = $db->query("SELECT `ac_invoices_ac_invoices_1ac_invoices_idb` FROM `ac_invoices_ac_invoices_1_c` WHERE `ac_invoices_ac_invoices_1ac_invoices_ida` = '{$bean->id}'");

            while($part_row = $db->fetchByAssoc($multi_part_query)) {
                $db->query("UPDATE `notifications` SET `deleted` = 1, `is_read` = 1 WHERE `parent_id` = '{$part_row['ac_invoices_ac_invoices_1ac_invoices_idb']}'");
                $db->query("UPDATE `ac_invoices` SET `deleted` = 1 WHERE `id` = '{$part_row['ac_invoices_ac_invoices_1ac_invoices_idb']}'");
            }
        }
    }

    public function putToStatistics($bean, $event, $arguments)
    {
        $values = array();
        $db = DBManagerFactory::getInstance();
        $average_orders = $db->fetchByAssoc($db->query("SELECT AVG(`nett_c`) AS `net_avg`
                                                FROM `ac_invoices`
                                                    LEFT JOIN `ac_invoices_cstm` ON(`id` = `id_c`)
                                                WHERE `account_id_c` = '{$bean->account_id_c}'
                                                    AND `deleted` = 0 AND `nett_c` > 0
                                                    AND (`multiproject_c` = 0 OR (`multiproject_c` = 1 AND `multiproject_part_c` = 1))"));

        $projects_data_query = $db->query("SELECT `nett_c`, `project_id_c`
                                                FROM `ac_invoices`
                                                    LEFT JOIN `ac_invoices_cstm` ON(`id` = `id_c`)
                                                WHERE `account_id_c` = '{$bean->account_id_c}'
                                                    AND `deleted` = 0 AND `project_id_c` IS NOT NULL AND `nett_c` > 0
                                                    AND (`multiproject_c` = 0 OR (`multiproject_c` = 1 AND `multiproject_part_c` = 1))");

        while($projects_data_row = $db->fetchByAssoc($projects_data_query)) {
            $values[$projects_data_row['project_id_c']][] = $projects_data_row['nett_c'];
        }

        $project_sum = array();
        foreach ($values as $key => $value) {
            $project_sum[$key] = array_sum($values[$key]);

            $acount_project_count= $db->fetchByAssoc($db->query("SELECT COUNT(`id`) AS `ile`
                                                FROM `accounts_project_1_c`
                                                WHERE `accounts_project_1accounts_ida` = '{$bean->account_id_c}'
                                                    AND `accounts_project_1project_idb` = '{$key}'"));

            if($acount_project_count['ile'] == 0) {
                $db->query("INSERT INTO `accounts_project_1_c` VALUES ('". create_guid() ."', CURRENT_DATE, 0, '{$bean->account_id_c}', '{$key}')");
            }
        }

        $min = min($project_sum);
        $max = max($project_sum);
        $project_count = count($project_sum);
        $average = array_sum($project_sum) / $project_count;

        $db->query("UPDATE `accounts_cstm`
                        SET `average_value_c` = '{$average}',
                            `average_orders_c` = '{$average_orders['net_avg']}',
                            `min_value_c` = '{$min}',
                            `max_value_c` = '{$max}',
                            `complited_projects_c` = '{$project_count}'
                    WHERE `id_c` = '{$bean->account_id_c}'");
    }

    public function addRelationshipWithSupplier(&$bean, $event, $arguments)
    {
        if($bean->load_relationship("accounts_ac_invoices_1")) {
            $bean->accounts_ac_invoices_1ac_invoices_idb = $bean->id;
            $bean->accounts_ac_invoices_1->add($bean->account_id_c);
        }
    }
}

?>
