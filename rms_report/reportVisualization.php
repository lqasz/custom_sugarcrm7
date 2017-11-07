<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportVisualization
{
	private $db;
	private $current_user;
	private $all_departments_users;
	private $employee_activities_count;

	public function __construct($current_user)
	{
		$this->all_departments_users = array();
		$this->current_user = $current_user; // current user object
		$this->employee_activities_count = array(); // helper var for hidden table
		$this->db = DBManagerFactory::getInstance();
	}

	public function groupByManagers()
	{
		$users = array();
		// get all active users grouped by their managers
		$query = 'SELECT CONCAT(`u2`.`first_name`, " ", `u2`.`last_name`) AS `m_name`,
						CONCAT(`u1`.`first_name`, " ", `u1`.`last_name`) AS `e_name`,
						`u1`.`id` AS `u1_id`,
						`u2`.`id` AS `u2_id`,
						`aa_departments`.`name` AS `dep_name`
				FROM `users` `u1` 
					LEFT JOIN `users_cstm` 
						ON(`u1`.`id`=`users_cstm`.`id_c`) 
					LEFT JOIN `aa_departments` 
						ON(`aa_departments`.`id`=`aa_departments_id_c`) 
					INNER JOIN `users` `u2` 
						ON(`u2`.`id`=`aa_departments`.`assigned_user_id`) 
				WHERE `u1`.`employee_status`="Active" 
					AND `u1`.`status`="Active" 
					AND `u1`.`show_on_employees`=1
					AND `aa_departments`.`name` NOT LIKE "IT Department"';

		$result = $this->db->query($query);

		if($this->current_user['data']['dep_name'] == "Board" || $this->current_user['data']['user_id'] == "e22f8f47-6388-e3b0-2802-55fbf7383322") {
			while($row = $this->db->fetchByAssoc($result)) {
				$users[$this->current_user['data']['user_id']]['subordinates'][$row['dep_name']][$row['u2_id']] = $row['m_name'];
				$users[$this->current_user['data']['user_id']]['subordinates'][$row['dep_name']][$row['u1_id']] = $row['e_name'];

				$this->all_departments_users[$row['dep_name']][$row['m_name']] = 1;
				$this->all_departments_users[$row['dep_name']][$row['e_name']] = 1;
			}
		} else {
			while($row = $this->db->fetchByAssoc($result)) {
				$users[$row['u2_id']]['subordinates'][$row['u1_id']] = $row['e_name'];

				$this->all_departments_users[$row['dep_name']][$row['m_name']] = 1;
				$this->all_departments_users[$row['dep_name']][$row['e_name']] = 1;
			}
		}

		return $users;
	}

	public function getReportData($user_name)
	{
		$data = array();
		$query = "SELECT * 
				FROM `rms_report_tasks` 
				WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) 
					AND CURRENT_DATE AND `user_name` LIKE '$user_name'
				ORDER BY `date_entered` ASC";

		$result = $this->db->query($query);
		
		while($row = $this->db->fetchByAssoc($result)) {
			$all_data = array();

			foreach($row as $key => $value) {
				if($key != "id" && $key != "user_name" && $key != "date_entered") {
					$all_data[$key] = $value;
				}
			}
			
			$date = date("d-m-Y", strtotime($row["date_entered"]));
			$data[$row["user_name"]][$date]['Tasks'] = $all_data;
		}

		$query = "SELECT * 
				FROM `rms_report_modules` 
				WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) 
					AND CURRENT_DATE AND `user_name` LIKE '$user_name'
				ORDER BY `date_entered` ASC";

		$result = $this->db->query($query);

		while($row = $this->db->fetchByAssoc($result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));
			$json_result = mb_convert_encoding($row['data'], "UTF-8");
			$json_result = str_replace('&quot;', '"', $json_result);
			$json_result = json_decode($json_result, true);

			foreach($json_result as $module_name => $values) {
				$data[$row["user_name"]][$date][$module_name] = $values;	
			}	
		}

		$query = "SELECT * 
				FROM `rms_report_activities` 
				WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) 
					AND CURRENT_DATE AND `user_name` LIKE '$user_name'
				ORDER BY `date_entered` ASC";

		$result = $this->db->query($query);

		while($row = $this->db->fetchByAssoc($result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));

			$data[$row["user_name"]][$date]['Activities']["Bug"] = $row['bugs'];
			$data[$row["user_name"]][$date]['Activities']["Chat"] = $row['chat'];
			$data[$row["user_name"]][$date]['Activities']["Login"]["Normal Login"] = $row['normal_login'];
			$data[$row["user_name"]][$date]['Activities']["Login"]["Login by Mobile"] = $row['mobile_login'];
			$data[$row["user_name"]][$date]['Activities']["Notification"] = $row['notifications'];
		}

		return $data;
	}

	public function getUsersStatistics($period = "week")
	{
		$data = array();
		$where = "ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE";

		if($period == "month") {
			$where = "ADDDATE(CURRENT_DATE,-30) AND CURRENT_DATE";
		} else if($period == "quarter") {
			$where = "ADDDATE(CURRENT_DATE,-90) AND CURRENT_DATE";
		} elseif($period == "year") {
			$where = "ADDDATE(CURRENT_DATE,-365) AND CURRENT_DATE";
		}

		$statistics_result = $this->db->query("SELECT * FROM `rms_statistics` WHERE `date_entered` BETWEEN $where ORDER BY `date_entered` ASC");

		$count = array();
		while($row = $this->db->fetchByAssoc($statistics_result)) {
			$user_name = $row["user_name"];
			unset($row['id']);
			unset($row['user_name']);
			unset($row['date_entered']);

			foreach($row as $w => $value) {
				if(empty($data[$user_name][$w])) {
					$data[$user_name][$w] = $value;
				} else {
					$data[$user_name][$w] += $value;
				}
			}

			if(empty($count[$user_name])) {
				$count[$user_name] = 1;
			} else {
				$count[$user_name]++;
			}
		}

		foreach($count as $user_name => $count) {
			foreach($data[$user_name] as $w => $value) {
				$data[$user_name][$w] = $value / $count;
			}
		}

		return $data;
	}

	public function returnUsersIndicators($data)
	{
		$min = array();
		$max = array();
		$average = array();
		foreach($data as $user_name => $user_indicators) {
			foreach($user_indicators as $w => $value) {
				if(!isset($min[$w])) {
					$min[$w] = $value;
				} else {
					if($min[$w] > $value) {
						$min[$w] = $value;
					}
				}

				if(!isset($max[$w])) {
					$max[$w] = $value;
				} else {
					if($max[$w] < $value) {
						$max[$w] = $value;
					}
				}

				if(!isset($data['Average'][$w])) {
					$data['Average'][$w] = $value;
				} else {
					$data['Average'][$w] += $value;
				}

				$data[$user_name][$w] = $value;
			}
		}

		foreach($data['Average'] as $w => $value) {
			$data['Average'][$w] = ($value / (count($data) - 1));
		}

		$standarized_indicator = array();
		foreach($data as $user_name => $user_indicators) {
			foreach($user_indicators as $w => $value) {
				$max_minus_min = (($val=$max[$w]-$min[$w]) == 0) ? 1 : $val;
				$indicator = (2*($value-$min[$w])/($max_minus_min))-1;
				$change_scope = 50+($indicator*50);

				if(!isset($standarized_indicator[$user_name][$w])) {
					$standarized_indicator[$user_name] = $change_scope;
				} else {
					$standarized_indicator[$user_name] += $change_scope;
				}
			}

			$standarized_indicator[$user_name] /= 1.1;
			$standarized_indicator[$user_name] = $this->floordec($standarized_indicator[$user_name]);
		}

		return $standarized_indicator;
	}

	public function prepareReport()
	{
		$html_content = "";
		$detail_report = array();
		$statistic_report = array();
		$users = $this->groupByManagers();

		if(isset($users[$this->current_user['data']['user_id']])) {
			if($this->current_user['data']['dep_name'] == "Board" || $this->current_user['data']['user_id'] == "e22f8f47-6388-e3b0-2802-55fbf7383322") {
				foreach($users[$this->current_user['data']['user_id']]['subordinates'] as $dep_name => $dep_data) {
					foreach($dep_data as $e_name) {
						$detail = $this->getReportData($e_name);
						$detail_report[$dep_name][$e_name] = $this->generateDetailReportForUser($detail[$e_name], $e_name);
					}
				}
			} else {
				foreach($users[$this->current_user['data']['user_id']]['subordinates'] as $e_name) {
					$detail = $this->getReportData($e_name);
					$detail_report[$e_name] = $this->generateDetailReportForUser($detail[$e_name], $e_name);
				}
			}
		} else {
			$e_name = $this->current_user['data']['user_name'];
			$detail = $this->getReportData($e_name);
			$detail_report[$e_name] = $this->generateDetailReportForUser($detail[$e_name], $e_name);
		}
		
		$statistic_report['week'] = $this->returnStatistics("week");
		$statistic_report['month'] = $this->returnStatistics("month");
		$statistic_report['quarter'] = $this->returnStatistics("quarter");
		$statistic_report['year'] = $this->returnStatistics("year");

		if(isset($users[$this->current_user['data']['user_id']])) {
			if($this->current_user['data']['dep_name'] == "Board" || $this->current_user['data']['user_id'] == "e22f8f47-6388-e3b0-2802-55fbf7383322") {
				foreach($users[$this->current_user['data']['user_id']]['subordinates'] as $dep_name => $dep_data) {
					foreach($dep_data as $e_name) {
						$html_content = $this->getHTMLContent($detail_report, $statistic_report, $this->current_user['data']['dep_name'], true);
					}
				}
			} else {
				$dep_name = $this->current_user['data']['dep_name'];

				foreach($users[$this->current_user['data']['user_id']]['subordinates'] as $e_name) {
					$html_content = $this->getHTMLContent($detail_report, $statistic_report, $dep_name);
				}
			}
		} else {
			$dep_name = $this->current_user['data']['dep_name'];
			$html_content = $this->getHTMLContent($detail_report, $statistic_report, $dep_name);
		}

		return $html_content;
	}

	public function returnStatistics($period)
	{
		$users_indicators = $this->returnUsersIndicators($this->getUsersStatistics($period));
		$statistic_report["main"] = $this->generateStatisticReport($users_indicators, $period);

		foreach($this->all_departments_users as $dep_name => $dep_data) {
			foreach($dep_data as $user_name => $value) {
				$this->all_departments_users[$dep_name][$user_name] = $users_indicators[$user_name];
			}
		}

		$teams_data = array();
		foreach($this->all_departments_users as $dep_name => $dep_data) {
			$statistic_report["by_team"][$dep_name] = $this->generateUsersDepartmentStatisticReport($dep_data, $dep_name, $teams_data, $period);
		}

		
		$statistic_report["teams"] = $this->generateDepartmentsStatisticReport($teams_data, $users_indicators['Average'], $period);

		return $statistic_report;
	}

	public function generateStatisticReport($users_indicators, $period)
	{
		$average = $users_indicators['Average'];
		unset($users_indicators['Average']);
		arsort($users_indicators);

		$html = "<table>
					<tr>
						<th class='employee-name'>Employees Activities</th>
					</tr>
					<tr>
						<td>
							<table>";
		$html .= "<tr><td><b>Average: $average</b></td></tr>";
		$html .= "<tr class='stats-header date-value'><th>Employee</th><th>Ws</th><th>Ws/Average</th></tr>";

		$iter = 1;
		foreach($users_indicators as $user_name => $indicator) {
			$value = ($average != 0) ? number_format(($indicator / $average), 2, '.', '') : 0;
			$warn_class = ($value < 1) ? "warn" : "";
			$info_class = ($value >= 1.00 && $value < 1.02) ? "info" : "";
			$current_user_class = ($this->current_user['data']['user_name'] == $user_name) ? "current-user" : "";

			$html .= "<tr class='stats-user-name $warn_class $info_class $current_user_class'><td>$iter. ". $user_name ."</td><td>". $indicator ."</td><td>". $value ."</td></tr>";

			$iter++;
		}

		$this->employee_activities_count[$period]['all'] = $iter-1;

		$html .= "				</table>
							</td>
						</tr>
					</table>";
		return $html;
	}

	public function generateDepartmentsStatisticReport($teams_data, $average, $period)
	{
		arsort($teams_data);
		$html = "<table class='table-header'>
					<tr>
						<th class='employee-name'>Departments Activities</th>
					</tr>
					<tr>
						<td>
							<table>";
		$html .= "<tr><td><b>Average: $average</b></td></tr>";
		$html .= "<tr class='stats-header date-value'><th>Department</th><th>Ws</th><th>Ws/Average</th></tr>";

		$iter = 1;
		foreach($teams_data as $team => $indicator) {
			$value = ($average != 0) ? number_format(($indicator / $average), 2, '.', '') : 0;			
			$class = ($value < 1) ? "warn" : "";
			$info_class = ($value >= 1.00 && $value < 1.02) ? "info" : "";
			$current_user_class = ($this->current_user['data']['dep_name'] == $team) ? "current-user" : "";

			$html .= "<tr class='stats-user-name $class $current_user_class $info_class'><td>$iter. ". $team ."</td><td>". $indicator ."</td><td>". $value ."</td></tr>";

			$iter++;
		}

		$this->employee_activities_count[$period]['teams'] = $iter-1;
		
		$html .= "				</table>
							</td>
						</tr>
					</table>";
		return $html;
	}

	public function generateUsersDepartmentStatisticReport($dep_data, $dep_name, &$teams, $period) 
	{
		$average = 0;
		$count = count($dep_data);
		
		foreach($dep_data as $user_name => $indicator) {
			$average += $indicator;
		}

		$average /= $count;
		$average = number_format($average, 2, '.', '');
		$teams[$dep_name] = $average;

		$html = "<table>
					<tr>
						<th class='employee-name'>$dep_name Activities</th>
					</tr>
					<tr>
						<td>
							<table>";
		$html .= "<tr><td><b>Average: $average</b></td></tr>";
		$html .= "<tr class='stats-header date-value'><th>Employee</th><th>Ws</th><th>Ws/Average</th></tr>";

		$iter = 1;
		arsort($dep_data);
		foreach($dep_data as $user_name => $indicator) {
			$value = ($average != 0) ? number_format(($indicator / $average), 2, '.', '') : 0;
			$class = ($value < 1) ? "warn" : "";
			$info_class = ($value >= 1.00 && $value < 1.01) ? "info" : "";
			$current_user_class = ($this->current_user['data']['user_name'] == $user_name) ? "current-user" : "";

			$html .= "<tr class='stats-user-name $class $current_user_class $info_class'><td>$iter. ". $user_name ."</td><td>". $indicator ."</td><td>". $value ."</td></tr>";

			$iter++;
		}

		$this->employee_activities_count[$period][$dep_name] = $iter-1;

		$html .= "				</table>
							</td>
						</tr>
					</table>";
		return $html;
	}

	public function generateDetailReportForUser($user_data, $employee)
	{
		$iter1 = 0;
		$language_pack = array(
			"overdue_tasks" => "Overdue Tasks",
			"today_tasks" => "Today Tasks",
			"tomorow_tasks" => "Tomorow Tasks",
			"next_tasks" => "Next Tasks",
			"created_tasks" => "Created Tasks",
			"closed" => "Closed Tasks",
			"deleted" => "Deleted Tasks",
			"quick_tasks" => "Quick Tasks",
			"sum" => "Sum of Existing Tasks",
		);
		$html = "<table class='employee-table table-header detail-table-header'>
					<tr class='employee-name'>
						<th style='vertical-align: bottom;'>$employee</th>
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

			foreach($date_data as $module_name => $module_data) {
				if($iter1 == 0) {
					$horizontal_html .= "<tr class='module-name'>
							<th class='module-name-row'>$module_name</th>
							<td class='module-name-row'>
								<table class='single-module-horizontal-table'>";
				}

				$date_data_html .= " <tr class='module-data'>
										<td class='module-name-row'>
											<table class='single-module-table'>";

				foreach($module_data as $type => $data_type) {
					$class_name = "";

					if(!is_array($data_type) && ($data_type == 0 || ($data_type > 0 && $type == "overdue_tasks"))) {
						$class_name = "warn";
					}

					if($iter1 == 0) {
						$horizontal_html .= "<tr class='module-type horizontal-header'>";
					}

					$date_data_html .= "<tr class='module-type complex-type-data'>";

					if($module_name == "Tasks") {

						if($iter1 == 0) {
							$horizontal_html .= "<td>"
												. $language_pack[$type] .
												"</td>";
						}
						$date_data_html .= "<td class='align-center $class_name'>"
											. $data_type .
											"</td>";
					} else if($module_name != "Activities") {
						if($iter1 == 0) {
							$horizontal_html .= "<td class='type-row'>". $type ."</td>";
						}

						$date_data_html .= "<td class='align-center $class_name'>". $data_type ."</td>";
					} else {
						if(!is_array($data_type)) {
							if($iter1 == 0) {
								$horizontal_html .= "<td>". $type ."</td>";
							}

							$date_data_html .= "<td class='align-center $class_name'>". $data_type ."</td>";
						} else {
							if($iter1 == 0) {
								$horizontal_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr class='submodule-type-name'>
													<td>
														<table class='submodule-values'>";

								if($type = "Login") {
									foreach($data_type as $key => $value) {
										$horizontal_html .= "<tr class='submodel-value'>
													<td class='type-row $class_name'>". $key ."</td>
												</tr>";
									}
								}
							}

							$date_data_html .= "<td colspan='2'>
											<table class='submodule-type-table'>
												<tr>
													<td>
														<table class='submodule-values'>";

							if($type == "Login") {
								foreach($data_type as $key => $value) {
									if($value == 0) {
										$class_name = "warn";
									}

									$date_data_html .= "<tr class='submodel-value'>
												<td class='align-center $class_name'>". $value ."</td>
											</tr>";
								}
							}

							if($iter1 == 0) {
								$horizontal_html .= "	</table>
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

		return $html;
	}

	public function getHTMLContent($detail_report, $statistic_report, $dep_name, $board = false)
	{
		$html_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
						<meta charset="utf-8" /> 
							<style>
								body {
							  		color: #000;
							  		font-family: Lato;
							  		font-size: 13px;
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
								}
								table {
									width: 100%;
								}
								.align-center {
									text-align: center;
								}
								.horizontal-content {
									width: 500px;
								}
								.modules-table>tr {
									background-color: #F5F5F5;
								}
								.submodel-value td {
									height: 20px;
								}
								.type-row {
									width: 90%;
								}
								.module-name-row {
									text-align: center;
									border-bottom: 1px solid #000;
								}
								.module-name-row .horizontal-header {
									text-align: right;
								}
								.module-type:not(:last-child) {
									border-bottom: 1px dotted #70b933;
								}
								.module-type:nth-child(even) {
									background-color: #F5F5F5;
								}
								.module-type td {
									padding: 2px;
								}
								.module-name-value, .date-value {
									border-top: 1px solid #000;
									border-bottom: 1px solid #000;
								}
								th.module-name-row {
									background-color: #70b933;
									text-align: center;
									color: white;
								}
								.warn {
									font-weight: bold;
									color: red;
								}
								.info {
									font-weight: bold;
									color: blue;
								}
								.stats-user-name:nth-child(even) {
									background-color: #F5F5F5;
								}
								.stats-user-name {
									text-align: center;
									border-bottom: 1px solid #000;
								}
								.stats-user-name td {
									padding: 2px;
								}
								.stats-header {
									border-bottom: 1px solid #000;
									border-top: 1px solid #000;
								}
								.stats-user-name td:first-child {
									text-align: left;
								}
								.table-header {
									margin-top: 5%;
								}
								.detail-table-header {
									margin-top: 0 !important;
								}
								.current-user {
									background-color: yellow !important;
								}
								.employee-table {
									height: 1220px;
								}
							</style>
						</head>
						<body>';
		
		$html_content .= '<table>
					<tr>
						<td align="left">
							'. date('Y') .' - '. date('m') .' - '. date('d') .'
						</td>
						<td align="center" style="font-weight: bold; font-size: 12px;">
							<h1>RMS Activities Report</h1>
						</td>
						<td align="right">
							<img src="https://rms.reesco.pl/custom/themes/default/images/company_logo.png" />
						</td>
					</tr>
				</table>';
		$html_content .= '<table>';
		$html_content .= '<tr><td height="20" colspan="3"></td></tr>';
		$html_content .= '</table>';

		$html_content .= '<table>';

		$iter = 1;
		foreach($statistic_report as $period => $report) {
			$html_content .= '<tr style="height: 90px;">
								<th colspan="3" align="left">
									'.$iter.') BETWEEN TODAY AND LAST '.  strtoupper($period) .'
								</th>
								<th></th>
								<th></th>
							</tr>';
			$html_content .= '<tr style="height: 1100px;">';
			$html_content .= '<td style="width: 47.5%;">
								<table class="table-header">
									<tr>
										<td>'. $report['main'] .'</td>
									</tr>
								</table>
							</td>';
			$html_content .= '<td style="width: 5%;"></td>';

			$new_table_count = $this->employee_activities_count[$period]['all'] - $this->employee_activities_count[$period]['teams'] - $this->employee_activities_count[$period][$dep_name] - 1;

			$new_table = "<table>";
			for($i = 0; $i < $new_table_count; $i++) { 
				$new_table .= "<tr><td>&nbsp;</td></tr>";
			}
			$new_table .= "</table>";

			$html_content .= '<td style="width: 47.5%;">
								<table class="table-header">
									<tr>
										<td>'. $report["by_team"][$dep_name] .'</td>
									</tr>
									<tr>
										<td>'. $report['teams'] .'</td>
									</tr>
									<tr>
										<td>'. $new_table .'</td>
									</tr>
								</table>
							</td>';
			$html_content .= '</tr>';

			$iter++;
		}
		$html_content .= '<tr><td colspan="3">';

		if($board) {
			foreach($detail_report as $dep_name => $dep_data) {
				// $html_content .= "<table></table>";
				foreach($dep_data as $e_name => $user_report) {
					if(!($e_name == "Artur Winnicki" && $dep_name == "Development Department")) {
						$html_content .= $user_report;
					}
				}
			}
		} else {
			foreach($detail_report as $e_name => $user_report) {
				$html_content .= $user_report;
			}
		}

		$html_content .= '			</td>
								</tr>
							</table>
						</body>
					</html>';

		return $html_content;
	}

	private function floordec($zahl, $decimals = 2) 
	{    
		return floor($zahl * pow(10, $decimals)) / pow(10, $decimals);
	}
}