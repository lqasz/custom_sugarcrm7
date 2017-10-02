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
	public $language_pack;
	public $week_tasks;
	public $week_notifi;
	public $week_modules;

	public function __construct()
	{
		$this->week_tasks = array();
		$this->db = DBManagerFactory::getInstance();

		$this->language_pack = array(
			"overdue_tasks" => "Overdue Tasks",
			"today_tasks" => "Today Tasks",
			"tomorow_tasks" => "Tomorow Tasks",
			"next_tasks" => "Next Tasks",
			"created_tasks" => "Created Tasks",
			"closed" => "Closed Tasks",
			"deleted" => "Deleted Tasks",
			"quick_tasks" => "Quick Tasks"
		);
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
		$tasks_result = $this->db->query("SELECT * FROM `rms_report_tasks` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE");
		while($row = $this->db->fetchByAssoc($tasks_result)) {
			$json_result = array();
			$all_parent_types = array();

			foreach($row as $key => $json) {
				if($key != "id" && $key != "user_name" && $key != "date_entered") {
					$json_result[$key] = mb_convert_encoding($json, "UTF-8");
					$json_result[$key] = str_replace('&quot;', '"', $json_result[$key]);
					$json_result[$key] = json_decode($json_result[$key], true);

					if($key != "sum") {
						foreach($json_result[$key] as $parent_type => $value) {
							if($parent_type != "all") {
								$this->week_tasks[$row["user_name"]][$parent_type] = $value;
							}
						}
					}
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
			
			foreach($notification_result as $severity => $value) {
				$this->week_notifi[$row["user_name"]][$severity] = $value;
			}

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
		$rms_users = array();
		$data = $this->getReportData();
		$users = $this->getUsersDepartments();

		foreach($users as $dep_name => $users_values) {
			foreach($users_values as $manager => $employees) {
				if($manager != "Mateusz Ruszkowski") {
					$rms_users[$manager][] = $this->generateReportForUser($data[$manager], $manager);
					foreach($employees as $key => $employee) {
						$rms_users[$manager][] = $this->generateReportForUser($data[$employee], $employee);
					}
				}
			}
		}



		dump($rms_users);
		die();
	}

	public function generateReportForUser($user_data, $employee)
	{
		$iter1 = 0;
		$html = "<table class='employee-table'>
					<tr class='employee-name'>
						<th>$employee</th>
					</tr>
					<tr class='employee-data'>
						<td>
							<table class='dates-table'>
								<tr class='dates'>";

		$date_data_html = "";
		$horizontal_html = "";
		foreach($user_data as $date => $date_data) {
			if($iter1 == 0) {
				$horizontal_html .= "<td class='horizontal-content'><table class='module-name-table'>
							<tr class='module-name-value'>
								<th>Module Name</th>
							</tr>
							<tr>
								<td>
									<table class='horizontal-modules-table'>";
			}

			$date_data_html .= "<td  class='date-content'><table class='single-date-table'>
							<tr class='date-value'>
								<th>$date</th>
							</tr>
							<tr>
								<td>
									<table class='modules-table'>";

			$i = 0;
			foreach($date_data as $module_name => $module_data) {
				$class_name = "";

				if($i % 2 == 0) {
					$class_name = "gray";
				}

				if($iter1 == 0) {
					$inline_css = "";
					if($module_name != "Tasks" && $module_name != "Activities") {
						$inline_css = "style='text-align: right'";
					}
					$horizontal_html .= "<tr class='module-name $class_name'>
							<th $inline_css class='module-name-row'>$module_name</th>
							<td class='module-name-row'>
								<table class='single-module-horizontal-table'>";
				}

				$date_data_html .= " <tr class='$class_name module-data'>
										<td class='module-name-row'>
											<table class='single-module-table'>";

				foreach($module_data as $type => $data_type) {
					if($iter1 == 0) { 
						if(($type == "created_tasks" || $type == "quick_tasks" || $type == "closed" || $type == "deleted") || $module_name != "Tasks") {

							if($module_name == "Tasks") {
								$horizontal_html .= "<tr class='module-type task-type-data'>";
							} else {
								$horizontal_html .= "<tr class='module-type'>";
							}
						} else {
							$horizontal_html .= "<tr class='module-type horizontal-header'>";
						}
					}

					if(($type == "created_tasks" || $type == "quick_tasks" || $type == "closed" || $type == "deleted") || $module_name != "Tasks") {

						if($module_name == "Tasks") {
							$date_data_html .= "<tr class='module-type simple-type-data task-type-data'>";
						} else {
							$date_data_html .= "<tr class='module-type simple-type-data'>";
						}
					} else {
						$date_data_html .= "<tr class='module-type complex-type-data'>";
					}

					if($module_name == "Tasks") {
						if($type != "sum") {
							if($type == "created_tasks" || $type == "quick_tasks" || $type == "closed" || $type == "deleted") {
								if($iter1 == 0) {
									$horizontal_html .= "<td>&nbsp;</td>
														<td>&nbsp;</td>";
								}
								$date_data_html .= "<td class='type-row'>". $this->language_pack[$type] ."</td>
											<td class='align-center'>". $data_type['all'] ."</td>";
							} else {
								if($iter1 == 0) {
									$horizontal_html .= "<td colspan='2'>
															<table>
																<tr>
																	<th colspan='2'>". $this->language_pack[$type] ."
																	</th>
																	<td>
																		<table>";

									foreach($this->week_tasks[$employee] as $parent_type => $val) {
										$value = (!empty($data_type[$parent_type])) ? $data_type[$parent_type] : 0 ;
										$horizontal_html .= "<tr class='submodel-value'>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>";
									}
								}
								$date_data_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr>
													<td>
														<table class='submodule-values'>";

								foreach($this->week_tasks[$employee] as $parent_type => $val) {
									$value = (!empty($data_type[$parent_type])) ? $data_type[$parent_type] : 0 ;
									$date_data_html .= "<tr class='submodel-value'>
												<td class='type-row'>". $parent_type ."</td>
												<td class='align-center'>". $value ."</td>
											</tr>";
								}

								$date_data_html .= "	</table>
													</td>
												</tr>
											</table>
										</td>";

								if($iter1 == 0) {
									$horizontal_html .= "	</table>
													</td>
												</tr>
											</table>
										</td>";
								}
							}
						}
					} else if($module_name != "Activities") {
						if(!is_array($data_type)) {
							$date_data_html .= "<td class='type-row'>". $type ."</td>
												<td class='align-center'>". $data_type ."</td>";

							if($iter1 == 0) {
								$horizontal_html .= "<td>&nbsp;</td>
													<td>&nbsp;</td>";
							}
						} else {
							if($data_type['all'] != 0) {
								if($iter1 == 0) {
									$horizontal_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr class='submodule-type-name'>
													<th colspan='2'>". $type ."</th>
													<td>
														<table class='submodule-values'>";
								
									foreach($data_type as $key => $value) {
										if($key != "all") {
											$horizontal_html .= "<tr class='submodel-value'>
														<td>&nbsp;</td>
														<td>&nbsp;</td>
													</tr>";
										}
									}
								}

								$date_data_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr>
													<td>
														<table class='submodule-values'>";
								
								foreach($data_type as $key => $value) {
									if($key != "all") {
										$date_data_html .= "<tr class='submodel-value'>
													<td class='type-row'>". $key ."</td>
													<td class='align-center'>". $value ."</td>
												</tr>";
									}
								}

								$date_data_html .= "	</table>
													</td>
												</tr>
											</table>
										</td>";
								if($iter1 == 0) {
									$horizontal_html .= "</table>
													</td>
												</tr>
											</table>
										</td>";
								}
							} else {
								$date_data_html .= "<td class='type-row'>". $type ."</td>
													<td class='align-center'>". 0 ."</td>";
								if($iter1 == 0) {
									$horizontal_html .= "<td>&nbsp;</td>
														<td>&nbsp;</td>";
								}
							}
						}
					} else {
						if(!is_array($data_type)) {
							$date_data_html .= "<td class='type-row'>". $type ."</td>
												<td class='align-center'>". $data_type ."</td>";
							if($iter1 == 0) {
								$horizontal_html .= "<td>&nbsp;</td>
													<td>&nbsp;</td>";
							}
						} else {
							if($iter1 == 0) {
								$horizontal_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr class='submodule-type-name'>
													<th colspan='2'>". $type ."</th>
													<td>
														<table class='submodule-values'>";

								if($type != "Login") {
									foreach($this->week_notifi[$employee] as $severity => $val) {
										$value = (!empty($data_type[$severity])) ? $data_type[$severity] : 0 ;
										$horizontal_html .= "<tr class='submodel-value'>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>";
									}
								} else {
									foreach($data_type as $key => $value) {
										$horizontal_html .= "<tr class='submodel-value'>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>";
									}
								}
							}

							$date_data_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr>
													<td>
														<table class='submodule-values'>";

							if($type != "Login") {
								foreach($this->week_notifi[$employee] as $severity => $val) {
									$value = (!empty($data_type[$severity])) ? $data_type[$severity] : 0 ;
									$date_data_html .= "<tr class='submodel-value'>
												<td class='type-row'>". $severity ."</td>
												<td class='align-center'>". $value ."</td>
											</tr>";
								}
							} else {
								foreach($data_type as $key => $value) {
									$date_data_html .= "<tr class='submodel-value'>
												<td class='type-row'>". $key ."</td>
												<td class='align-center'>". $value ."</td>
											</tr>";
								}
							}

							if($iter1 == 0) {
								$horizontal_html .= "		</table>
													</td>
												</tr>
											</table>
										</td>";
							}

							$date_data_html .= "		</table>
													</td>
												</tr>
											</table>
										</td>";
						}
					}

					if($iter1 == 0) { $horizontal_html .= "</tr>"; }
					$date_data_html .= "</tr>";
				}

				$date_data_html .= "</table>
							</td>
						</tr>";

				if($iter1 == 0) {
					$horizontal_html .= "</table>
								</td>
							</tr>";
				}

				$i++;
			}

			$date_data_html .= "	</table>
								</td>
							</tr>
						</table>
					</td>";

			if($iter1 == 0) {
				$horizontal_html .= "	</table>
								</td>
							</tr>
						</table>
					</td>";
			}
			
			$iter1++;
		}

		$html .= $horizontal_html.$date_data_html;
		$html .= "				</tr>
							</table>
						</td>	
					</tr>
				</table>";

		// echo $html; die();
		// echo $horizontal_html; die();

		$html_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
							<style>
								body {
							  		color: #000;
								}
								table {
									border-collapse: collapse;
								}
								.employee-name {
									margin-bottom: 1em;
									font-size: 1.5em;
									font-weight: bold;
									text-align: center;
								}
								.module-name-value, .date-value {
									background-color: #70b933;
									border: 1px solid #70b933;
									font-weight: normal;
									text-align: center;
									color: white;
									height: 40px;
								}
								tbody, tr, th, td {
									padding: 0;
									white-space: normal;
								}
								.gray {
									background-color: rgba(94,93,82,.1);
								}
								.module-name>th {
									width: 60%;
									border-left: 1px solid #000;
									border-top: 1px solid #000;
									border-bottom: 1px solid #000;
								}
								.module-name>td {
									border-top: 1px solid #000;
									border-bottom: 1px solid #000;
								}
								.module-data>td {
									border-top: 1px solid #000;
								}
								table {
									width: 100%;
								}
								.align-center {
									text-align: center;
								}
								.horizontal-header>td {
									border-bottom: 1px solid #000;
								}
								.complex-type-data>td {
									border-bottom: 1px solid rgba(94,93,82,0);
								}
								.horizontal-content {
									width: 500px;
								}
								.modules-table>tr {
									background-color: #F5F5F5;
								}
								.submodel-value {
									border-bottom: 1px solid #000;
									border-right: 1px solid #000;
								}
								.submodel-value td {
									height: 20px;
								}
								.single-module-horizontal-table .submodel-value,
								.horizontal-header .submodel-value {
									border-bottom: 1px solid rgba(94,93,82,0);
									border-right: 1px solid rgba(94,93,82,0);
								}
								.type-row {
									width: 30%;
								}
								.task-type-data {
									border-right: 1px solid #000;
									border-bottom: 1px solid #000;
								}
								.single-module-horizontal-table .task-type-data {
									border-right: 1px solid rgba(94,93,82,0);
									border-bottom: 1px solid rgba(94,93,82,0);
								}
							</style>
						</head>
						<body>';
		$html_content .= $html;
		$html_content .= '</body>
					</html>';

		echo $html_content; die();
		return $html_content;
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