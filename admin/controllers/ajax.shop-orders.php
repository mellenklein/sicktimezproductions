<?php

$order_db = new Model('shop_orders');

$response = false;
$action = isset($_POST['action']) ? $_POST['action'] : '';
unset($_POST['action']);


if($action == 'mark_paid'){
	$order_db->load($_POST['order_id']);
	$response = $order_db->update(array('paid' => 1));
}
if($action == 'mark_shipped'){
	$order_db->load($_POST['order_id']);
	$response = $order_db->update(array('shipped' => 1));
}
if($action == 'del_card'){
	$order_db->load($_POST['order_id']);
	$response = $order_db->update(array('ccNum' => '', 'CVV' => '', 'expMon' => '', 'expYear' => ''));
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>