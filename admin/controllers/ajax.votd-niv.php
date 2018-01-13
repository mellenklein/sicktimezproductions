<?php

$votd_niv_db = new Model('votd_niv');

$response = false;
$action = isset($_POST['action']) ? $_POST['action'] : '';
unset($_POST['action']);

if($action == 'save_votd_niv'){
	
	$_POST['product_title_color'] = str_replace('#', '', $_POST['product_title_color']);
	$_POST['product_desc_color'] = str_replace('#', '', $_POST['product_desc_color']);
	
	if(!empty($_POST['date'])) {
		unset($_POST['date']);
		if(!empty($_POST['id'])) {
			$votd_niv_db->load($_POST['id']);
			if(!empty($votd_niv_db->data)) {
				$votd_niv_db->update($_POST);
				$response = $_POST;
			}
		}
	}
}

if($action == 'delImage'){
	if(!empty($_POST['id']) && ctype_digit($_POST['id'])) {
		$votd_niv_db->load($_POST['id']);
		$response = $votd_niv_db->update(array('verse_image' => ''));
	}
}

if($action == 'delete_votd_niv'){
	if(!empty($_POST['id']) && ctype_digit($_POST['id'])) {
		$votd_niv_db->load($_POST['id']);
		$response = $votd_niv_db->delete();
	}
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>