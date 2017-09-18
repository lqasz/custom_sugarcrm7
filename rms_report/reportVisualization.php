<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportVisualization
{
	public $db;
	public $assistants;
	public $managers;

	public function __construct()
	{
		$this->db = DBManagerFactory::getInstance();
		// $manager = "";
		// $previous = "1";
		// foreach($departments as $dep_name => $department) {
		// 	$this->generateReportByDepartment($department, $dep_name, $manager);

		// 	if($previous != "1" && $previous != $dep_name) {
		// 		$content = $this->formatContentForDepartment($this->users_html['departments'][$dep_name], $dep_name);
		// 		$this->managers[$manager] = $content;
		// 	}
		// 	$previous = $dep_name;
		// }
	}

	public function getReportData()
	{
		$data = array();
		$tasks_result = $this->db->query("SELECT * FROM `rms_report_tasks` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-7) AND CURRENT_DATE");
		while($row = $this->db->fetchByAssoc($tasks_result)) {
			$json_result = array();

			foreach($row as $key => $json) {
				if($key != "id" && $key != "user_name" && $key != "date_entered") {
					$json_result[$key] = mb_convert_encoding($json, "UTF-8");
					$json_result[$key] = str_replace('&quot;', '"', $json_result[$key]);
					$json_result[$key] = json_decode($json_result[$key], true);
				}
			}

			$date = date("d-m-Y", strtotime($row["date_entered"]));
			$data[$row["user_name"]][$date]['Tasks'] = $json_result;
		}

		$modules_result = $this->db->query("SELECT * FROM `rms_report_modules` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE");
		while($row = $this->db->fetchByAssoc($modules_result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));
			$json_result = mb_convert_encoding($row['data'], "UTF-8");
			$json_result = str_replace('&quot;', '"', $json_result);
			$json_result = json_decode($json_result, true);
			
			foreach($json_result as $module_name => $values) {
				$data[$row["user_name"]][$date][$module_name] = $values;	
			}	
		}

		$activities_result = $this->db->query("SELECT * FROM `rms_report_activities` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE");
		while($row = $this->db->fetchByAssoc($activities_result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));

			$login_result = mb_convert_encoding($row['login'], "UTF-8");
			$login_result = str_replace('&quot;', '"', $login_result);
			$login_result = json_decode($login_result, true);

			$notification_result = mb_convert_encoding($row['notifications'], "UTF-8");
			$notification_result = str_replace('&quot;', '"', $notification_result);
			$notification_result = json_decode($notification_result, true);
			
			$data[$row["user_name"]][$date]['Activities']["Bug"] = $row['bugs'];
			$data[$row["user_name"]][$date]['Activities']["Chat"] = $row['chat'];
			$data[$row["user_name"]][$date]['Activities']["Login"] = $login_result;
			$data[$row["user_name"]][$date]['Activities']["Notification"] = $notification_result;
		}

		return $data;
	}

	public function getUsersDepartments()
	{
		$users = array();
		$departments_result = $this->db->query('SELECT CONCAT(`u1`.`first_name`, " ", `u1`.`last_name`) AS `e_name`, `aa_departments`.`name` AS `dep_name`, CONCAT(`u2`.`first_name`, " ", `u2`.`last_name`) AS `m_name` FROM `users` `u1` LEFT JOIN `users_cstm` ON(`u1`.`id`=`users_cstm`.`id_c`) LEFT JOIN `aa_departments` ON(`aa_departments`.`id`=`aa_departments_id_c`) INNER JOIN `users` `u2` ON(`u2`.`id`=`aa_departments`.`assigned_user_id`) WHERE `u1`.`employee_status`="Active" AND `u1`.`status`="Active" AND `u1`.`show_on_employees`=1');

		while($row = $this->db->fetchByAssoc($departments_result)) {
			if($row['m_name'] != $row['e_name']) {
				$users[$row['dep_name']][$row['m_name']][] = $row['e_name'];
			}
		}

		return $users;
	}

	public function prepareReport()
	{
		$data = $this->getReportData();
		$users = $this->getUsersDepartments();

		foreach($users as $dep_name => $users_values) {
			foreach($users_values as $manager => $employees) {
				foreach($employees as $key => $employee) {
					$header_row = '<tr><th></th><th></th><th></th><th colspan="3">'. $employee .'</th>';
					$user_html = $this->generateReportForUser($data[$employee], $header_row);
				}
			}
		}
	}

	public function generateReportForUser($user_data, $header_row)
	{
		$structure = array();
		$structure['first_row'] = $header_row;

		foreach($user_data as $date => $date_data) {
			$structure['first_row'] .= '<th>'. $date .'</th>';
			foreach($date_data as $module_name => $module_data) {
				foreach($module_data as $type => $data_type) {
					if($module_name == "Tasks") {
						if($type != "sum") {
							if($type == "created_tasks" || $type == "quick_tasks" || $type == "closed" || $type == "deleted") {
								$structure[$module_name][$type][$date]['Sum'] = $data_type['all'];
							} else {
								if($data_type['all'] != 0) {
									foreach($data_type as $key => $value) {
										if($key != "all") {
											$structure[$module_name][$type][$date][$key] = $value;
										}
									}
								}
							}
						}
					} else if($module_name != "Activities") {
						if(!is_array($data_type)) {
							$structure[$module_name][$date][$type] = $data_type;
						} else {
							if($data_type['all'] != 0) {
								foreach($data_type as $key => $value) {
									if($key != "all") {
										$structure[$module_name][$date][$key] = $value;
									}
								}
							} else {
								$structure[$module_name][$date][$type] = 0;
							}
						}
					}
				}
			}
		}

		$structure['first_row'] .= '</tr>';
		dump($structure);
	}

	private function generateReportByDepartment($department, $dep_name, &$manager)
	{

		foreach($department as $user_id => $user_data) {

			$this->users_html['departments'][$dep_name][$user_data['user_name']] = $this->generateReportByUser($user_data, $user_id);

			if(strrchr($user_data['position'], "Manager") && !strrchr($user_data['position'], "Junior")) {
				$manager = $user_data['email'];
			}
		}
	}

	private function generateReportByUser($user_data, $user_id)
	{
		$related_to = false;
		$html = array();

		$html["Tasks"] = "<table>";
		$html["Tasks"] .= "<tr><th colspan='9'>Tasks</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>";
		$html["Tasks"] .= "<tr><th>Overdue Tasks</th><th>Today Tasks</th><th>Tomorrow Tasks</th><th>Next Tasks</th><th>Created Tasks</th><th>Quick Tasks</th><th>Sum of Tasks</th><th>Closed Tasks</th><th>Deleted Tasks</th></tr>";
		$html["Tasks"] .= "<tr>";

		foreach($user_data['modules']['Tasks']['type'] as $label => $value) {
			$html["Tasks"] .= "<td align='center'>{$value}</td>";
		}
		$html["Tasks"] .= "</tr>";
		$html["Tasks"] .= "</table>";

		$html["Tasks"] .= "<table>";
		$html["Tasks"] .= "<tr><th colspan='2'>% of tasks related to:</th><th></th></tr>";
		foreach($user_data['modules']['Tasks']['parent_type'] as $label => $value) {
			$html["Tasks"] .= "<tr><td>{$label}:</td><td align='center'>". ($value * 100) ."%</td></tr>";
		}
		$html["Tasks"] .= "</table>";

		unset($user_data['modules']['Tasks']);
		foreach($user_data['modules'] as $module_name => $module_data) {
			$html[$module_name] = "<table>";
			$html[$module_name] .= "<tr><th colspan='2'>{$module_name}</th><th></th></tr>";
			foreach($module_data as $key => $value) {
				$html[$module_name] .= "<tr><td>{$key}:</td><td>{$value}:</td></tr>";
			}

			$html[$module_name] .= "</table>";
		}

		$position = trim($user_data['position']);
		if(preg_match("/Assistant/", $position) || preg_match("/Junior/", $position)) {
			$this->assistants[$user_data['email']] = $this->formatContentForUser($html, $user_data['user_name']);
		}

		return $html;
	}

	public function formatContentForUser($html, $user_name)
	{
		$iter = 0;
		$content = '<table>';
		$content .= "<tr><th align='left'>$user_name</th><th></th></tr>";
		$content .= '<tr><td>'. $html["Tasks"] .'</td><td></td></tr>';
		$content .= '</table>';

		unset($html["Tasks"]);
		$content .= '<table>';
		foreach($html as $module_name => $string) {
			if($iter % 9 == 0) {
				$content .= '<tr><td>'.$string.'</td>';
			} elseif($iter % 9 != 8) {
				$content .= '<td>'.$string.'</td>';
			} else {
				$content .= '<td>'.$string.'</td></tr>';
			}

			$iter++;
		}

		$content .= '</table>';
		return $content;
	}

	public function formatContentForDepartment($department, $dep_name) 
	{
		$content = "<table>";
		$content .= "<tr><th>$dep_name</th><th></th></tr>";

		foreach($department as $user_name => $html) {
			$content .= "<tr><td>". $this->formatContentForUser($html, $user_name) ."</td></tr>";
		}

		$content .= "</table>";
		return $content;
	}
}