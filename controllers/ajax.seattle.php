<?php
require_once('classes/Singleton.php');
require_once('classes/Registry.php');
require_once('classes/Database.php');
require_once('classes/Model.php');
require_once('config.php');

$out = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

	if($action == 'contact-form') {

		$form_data = $_POST;

		$admin_to = 'maryellen@foxfuelcreative.com';

		// Store in database
		// $model = new Model('contacts');
		// $post = array(
		// 	'date_submitted' => date('Y-m-d H:i:s'),
		// 	'contact_info' => $form_data['contact_info'],
		// 	'activity' => $form_data['activity'],
		// 	'date' => $form_data['date'],
		// 	'time' => $form_data['time'],
		// );
		// $model->insert($post);

		if($form_data['date'] == 'day1') {
			$meeting_date = 'Friday, Feb. 9th:';
		} else if($form_data['date'] == 'day2') {
			$meeting_date = 'Monday, Feb. 12th';
		} else if($form_data['date'] == 'day3') {
			$meeting_date = 'Tuesday, Feb. 13th';
		} else {
			$meeting_date = 'unspecified';
		}


		// Send email
		$admin_subject = 'Seattle Meeting Request';

		$headers = "From: FoxFuel Creative <info@foxfuelcreative.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$headers .= "Bcc: maryellen@foxfuelcreative.com\r\n";

		$admin_message = '<h4 style="font-size:18px;">There\'s a new meeting request from the form on http://www.foxfuelcreative.com/seattle:</h4>
		<h3>Message Details:</h3>';

		$admin_message .= '<p><strong>Contact Info: </strong>'.$form_data['contact_info'].'</p>
		<p><strong>Is interested in meeting for: </strong>'.$form_data['activity'].'</p>
		<p><strong>On </strong>'.$meeting_date.' at '.$form_data['time'].'.</p>
		<p><strong>Date Submitted: </strong>'.date('m/d/Y H:iA').'</p><br>';


		mail($admin_to, $admin_subject, $admin_message, $headers);

} //end of action
echo $out;
exit();
?>
