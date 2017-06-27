<?php
/*
Generowanie pytań
http://dev2.rms.reesco.pl/index.php?entryPoint=dailyQuestions
http://developer.sugarcrm.com/2014/04/22/sugarcrm-cookbook-sugarquery-the-basics/
*/
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if(empty($current_language) && !empty($sugar_config['default_language'])) {
	$current_language = $sugar_config['default_language'];
	$app_list_strings = return_app_list_strings_language($current_language);
	$app_strings = return_application_language($current_language);
}else{
	$app_list_strings = return_app_list_strings_language('en_us');
	$app_strings = return_application_language('en_us');
}

require_once('modules/Teams/TeamSet.php');

$page = file_get_contents('http://www.kalendarzswiat.pl/swieta/wolne_od_pracy/'. date('Y'));
$matches = array();

preg_match_all('#data-date *= *["\']?([^"\']*)#is', $page, $matches);

foreach ($matches[1] as $key => $value) {
    if(date("n", strtotime($value)) == date('n')) {
        if(date("j", strtotime($value)) == date('j')) {
        	echo "It is a holiday";
        	die();	
        }
    } // end if
} // end for

$holidays = array();
$all_users = array();
$db = DBManagerFactory::getInstance();

echo '(I) Generate Questions Notification:<br />';

$query_users = new SugarQuery();
$query_users->select('id');
$query_users->from(BeanFactory::getBean('Users'));
$query_users->where()->queryOr()->equals('daily_questions_c','1');

echo '- getting users<br />';
$users = $query_users->execute();

$questions = array();
$all_questions = array();

$query_questions = new SugarQuery();
$query_questions->select(array('id'));
$query_questions->from(BeanFactory::getBean('AA_Questions'));
$query_questions->where()->equals('deleted','0');

echo '- getting all questions<br />';
$questions = $query_questions->execute();

foreach ($questions as $value) {
	$all_questions[] = $value['id'];
}

echo '- checking holidays<br />';
foreach ($users as $value) {
	$all_users[] = $value['id'];
	$holidays_query = $db->query('SELECT id, assigned_user_id FROM ac_holiday WHERE assigned_user_id="'. $value['id'] .'" AND deleted=0 AND (sick_leave=1 OR (board=1 AND supervisor=1) ) AND CURDATE() BETWEEN v_from AND v_to ');

	if($db->getRowCount($holidays_query) > 0) {
		$row = $db->fetchByAssoc($holidays_query);
		$holidays[] = $row['assigned_user_id'];
	}
}

// po zobaczeniu kto na urlopie usunięcie ich id
$question_users = array_diff($all_users, $holidays);

echo '- checking answers<br />';
foreach ($question_users as $user_id) {
	$good = array();
	$rand_questions = array();

	echo '- geting good <br />';
	$good_answers_query = $db->query("SELECT `aa_questions_aa_answers_1aa_questions_ida` AS `a_id`
		FROM `aa_questions_aa_answers_1_c` WHERE `aa_questions_aa_answers_1aa_answers_idb` IN
			(SELECT `id` FROM `aa_answers` WHERE `created_by` = '{$user_id}' AND `is_good` = 1 AND MONTH(`date_entered`) > MONTH(CURRENT_DATE) - 3)");

	// pętla przez dobre odpowiedzi
	while($good_answer_row = $db->fetchByAssoc($good_answers_query)) {
		$good[] = $good_answer_row['a_id'];
	}

	$good = array_unique($good);
	echo '- comparing answers <br />';
	// zwraca różnicę pomiędzy dobrymi odpowiedziami, a złymi
	$diffed_array = array_diff($all_questions, $good);

	// wybiera 4 losowe ze wszystkich pytań - te pytania ze złymi odp. lub brakiem odp.
	$diff_rand_questions = array_rand($diffed_array, 4);

	// łączenie tablic
	for($i = 0; $i < count($diff_rand_questions); $i++) {
		$rand_questions[] = $diffed_array[$diff_rand_questions[$i]];
	}

	echo '- user id: '. $user_id .'<br />';
	foreach ($rand_questions as $question_id) {
		echo '-- question id: '. $question_id .'<br/>';

		$notif_bean = BeanFactory::newBean('Notifications');
		$notif_bean->name = 'Daily question';
		$notif_bean->severity = 'education';
		$notif_bean->type_c = 1;
		$notif_bean->is_read = 0;
		$notif_bean->parent_type = 'AA_Questions';
		$notif_bean->parent_id = $question_id ;
		$notif_bean->deleted = 0;
		$notif_bean->description = '';

		$notif_bean->assigned_user_id = $user_id;
		$notif_bean->update_modified_by = false;
		$notif_bean->set_created_by = false;

		$notif_bean->created_by = '1';
		$notif_bean->modified_user_id = '1';

		$notif_bean->save(false);
		$notif_bean = null;
		unset($notif_bean);
	}

	echo '(II) Question test: <br />';

	echo $rand_questions[0].'<br />';
	echo $rand_questions[1].'<br />';
	echo $rand_questions[2].'<br />';
	echo $rand_questions[3].'<br /><br />';

	unset($good);
	unset($rand_questions);
}

?>
