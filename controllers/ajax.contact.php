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

		$admin_to = 'maryellenklein@gmail.com';

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

		// Send email
		$admin_subject = 'New Message For Sick Timez Productions';

		$headers = "From: Sick Timez Productions <info@sicktimez.productions>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$headers .= "Bcc: maryellenklein@gmail.com\r\n";

		$admin_message = '<h4 style="font-size:18px;">There\'s a new message from the form on the Sick Timez website:</h4>
		<h3>Message Details:</h3>';

		$admin_message .= '<p><strong>Name: </strong>'.$form_data['name'].'</p>
		<p><strong>Email: </strong>'.$form_data['email'].'</p>
		<p><strong>Message: </strong>'.$form_data['message'].'.</p>
		<p><strong>Date Submitted: </strong>'.date('m/d/Y H:iA').'</p><br>';


		mail($admin_to, $admin_subject, $admin_message, $headers);

} //end of action
echo $out;
exit();
?>
