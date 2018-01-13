<?php
$out = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if($action == 'quiz') {
	
	require_once('classes/Quiz.php');
	$quiz = new Quiz();
	$json = array(
		'num_correct' => 0,
		'answers' => array(),
		'user_answers' => $_POST['quizData'],
		'status' => 'error',
		'quiz_id' => $_POST['quizId']
	);
	
	// Load quiz from posted id
	$quiz->load($_POST['quizId']);
	if (!empty($quiz->data)) {
		if (is_array($_POST['quizData'])) {
			
			// Loop through questions, get correct answer from DB and compare to posted
			foreach ($_POST['quizData'] as $q) {
				$q = explode(':', $q);
				$ans = $quiz->get_correct_answer($q[0]);
				$json['answers'][] = $ans;
				if ($ans == $q[1]) {
					$json['num_correct']++;
				}
			}
			$json['status'] = 'ok';
			
			// Track
			$score = $json['num_correct'].'/'.count($_POST['quizData']);
			$answers = json_encode($_POST['quizData']);
			$quiz->track($user->id, $quiz->id, $score, $answers);
		}
	}
	
	$out = json_encode($json);
} else if($action == 'ram-email') {
	$return = false;
	$ram_db = new Model('rams');
	$ram_db->load($_POST['ram_id']);
	if(!empty($ram_db->data)) {
		$to = $ram_db->data['email'];
		
		$subject = 'TEPSupport RAM Contact - '.date('m/d/Y H:iA');
		
		$headers = "From: TEPSupport <no-reply@tepsupport.com>\r\n";
		$headers .= "Reply-To: TEPSupport <no-reply@tepsupport.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		
		$message = '<h4>Contact my RAM message:</h4>';
		$message .= '<p><strong>Name: </strong>'.$_POST['name'].'</p>';
		$message .= '<p><strong>Email: </strong>'.$_POST['email'].'</p>';
		$message .= '<p><strong>Message: </strong><br>'.$_POST['message'].'</p>';
		if(!empty($to)) {
			mail($to, $subject, $message, $headers);
		} else {
			mail('nick.tate@asurion.com', $subject, $message, $headers);
		}
		$return = true;
	}
	$out = json_encode($return);
} else if($action == 'contact-email') {
	$return = false;
	$to = "nick.tate@asurion.com";
	
	$subject = 'TEPSupport Contact Form - '.date('m/d/Y H:iA');
	
	$headers = "From: TEPSupport <no-reply@tepsupport.com>\r\n";
	$headers .= "Reply-To: TEPSupport <no-reply@tepsupport.com>\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	$message = '<h4>Contact form message:</h4>';
	$message .= '<p><strong>Name: </strong>'.$_POST['name'].'</p>';
	$message .= '<p><strong>Email: </strong>'.$_POST['email'].'</p>';
	$message .= '<p><strong>Message: </strong><br>'.$_POST['message'].'</p>';
	
	if(mail($to, $subject, $message, $headers)) {
		$return = true;
	}
	$out = json_encode($return);
} else if($action == 'quiz-email') {
	require_once('classes/Quiz.php');
	$quiz = new Quiz();
	$json = array();
	$return = false;
	
	// Load quiz from posted id
	$quiz->load($_POST['quiz_data']['quiz_id']);
	if (!empty($quiz->data)) {
		if(!empty($_POST['form_data']['to_email'])) {
			$to = $_POST['form_data']['to_email'];

			$subject = 'Asurion TEP Quiz Results';

			$headers = "From: TEPSupport <no-reply@tepsupport.com>\r\n";
			$headers .= "Reply-To: TEPSupport <no-reply@tepsupport.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$message = '<p>You received this email because '.$_POST['form_data']['from_name'].' wants you to see their TEP Quiz results. Here\'s how they did:</p>';
			$message .= '<p>Your score: <strong>'.(($_POST['quiz_data']['num_correct'] / count($quiz->data['questions'])) * 100).'%</strong> ('.$_POST['quiz_data']['num_correct'].' out of '.count($quiz->data['questions']).')</p>';
			$message .= '<br><br>';
			foreach($quiz->data['questions'] AS $k => $q) {
				$user_answer_data = explode(':', $_POST['quiz_data']['user_answers'][$k]);
				$user_answer_id = $user_answer_data[1];
				$message .= '<h3>'.($k+1).'. '.$q['content'].'</h3>';
				$message .= '<ul>';
				foreach($q['answers'] AS $a) {
					if($a['id'] == $user_answer_id) {
						if($a['correct']) {
							$message .= '<li style="color: #11a500;"><strong>'.$a['content'].'</strong></li>';
						} else {
							$message .= '<li style="color: #d20000;"><strong>'.$a['content'].'</strong></li>';
						}
					} else {
						$message .= '<li>'.$a['content'].'</li>';
					}
				}
				$message .= '</ul>';
			}
			
			mail($to, $subject, $message, $headers);
			$return = true;
		}
	}
	
	$out = json_encode($return);
}
elseif ($action == 'video') {
	
	$ra = new Model('resource_access');
	$ra->load($_POST['trackId']);
	if (!empty($ra->data['user_id']) && $ra->data['user_id'] == $user->id) {
		$data = array('video_complete' => 1);
		$ra->update($data);
	}
	
}
elseif ($action == 'zipcode-lookup') {
	$store_db = new Model('stores');
	$zip_matches = $store_db->get_items(array('where' => 'store_type = 2 AND zip = ?', 'params' => array($_POST['zip_code'])));
	$addresses = array();
	$zip_matches_return = array();
	foreach($zip_matches AS $k => $zm) {
		if(!in_array($zm['address'].$zm['address2'], $addresses)) {
			$zip_matches_return[] = $zm;
			$addresses[] = $zm['address'].$zm['address2'];
		}
	}
	$out = json_encode($zip_matches_return);
}
elseif ($action == 'log-attendance') {
	$attendance_db = new Model('attendance');
	unset($_POST['action']);
	$check_attendance = $attendance_db->get_items(array('where' => 'user_id = ? AND resource_id = ?', 'params' => array($_POST['user_id'], $_POST['resource_id'])));
	if(!empty($check_attendance)) {
		$res = 0;
	} else {
		$res = $attendance_db->insert($_POST);
	}
	$out = json_encode($res);
}

echo $out;
exit();
?>