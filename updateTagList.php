<?php
	/**
	 * Script used for create/update dropdown tag list
	 */

	if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

	require_once('modules/Teams/TeamSet.php');
	require_once('include/SugarQuery/SugarQuery.php');
	require_once('modules/Studio/DropDowns/DropDownHelper.php');

	$sugarQuery = new SugarQuery();
	$dropdownHelper = new DropDownHelper();

	//fetch the bean of the module to query
	$tagBean = BeanFactory::newBean('AD_Tags');

	//create query to get all tags from database
	$sql = new SugarQuery();
	$sql->select('id', 'name');
	$sql->from($tag_bean, array('team_security' => false));
	$sql->where();

	$result = $sql->execute();
	$recordsNumber = count($result);

	$response = array();
	$response['text'] = '';

	// check tags in database
	if($recordsNumber == 0) {
	    $response['text'] .= 'no tags in database<br />';
	} else {
		$count = 0;
		$response['text'] .= 'saving<br />';

		$parameters = array();
		$parameters['dropdown_name'] = 'tags_list';

		foreach($result as $value){
			$parameters['slot_'. $count] = $count;
		    $parameters['key_'. $count] = str_replace(' ', '_', $value['name']);
		    $parameters['value_'. $count] = $value['name'];

		    //set 'use_push' to true to update/add values while keeping old values
		    $parameters['use_push'] = false;
		    $count++;
		}

		// add dropdown list
		$saveResult = $dropdownHelper->saveDropDown($parameters);
		$response['text'] .= 'saved '. $count;

		// show response
		echo json_encode($response['text']);
	}
?>