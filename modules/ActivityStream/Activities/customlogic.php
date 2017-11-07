<?php

/*
 * Class adds notification after post/comment
 */
class SugarNotifyUser
{
	/*
	 * Function gets all active users from single post,
	 * gets content and makes notification to that users
	 */
	function notifyUserOnPostComment(&$bean, $event, $arguments)
	{
		$all_users = array();
		$all_commentators = $this->getCommentators($bean->id); // array that has all previous comments

		/*
		 * if it is post then get users from post
		 * else if it is comment then get from comment
		 */
		$activity = (!empty($bean->last_comment_bean->id)) ? $this->getContentAndUser($bean->last_comment_bean) : $this->getContentAndUser($bean);

		if(empty($all_commentators) && empty($bean->last_comment_bean->id) && !empty($activity['users'])) {
			$all_users = $activity['users'];
		} elseif(!empty($bean->last_comment_bean->id)) {

			if(!empty($activity['users'])) {
				$all_users = array_merge($all_commentators, $activity['users']);
			} else {
				$all_users = $all_commentators;
			}
			
			// get all users from main post and add to all active persons
			$tmp_users = $this->getContentAndUser($bean);

			if(!empty($tmp_users['users'])) { $all_users = array_merge($all_users, $tmp_users['users']); }

			$all_users[] = $bean->created_by;
			$all_users[] = ($bean->created_by == $bean->last_comment_bean->created_by) ? null : $bean->last_comment_bean->created_by;

			$all_users = array_unique($all_users); // only not the same records suit our need
			$all_users = array_diff($all_users, array('', $GLOBALS['current_user']->id)); // delete empty and current user
		}

		if(!empty($all_users)) {
			$user = BeanFactory::getBean("Users", $bean->created_by);
			$current_user = BeanFactory::getBean("Users", $GLOBALS['current_user']->id);

			// loop for all active users
			foreach ($all_users as $user_id) {
				$notification = BeanFactory::newBean("Notifications", array('disable_row_level_security' => true)); //initialize notification bean

				// check if comment or a post on module without Home module
				if(!empty($bean->parent_type)) {
					$suite_parent_type = $bean->parent_type;
					$suite_parent_id = $bean->parent_id;

					// make a nice looking titles
					$suite_parent_type = ($suite_parent_type == 'AA_Buildings') ? 'Buildings' : $suite_parent_type;
					$suite_parent_type = ($suite_parent_type == 'Home') ? 'Main Board' : $suite_parent_type;
					$suite_parent_type = ($suite_parent_type == 'AC_FeeProposal') ? 'Fee Proposal' : $suite_parent_type;
					$suite_parent_type = ($suite_parent_type == 'AA_Holidays') ? 'Holiday' : $suite_parent_type;
					$suite_parent_type = ($suite_parent_type == 'AC_Invoices') ? 'Invoice' : $suite_parent_type;

					if(!empty($suite_parent_id)) {
						$notification->parent_id = $suite_parent_id;
						// get the parent bean - specified record
						$parent_bean = BeanFactory::getBean($bean->parent_type, $suite_parent_id);
						$notification->name = ($bean->activity_type == "post" && $bean->comment_count == 0) ? "{$user->first_name} {$user->last_name} post {$activity['post']} on {$parent_bean->name}" : "{$current_user->first_name} {$current_user->last_name} comment {$activity['post']} on {$parent_bean->name}"; 
					} else {
						// set module activity
						$notification->name = ($bean->activity_type == "post" && $bean->comment_count == 0) ? "{$user->first_name} {$user->last_name} post {$activity['post']} on {$suite_parent_type}" : "{$current_user->first_name} {$current_user->last_name} comment {$activity['post']} on {$suite_parent_type}";
					}

					$notification->assigned_user_id = $user_id;
					$notification->parent_type =  $bean->parent_type;
					$notification->created_by = $bean->created_by;
					$notification->is_read = 0; //set is_read to false
					$notification->confirmation = 1; //set confirmation to true
					$notification->severity = "notification"; //set the level of severity
					$notification->new_with_id = true; // very important
					$notification->save();
				}
			}
		}
	}

	/*
	 * Function gets all active users but with no repeat
	 */
	function getCommentators($post_id)
	{
		$users = null;
		$db = DBManagerFactory::getInstance();

		$users_query = $db->query('SELECT DISTINCT `created_by` FROM `comments` WHERE `parent_id`="'. $post_id .'"');
		while($user = $db->fetchByAssoc($users_query)) {
			$users[] = $user['created_by'];
		}

		return $users;
	}

	public function salesModules(&$bean, $event, $arguments)
	{
		$suite_parent_type = $bean->parent_type;
		$suite_parent_id = $bean->parent_id;
		
		if($suite_parent_type == "Prospects" || $suite_parent_type == "Leads") {
			$retrive_bean = BeanFactory::getBean($suite_parent_type, $suite_parent_id);
			$retrive_bean->date_of_last_comment_c = date("Y-m-d H:i:s");
			$retrive_bean->save();

			// $notifi_name = ($suite_parent_type == "Prospects") ? "Target" : "Lead";
			// $db = DBManagerFactory::getInstance();

			// if(empty($this->getContentAndUser($bean)) && empty($this->getContentAndUser($bean)['users'])) {
			// 	$managers_dev = $db->query("SELECT `user_id`, `first_name`, `last_name` FROM `users` INNER JOIN `acl_roles_users` ON(`user_id`=`users`.`id`) WHERE `acl_roles_users`.deleted=0 AND `status`='Active' AND `employee_status`='Active' AND `role_id`='6bc49a11-9d89-3ca7-eed9-5936b2efb20f'");
			// 	while($row = $db->fetchByAssoc($managers_dev)) {
			// 		$notifi_bean = BeanFactory::newBean('Notifications');
	  //               $notifi_bean->parent_type = $suite_parent_type;
	  //               $notifi_bean->severity = "notification";
	  //               $notifi_bean->is_read = 0;
	  //               $notifi_bean->confirmation = 1; $notifi_bean->deleted = 0;
	  //               $notifi_bean->name = $row['first_name'] ." ". $row['last_name'] .' added post on '. $notifi_name;
	  //               $notifi_bean->parent_id = $suite_parent_id;
	  //               $notifi_bean->assigned_user_id = $row['user_id'];
	  //               $notifi_bean->description = "";
	  //               $notifi_bean->save();
	  //               $notifi_bean = null;
	  //               unset($notifi_bean);		
			// 	}

			// }
		}
	}

	/*
	 * Function gets content from the post/comment and 
	 * if exists tags mentioned to the users
	 */
	function getContentAndUser($bean)
	{
		$returnValue = array();
		$decoded = json_decode($bean->data);

		if($decoded) {
			if(!empty($decoded->tags)) {
				foreach($decoded->tags as $tag) {
					if($tag->module == "Users") {
						$contents = $decoded->value;

						//if activity type is a post or table names comments
						if($bean->activity_type == "post" || $bean->table_name == "comments") {
							for($i = 0; $i < count($decoded->tags); $i++) {
								$search = "@[". $decoded->tags[$i]->module .":". $decoded->tags[$i]->id .":". $decoded->tags[$i]->name."]";

								if($i == 0) { $returnValue['post'] = str_replace($search, '', $contents); }
								else { $returnValue['post'] = str_replace($search, '', $returnValue['post']); }
							}
						}

						$returnValue['post'] = trim($returnValue['post']);
						$returnValue['users'][] = (!empty($tag->id)) ? $tag->id: null;
					}
				}
			}
		}

		return $returnValue;
	}
}
?>