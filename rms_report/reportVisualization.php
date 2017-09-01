<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set("display_errors", 1);

/**
* Class used to get all data from task table
*/
class ReportVisualization
{
	private $users_html;

	public function __construct($departments)
	{
		foreach($departments as $dep_id => $department) {
			$this->users_html['departments'][$dep_id] = "";
			$this->generateReportByDepartment($department, $dep_id);
		}
	}

	private function generateReportByDepartment($department, $dep_id)
	{	
		foreach($department as $user_id => $user_data) {
			$this->users_html['users'][$user_id] = "";
			$this->users_html['departments'][$dep_id] .= $this->generateReportByUser($user_data, $user_id);

			echo $this->users_html['users'][$user_id];
		}
	}

	private function generateReportByUser($user_data, $user_id)
	{
		$this->users_html['users'][$user_id] .= "<tr>".
							"<td>". $user_data['user_name']."</td>".
							"<td>". $user_data['Tasks']['type']['overdue_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['today_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['tomarrow_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['next_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['sum'] ."</td>".
							"<td>". $user_data['Tasks']['type']['quick_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['created_tasks'] ."</td>".
							"<td>". $user_data['Tasks']['type']['closed'] ."</td>".
							"<td>". $user_data['Tasks']['type']['deleted'] ."</td>".
						"</tr>";

		return $this->users_html['users'][$user_id];
	}

	public function sendReportForAssistant($user_id)
	{
		dump($user_id);
	}

	public function sendReportForManager($user_id)
	{
		dump($user_id);
	}

	public function sendReportForSupervisior($user_id)
	{
		dump($user_id);
	}
}