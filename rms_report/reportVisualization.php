<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportVisualization
{
	public $assistants;
	public $managers;
	private $users_html;

	public function __construct($departments)
	{
		$manager = "";
		$previous = "1";
		foreach($departments as $dep_name => $department) {
			$this->generateReportByDepartment($department, $dep_name, $manager);

			if($previous != "1" && $previous != $dep_name) {
				$content = $this->formatContentForDepartment($this->users_html['departments'][$dep_name], $dep_name);
				$this->managers[$manager] = $content;
			}
			$previous = $dep_name;
		}
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