<?php

require_once("modules/Teams/TeamSetManager.php");

/**
 * Class, copy all records from old crm's database. Sets objects and save them in new crm's database
*/
class CopyInvoices
{
	private $db;
	private $dir_to;

	/**
	 * Constructor of the class
	*/
	function __construct()
	{
		$this->db = DBManagerFactory::getInstance();
		$this->dir_to = "/home/admin/domains/". $_SERVER['HTTP_HOST'] ."/public_html/upload/";
	} // constructor

	/**
	 * Function establish connection with old database
	 * @return database statment
	*/
	private function connectToOldDB()
	{
		$db = new PDO('mysql:host=localhost;dbname=admin_old_crm;charset=UTF-8', 'admin_old_crm', '12345', array(  
				PDO::ATTR_EMULATE_PREPARES => false,  
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION  
			) 
		);

		return $db;
	} // function

	/**
	 * Function deletes records from specified tables
	*/
	public function deleteRecords()
	{
		$this->db->query("TRUNCATE TABLE ac_invoices");
		$this->db->query("TRUNCATE TABLE ac_invoices_audit");
		$this->db->query("TRUNCATE TABLE ac_invoices_cstm");
		$this->db->query("DELETE FROM `notes` WHERE parent_type = 'AC_Invoices'");
		$this->db->query("DELETE FROM `notifications` WHERE parent_type = 'AC_Invoices'");
		$this->db->query("TRUNCATE TABLE notes_cstm");
		$this->db->query("TRUNCATE TABLE ac_invoices_notes_1_c");
		$this->db->query("TRUNCATE TABLE ac_invoices_notes_2_c");
		$this->db->query("TRUNCATE TABLE ac_invoices_notes_3_c");
		$this->db->query("TRUNCATE TABLE ac_invoices_notes_4_c");
		$this->db->query("TRUNCATE TABLE ac_invoices_notes_5_c");
	} // function

	/**
	 * Function gets all invoices from new database excluding multiproject invoice
	*/
	public function selectPartInvoices()
	{
		$part_invoice = array();
		$query = $this->db->query('SELECT id, date_modified, name FROM ac_invoices WHERE id IN (SELECT ac_invoices_ac_invoices_1ac_invoices_idb FROM ac_invoices_ac_invoices_1_c) AND name NOT LIKE "Multiproject%"');

		while($invoice = $this->db->fetchByAssoc($query)) {
			$part_invoice[$invoice['id']] = $invoice;
		} // while

		return $part_invoice;
	} // function

	/**
	 * Function gets invoices from old database
	 * @return array with old invoices
	*/
	private function selectAllFromOld()
	{
		$old_array = array();
		$old_db = $this->connectToOldDB();

		// gets invoice 
		$query = "SELECT * FROM `fa_invoices` 
   				LEFT JOIN `fa_invoices_cstm` ON(`fa_invoices`.`id` = `fa_invoices_cstm`.`id_c`) 
   				WHERE deleted = 0 ORDER BY date_modified DESC LIMIT ". $_GET['start'] .", ". $_GET['koniec'];
   	
   		$query = $old_db->query($query);

 		while($invoices = $query->fetch(PDO::FETCH_ASSOC)) {
 			$r_projects = $old_db->query("SELECT * FROM `fa_invoices_project_c` WHERE `fa_invoices_projectfa_invoices_ida` = '{$invoices['id']}' AND `deleted` = 0 LIMIT 1");
 			while($i_project = $r_projects->fetch(PDO::FETCH_ASSOC)) {
 				$invoices['project_result'][] = $i_project['fa_invoices_projectproject_idb']; // project invoice relation
 			} // while
 			$old_array[] = $invoices;
 		} // while

 		return $old_array;
	} // function

	/**
	 * Function gets invoices from new database
	 * @return array with new invoices
	*/
	public function selectAllFromNew()
	{
		$new_array = array();
		$result = $this->db->query("SELECT `id`, `date_modified` FROM `ac_invoices` WHERE deleted = 0 ORDER BY date_modified DESC");
		
		while($row = $this->db->fetchByAssoc($result)) {
 			$new_array[$row['id']] = $row;
    	} // while

    	return $new_array;
	} // function

	/**
	 * Function gets multiproject invoices from old database
	 * @return array with old invoices
	*/
	public function selectMultiprojects()
	{
		$multiprojects = array();
		$old_db = $this->connectToOldDB();
		$query = "SELECT * FROM `fa_invoices` 
		   				LEFT JOIN `fa_invoices_cstm` ON(`fa_invoices`.`id` = `fa_invoices_cstm`.`id_c`) 
		   				WHERE deleted = 0 AND name LIKE '%Multiprojects summary%' ORDER BY date_modified DESC LIMIT ". $_GET['start'] .", ". $_GET['koniec'];
		$result = $old_db->query($query);

		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
 			$multiprojects[$row['id']] = $row;
    	} // while

    	return $multiprojects;
	} // function

	/**
	 * Function updates or create new invoices
	 */
	public function manageDB()
	{
		$old_array = $this->selectAllFromOld();
    	$new_array = $this->selectAllFromNew();
    	$part_array = $this->selectPartInvoices();

    	echo "(I) Sprawdzam faktury:<br />";
	    foreach ($old_array as $o_array) {
	    	// update part invoice fields
	    	if(!empty($part_array[$o_array['id']])) {
	    		$this->deleteFiles($o_array['id']);
				$this->deleteInvoice($o_array['id']);
	      		$this->addInvoice($o_array);
				$this->db->query("UPDATE ac_invoices_cstm SET 
										nett1_c = '{$o_array['agreefee1_c']}', 
										nett_c = '{$o_array['vat_c']}',
										multiproject_part_c = 1,
										multiproject_c = 1
									WHERE id_c = '{$o_array['id']}'");
	    	} // if
	    } // foreach

      	// if not empty fcp list related to the invoice
      	if(!empty($o_array['fcps_c'])) {
      		$fcps_array = array();
			$fcps = explode(PHP_EOL, $o_array['fcps_c']); // explode by end of line

			// add one to the number
			$request = $this->db->query('SELECT `t`.`name`, @rownum := @rownum + 1 AS `rank` FROM `ab_fcp` `t`, (SELECT @rownum := -1) r');
			while($row = $this->db->fetchByAssoc($request)) {
				$fcps_array[$row['rank']] = $row['name'];
			} // while

			foreach ($fcps as $fcp_value) {
				if(in_array($fcp_value, $fcps_array)) {
					$fcp_number = array_search($fcp_value, $fcps_array); // fcp sortet by numbers
					$this->db->query('UPDATE `ac_invoices_cstm` SET `fcplist3_c`="^key_'. $fcp_number .'^" WHERE id_c="'. $o_array['id'] .'"'); // add fcp
				} // if
			} // foreach
	    } // if
	} // function

	/**
	 * Function remove invoice and all relationships with that invoice by specified id
	*/
	public function deleteInvoice($id)
	{
		// delete invoice and related notes
		$this->db->query('DELETE `i`, `i_c`, `in1`, `in2`, `in3`, `in4`, `in5` FROM `ac_invoices` `i` 
				LEFT JOIN `ac_invoices_notes_1_c` `in1` ON(`i`.`id` = `in1`.`ac_invoices_notes_1ac_invoices_ida`)
				LEFT JOIN `ac_invoices_notes_2_c` `in2` ON(`i`.`id` = `in2`.`ac_invoices_notes_2ac_invoices_ida`)
				LEFT JOIN `ac_invoices_notes_3_c` `in3` ON(`i`.`id` = `in3`.`ac_invoices_notes_3ac_invoices_ida`)
				LEFT JOIN `ac_invoices_notes_4_c` `in4` ON(`i`.`id` = `in4`.`ac_invoices_notes_4ac_invoices_ida`)
				LEFT JOIN `ac_invoices_notes_5_c` `in5` ON(`i`.`id` = `in5`.`ac_invoices_notes_5ac_invoices_ida`)
				LEFT JOIN `ac_invoices_cstm` `i_c` ON(`i`.`id` = `i_c`.`id_c`) 
				WHERE `i`.`id`="'. $id .'"');

		// delete notifications and notes
		$this->db->query('DELETE FROM `notifications` WHERE `parent_id` = "'. $id .'"');
		$this->db->query('DELETE `n`, `n_c` FROM `notes` `n` LEFT JOIN `notes_cstm` `n_c` ON(`n`.`id` = `n_c`.`id_c`) WHERE `n`.`parent_id` = "'. $id .'"');

		// check if deleted
		$sql = $this->db->query('SELECT * FROM `ac_invoices` WHERE id = "'. $id .'"');
		if($sql->num_rows > 0) { echo "<strong>". $id ."</strong>"; }
	} // function

	/**
	 * Function remove invoice file
	*/
	public function deleteFiles($invoice_id)
    {
        $result = $this->db->query("SELECT `id` FROM `notes` WHERE `parent_id`='{$invoice_id}'");
        if($result->num_rows > 0) {
            // delete old file
            while($note = $this->db->fetchByAssoc($result)) {
                unlink($this->dir_to.$note['id']);
            } // while
        } // if
    } // public

    /**
	 * Function adds teams to the invoice
	 * @return array with teams
	*/
	private function validateTeams($old_invoice, &$invoiceBean)
	{
		$teams = array();
		$assigned_teams = array();
		$old_db = $this->connectToOldDB();
		$main_assigned_team = "7142219e-7123-921d-33f1-5749237884b5";
		
		if($old_invoice['without_project_c'] == true) {
			$assigned_teams[] = $old_invoice['user_id1_c'];
		} // if
 
		if($old_invoice['no_owner_c'] == true) {
			$main_assigned_team = "1";
		} // if

		// get teams by department
		if(array_key_exists('project_result', $old_invoice)) {
			$project_teams = array();
			foreach ($old_invoice['project_result'] as $project_id) {
				$query = $old_db->query("SELECT `project_team_c`,`archival_c` FROM `project_cstm` WHERE `id_c`='". $project_id ."'");
				while($team = $query->fetch(PDO::FETCH_ASSOC)) {
					$invoiceBean->archived_c = $team['archival_c'];
					$team['project_team_c'] = ($team['project_team_c'] == "pt_mkier") ? "pt_mk" : $team['project_team_c'];

					$result = $this->db->query("SELECT `id` FROM  `teams` WHERE  `description` LIKE  '". $team['project_team_c'] ."'");
					$row = $this->db->fetchByAssoc($result);
					$assigned_teams[] = $row['id']; // array with teams
				} // while
			} // foreach
		} // if
		
		$assigned_teams[] = "462fff89-1c17-981f-353b-57492384e7a2";
		$assigned_teams[] = "e10f82cf-daee-f756-75b5-574482132d5d";

		$invoiceBean->load_relationship('teams');
		$invoiceBean->team_id = $main_assigned_team;
		$invoiceBean->teams->replace($assigned_teams);

		$teams[] = $main_assigned_team;
		foreach($assigned_teams as $team) {
			$teams[] = $team; // all teams together
		} // foreach

		return $teams;
	} // function

	/**
	 * Function create notes related to the invoice
	 * @return note id
	*/
	private function createAttachments($file_name, $invoice_id, $teams, $invoice_type)
	{
		// create note object
		$scanBean = BeanFactory::newBean("Notes", array('disable_row_level_security' => true));
		$scanBean->new_with_id = true;
		$new_id = create_guid();
		$scanBean->id = $new_id;
		$scanBean->name = $file_name;
		$scanBean->assigned_user_id = "9122d6b9-46e5-9013-99f7-540f4beb464e";
		$scanBean->created_by = "9122d6b9-46e5-9013-99f7-540f4beb464e";
		$scanBean->file_mime_type = "application/pdf";
		$scanBean->filename = $file_name;
		$scanBean->parent_type = "AC_Invoices";
		$scanBean->parent_id = $invoice_id;

		// add type of the note
		switch($invoice_type) {

			case "scan":
				$scanBean->invoice_scan_c = '1';
				break;

			case "agreement_f":
				$scanBean->invoice_agreement_c = '1';
				break;

			case "s_safety_f":
				$scanBean->invoice_bhpbioz_c = '1';
				break;

			case "s_enviroment_f":
				$scanBean->invoice_bhpbioz_c = '1';
				break;

			case "warranty_f":
				$scanBean->invoice_warranty_c = '1';
				break;

			case "work_completed_f":
				$scanBean->invoice_work_completed_c = '1';
				break;
		} // switch

		// add teams
		$scanBean->load_relationship('teams');
		$scanBean->team_id = $teams[0];
		$scanBean->teams->replace($teams);

		$scanBean->save();
		unset($scanBean);
		$scanBean = null;

		return $new_id;
	} // function

	/**
	 * Function create notification related to the invoice
	 * @return assigned user id
	*/
	private function createNotifications($invoice_id, $invoice_name, $project_qs)
	{	
		// get notification from old database
		$old_db = $this->connectToOldDB();
		$query = $old_db->query('SELECT `assigned_user_id`, `deleted` FROM ac_notifications WHERE fa_invoices_id_c = "'. $invoice_id .'" AND deleted=0 ');
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$deleted = $row['deleted'];
		$assigned_to = null;

		// if notification not deleted and some is assigned to it then
		if($deleted == 0 && !empty($row['assigned_user_id']) ) {
			// create notification object
			$notifyBean = BeanFactory::newBean("Notifications", array('disable_row_level_security' => true));
			$notifyBean->new_with_id = true;
			$notifyBean->id = create_guid();
			$notifyBean->name = "Invoice ". $invoice_name;
			$assigned_to = $row['assigned_user_id'];
			$notifyBean->assigned_user_id = $assigned_to;
			$notifyBean->is_read = 0;
			$notifyBean->severity = "invoice";
			$notifyBean->parent_type = "AC_Invoices";
			$notifyBean->parent_id = $invoice_id;
			$notifyBean->confirmation = 1;
			
			if($notifyBean->assigned_user_id == $project_qs) { $notifyBean->confirmation = 0; }

			// and save notification
			$notifyBean->save();
			unset($notifyBean);
			$notifyBean = null;

			return $assigned_to;
		} else {
			return null; // return null
		} // if/else
	} // function

	/**
	 * Function create notification related to the invoice
	 * @return assigned user id
	*/
	public function addInvoice($invoice, $remove = false)
	{
		$dir_from = "/domains/{old_crm}/public_html/upload/";
	    $folder_name = $this->dir_to;
	    $add_document = false;

	    // create new invoice object
	    $invoiceBean = BeanFactory::newBean("AC_Invoices", array('disable_row_level_security' => true));
	    $invoiceBean->new_with_id = true;
		$invoiceBean->import = true; // to prevent running custom logic
		$invoiceBean->id = $invoice['id'];
		$invoiceBean->name = $invoice['name'];
		$invoiceBean->date_entered = date("Y-m-d H:i:s", strtotime($invoice['date_entered']));
		$invoiceBean->date_modified = date("Y-m-d H:i:s", strtotime($invoice['date_modified']));
		$invoiceBean->modified_user_id = $invoice['modified_user_id'];
		$invoiceBean->created_by = $invoice['created_by'];
		$invoiceBean->description = $invoice['description'];
		$invoiceBean->deleted = $invoice['deleted'];
		$invoiceBean->accept1_c = $invoice['accept1'];
		$invoiceBean->accept2_c = $invoice['accept2'];
		$invoiceBean->accept3_c = $invoice['accept3'];
		$invoiceBean->accept4_c = $invoice['accept4_c'];
		$invoiceBean->invoice_no_c = $invoice['invoice_no_c'];
		$invoiceBean->without_project_c = $invoice['without_project_c'];
		$invoiceBean->owner_unknown_c = $invoice['no_owner_c'];
		$invoiceBean->board_invoice_c = $invoice['board_invoice_c'];
		$invoiceBean->account_id_c = $invoice['account_id_c'];
		$invoiceBean->fcp_list_c = $invoice['fcps_c'];
		$invoiceBean->vat_c = 1.23;
		$invoiceBean->nett_c = $invoice['vat_c'];
		$invoiceBean->gross_c = $invoice['amount_c'];
		$invoiceBean->package_no_c = (!empty($invoice['range_no_c'])) ? $invoice['range_no_c'] : null;
		$invoiceBean->agreement_na_c = $invoice['agreement_na_c'];
		$invoiceBean->s_safety_na_c = $invoice['s_safety_na_c'];
		$invoiceBean->warranty_na_c = $invoice['warranty_na_c'];
		$invoiceBean->s_enviroment_na_c = $invoice['s_enviroment_na_c'];
		$invoiceBean->work_completed_na_c = $invoice['work_completed_na_c'];
		$invoiceBean->part_invoice_c = $invoice['part_invoice_c'];
		$invoiceBean->last_invoice_c = $invoice['last_invoice_c'];
		$invoiceBean->proform_paid_c = $invoice['proform_c'];
		$invoiceBean->date_of_issue_c = $invoice['date_of_issue_c'];
		$invoiceBean->archived_c = 0;
		$invoiceBean->project_id_c = $invoice['project_id_c'];
		
		$teams = $this->validateTeams($invoice, $invoiceBean);

		// adding some properties
	    if(($invoice['without_project_c'] || $invoice['board_invoice_c']) && ($invoice['accept4_c'] == true)) {
	    	$invoiceBean->archived_c = 1;

	    	if($invoice['board_invoice_c']) {
	    		$invoiceBean->accept1_c = 1;
	    		$invoiceBean->accept2_c = 1;
	    		$invoiceBean->agreement_na_c = 1;
				$invoiceBean->s_safety_na_c = 1;
				$invoiceBean->warranty_na_c = 1;
				$invoiceBean->s_enviroment_na_c = 1;
				$invoiceBean->work_completed_na_c = 1;
	    	} // if
	    } // if

	    if($invoiceBean->s_enviroment_na_c == 1 && $invoiceBean->agreement_na_c == 1 && $invoiceBean->s_safety_na_c == 1 && $invoiceBean->warranty_na_c == 1 && $invoiceBean->work_completed_na_c == 1) { $invoiceBean->na_all_c = 1; }

    	$project = BeanFactory::retrieveBean('Project', $invoiceBean->project_id_c , array('disable_row_level_security' => true));
		$invoiceBean->assigned_user_id = $this->createNotifications($invoice['id'], $invoice['name'], $project->user_id1_c);

		// invoice need to be assigned to someone
		if(empty($invoiceBean->assigned_user_id )){
			$invoiceBean->assigned_user_id = ($invoice['assigned_user_id'] == null) ? "137a88d7-df78-8f89-9c4c-540f4ad585e4" : $invoice['assigned_user_id'];
		} // if

		// not a multiproject invoice
		if($invoice['more_project_c'] == 0 && $invoice['multiproject_part_c'] == 0) {
			$invoiceBean->nett1_c = $invoice['vat_c'];
			$invoiceBean->multiproject_part_c = 0;
			$invoiceBean->multiproject_c = 0;
			$add_document = true;
		} elseif($invoice['more_project_c'] == 1 && $invoice['multiproject_part_c'] == 0) { // multiproject
			$multi_project = array();
			$invoiceBean->multiproject_part_c = 0;
			$invoiceBean->multiproject_c = 1;

			// relation with projects
			for($i = 0; $i < 9; $i++) {
				$j = ($i == 0) ? $j = "" : $j = $i;

				if(!empty($invoice['project_id'. $j .'_c'])) {
					$multi_project['projects'][] = $invoice['project_id'. $j .'_c'];
				} // if
			} // for

			$invoiceBean->nett1_c = $invoice['agreefee1_c'];
			$invoiceBean->nett2_c = $invoice['agreefee2_c'];
			$invoiceBean->nett3_c = $invoice['agreefee3_c'];
			$invoiceBean->nett4_c = $invoice['agreefee4_c'];
			$invoiceBean->nett5_c = $invoice['agreefee5_c'];
			$invoiceBean->nett6_c = $invoice['agreefee6_c'];
			$invoiceBean->nett7_c = $invoice['agreefee7_c'];
			$invoiceBean->nett8_c = $invoice['agreefee8_c'];

			for($i = 0; $i < 9; $i++) {
				$j = ($i == 0) ? $j = "" : $j = $i;

				// part invoice related to summary invoice
				if(!empty($invoice['fa_invoices_id'. $j .'_c'])) {
					$multi_project['invoices'][] = $invoice['fa_invoices_id'. $j .'_c'];
				} // if
			} // for

			// project to invoice relation
			for($i = 0; $i < sizeof($multi_project['projects']); $i++) {
				$j = ($i == 0) ? $j = "" : $j = $i;
				$project_relation = "project_id". $j ."_c";

				if(!empty($multi_project['projects'][$i])) {
					$invoiceBean->$project_relation = $multi_project['projects'][$i];
				} // if
			} // for

			// invoice to invoice relation
			foreach ($multi_project['invoices'] as $invoice_id) {
				$invoiceBean->load_relationship('ac_invoices_ac_invoices_1');
				$invoiceBean->{"ac_invoices_ac_invoices_1ac_invoices_ida"} = $invoice['id'];
		    	$invoiceBean->ac_invoices_ac_invoices_1->add(array($invoice_id));
			} // foreach

			$accepts = false;
			for($i = 1; $i < 5; $i++) {
				$j = ($i == 4) ? $j = $i."_c" : $j = $i;
				if($invoice["accept". $j] == 1) { $accepts = true; }
				else { $accepts = false; }
			} // for

			if($accepts == true) {
				$invoiceBean->accept1_c = 1;
				$invoiceBean->accept2_c = 1;
				$invoiceBean->accept3_c = 1;
				$invoiceBean->accept4_c = 1;
			} // if

			$add_document = true;
		} // if/elseif

		// add relation with notes
		if($add_document == true) {
			$relationships = array("scan" => array("ac_invoices_notes_1", "scans"),
									"warranty_f" => array("ac_invoices_notes_2", "warranties"),
									"work_completed_f" => array("ac_invoices_notes_3", "work_completed"),
									"s_safety_f" => array("ac_invoices_notes_4", "bhp_bioz"),
									"s_enviroment_f" => array("ac_invoices_notes_4", "bhp_bioz"),
									"agreement_f" => array("ac_invoices_notes_5", "agreement"));

		    foreach ($relationships as $key => $relationship) {
		    	$to = ($key == "scan") ? $to = 7: $to = 10;

		    	for($i = 0; $i < $to; $i++) {
					$field = ($i == 0) ? $key.'_c' : $key. $i .'_c';

					if($invoice[$field]) {
						$j = $i + 1;
						$scan_id = $this->createAttachments($invoice[$field], $invoice['id'], $teams, $key);
						$invoiceBean->load_relationship($relationship[0]);
						$invoiceBean->{"ac_invoices_notes_{$j}ac_invoices_ida"} = $invoice['id'];
						$invoiceBean->$relationship[0]->add(($scan_id));
						$file_from = $dir_from . $invoice['id']."_".$field;

						copy($file_from, $folder_name.$scan_id);
						shell_exec("cp ".$file_from.' '. $folder_name.$scan_id); 
					} // if
				} // for
		    } // foreach
		} // if

		$invoiceBean->save();
		unset($invoiceBean);
		$invoiceBean = null;
	} // function
} // class

$copyInvoices = new CopyInvoices();
$copyInvoices->manageDB();
TeamSetManager::cleanUp();