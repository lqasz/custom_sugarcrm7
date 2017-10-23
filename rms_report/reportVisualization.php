<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportVisualization
{
	public $db;
	public $managers;
	public $assistants;
	public $week_notifi;
	public $language_pack;

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
			"quick_tasks" => "Quick Tasks",
			"sum" => "Sum of Existing Tasks",
		);
	}

	public function getReportData()
	{
		$data = array();
		$tasks_result = $this->db->query("SELECT * FROM `rms_report_tasks` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE ORDER BY `date_entered` ASC");
		while($row = $this->db->fetchByAssoc($tasks_result)) {
			$json_result = array();
			$all_parent_types = array();

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

		$modules_result = $this->db->query("SELECT * FROM `rms_report_modules` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE ORDER BY `date_entered` ASC");
		while($row = $this->db->fetchByAssoc($modules_result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));
			$json_result = mb_convert_encoding($row['data'], "UTF-8");
			$json_result = str_replace('&quot;', '"', $json_result);
			$json_result = json_decode($json_result, true);

			foreach($json_result as $module_name => $values) {
				$data[$row["user_name"]][$date][$module_name] = $values;	
			}	
		}

		$activities_result = $this->db->query("SELECT * FROM `rms_report_activities` WHERE DATE(`date_entered`) BETWEEN ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE ORDER BY `date_entered` ASC");
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

	public function getUsersStatistics($period = "last_week")
	{
		$data = array();
		$where = "ADDDATE(CURRENT_DATE,-5) AND CURRENT_DATE";

		$statistics_result = $this->db->query("SELECT * FROM `rms_statistics` ORDER BY `date_entered` ASC");

		$count = array();
		while($row = $this->db->fetchByAssoc($statistics_result)) {
			$date = date("d-m-Y", strtotime($row["date_entered"]));
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
		$detail_report = array();
		$detail = $this->getReportData();
		$users = $this->getUsersDepartments();
		$statistic_report = $this->generateStatisticReportForUsers($this->getUsersStatistics());
		
		foreach($users as $dep_name => $users_values) {
			foreach($users_values as $manager => $employees) {
				if($manager != "Mateusz Ruszkowski") {
					$detail_report[$manager][] = $this->generateDetailReportForUser($detail[$manager], $manager);

					foreach($employees as $key => $employee) {
						$detail_report[$manager][] = $this->generateDetailReportForUser($detail[$employee], $employee);
					}
				}
			}
		}
	}

	public function generateStatisticReportForUsers($data)
	{
		$html = '<html>
					<head>
  						<style>
  							html{font-family:Lato Light;}
  						</style>
						<script type="text/javascript">';

		$weights = array(
			'w1' => 0.15,
			'w2' => 0.15,
			'w3' => 0.15,
			'w4' => 0.1,
			'w5' => 0.05,
			'w6' => 0.05,
			'w7' => 0.05,
			'w8' => 0.1,
			'w9' => 0.05,
			'w10' => 0.05,
			'w11' => 0.1,
		);

		$min = array();
		$max = array();
		foreach($data as $user_name => $user_indicators) {
			foreach($user_indicators as $w => $value) {
				$value = $weights[$w] * $value;

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

				$data[$user_name][$w] = $value;
			}
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



		dump($standarized_indicator);
		die();
		$regresion = rtrim($regresion,", ");
		$avg_val = rtrim($avg_val,", ");
		$day_val = rtrim($day_val,", ");
		$dates = rtrim($dates,", ");

		// echo $regresion ."<br/>";
		// echo $avg_val ."<br/>";
		// echo $dates ."<br/>";
		// echo $day_val ."<br/>";die();

		$html .= 'var canvas;
				var context;
				var Val_max;
				var Val_min;
				var sections;
				var xScale;
				var yScale;
				// Values for the Data Plot, they can also be obtained from a external file
				// var Day =  ['. $day_val .'];
				// var Avg =   ['. $avg_val .'];
				var Day =  [54, 56, 57, 45, 48];
				var Avg =   [52, 53, 54.5, 55.75, 50.375];

				function init() {
					// set these values for your data 
					// sections = '.$sections.';
					sections = 5;
					// Val_max = '.$val_max.';
					// Val_min = '.$val_min.';
					Val_max = 70;
					Val_min = 0;
					var stepSize = 5;
					var columnSize = 50;
					var rowSize = 50;
					var margin = 10;
					// var xAxis = [" ", '.$dates.'] 
					var xAxis = [" ", "09-10-2017", "10-10-2017", "11-10-2017", "12-10-2017", "13-10-2017"]
					//
						
					canvas = document.getElementById("canvas");
					context = canvas.getContext("2d");
					context.fillStyle = "#0099ff"
					context.font = "20 pt Lato Light"
					
					yScale = (canvas.height - columnSize - margin) / (Val_max - Val_min);
					xScale = (canvas.width - rowSize) / sections;
					
					context.strokeStyle="#009933"; // color of grid lines
					context.beginPath();
					// print Parameters on X axis, and grid lines on the graph
					for (i=1;i<=sections;i++) {
						var x = i * xScale;
						context.fillText(xAxis[i], x,columnSize - margin);
						context.moveTo(x, columnSize);
						context.lineTo(x, canvas.height - margin);
					}
					// print row header and draw horizontal grid lines
					var count =  0;
					for (scale=Val_max;scale>=Val_min;scale = scale - stepSize) {
						var y = columnSize + (yScale * count * stepSize); 
						context.fillText(scale, margin,y + margin);
						context.moveTo(rowSize,y)
						context.lineTo(canvas.width,y)
						count++;
					}
					context.stroke();
					
					context.translate(rowSize,canvas.height + Val_min * yScale);
					context.scale(1,-1 * yScale);
					
					// Color of each dataplot items	
					context.strokeStyle="#FF0066";
					plotData(Day);
					context.strokeStyle="#000";
					plotData(Avg);
				}

				function plotData(dataSet) {
					context.beginPath();
					context.moveTo(0, dataSet[0]);
					for (i=1;i<sections;i++) {
						context.lineTo(i * xScale, dataSet[i]);
					}
					context.stroke();
				}';
		$html .= '	</script>
				</head>
				<body onLoad="init()">
					<div align="center">
					<h2>'. $employee .'</h2>

					<canvas id="canvas" height="400" width="650">
					</canvas>
					<br>
						<!--Legends for Dataplot -->
					<span style="color:#FF0066"> Day Value</span>
					<span style="color:#000"> Avg </span>
					</div>
				</body>
				</html>';

echo $html; die();
	}

	public function generateDetailReportForUser($user_data, $employee)
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
												. $this->language_pack[$type] .
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

								if($type != "Login") {
									foreach($this->week_notifi[$employee] as $severity => $val) {
										$value = (!empty($data_type[$severity])) ? $data_type[$severity] : 0 ;
										$horizontal_html .= "<tr class='submodel-value'>
													<td class='type-row'>". $severity ."</td>
												</tr>";
									}
								} else {
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

							if($type != "Login") {
								foreach($this->week_notifi[$employee] as $severity => $val) {
									$value = (!empty($data_type[$severity])) ? $data_type[$severity] : 0 ;

									if($value == 0) {
										$class_name = "warn";
									}

									$date_data_html .= "<tr class='submodel-value'>
													<td class='align-center $class_name'>". $value ."</td>
												</tr>";
								}
							} else {
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

		// echo $html; die();
		// echo $horizontal_html; die();

		$html_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
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
							</style>
						</head>
						<body>';
		$html_content .= $html;
		$html_content .= '</body>
					</html>';

		echo $html_content; die();
		return $html_content;
	}

	function floordec($zahl, $decimals = 2){    
		return floor($zahl*pow(10,$decimals)) / pow(10,$decimals);
	}
}