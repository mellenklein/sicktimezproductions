<?php
/*****
* Create registry object for config data.
* Access throughout application using $config->get('property_name')
*****/

$url = 'http://sicktimez.productions';

$config = Registry::getInstance();

$config->set('sitename', 'Sick Timez Productions');
$config->set('url', $url);

$config->set('db_host', 'localhost');
$config->set('db_user', 'sicktime_user');
$config->set('db_pass', 'S!ckT!m3z');
$config->set('db_name', 'sicktime_db');

$config->set('meta_title', 'Sick Timez Productions');
$config->set('meta_desc', 'Sick Timez Productions | Stoke Your Sound. | Nashville, TN');
$config->set('meta_keys', 'Sick Timez Productions');

$config->set('admin_url', $url.'/admin/');
$config->set('admin_upload_img_dir', './../assets/images/');

$output = array('head'=>'', 'content'=>'', 'footer_js'=>'');

//client logo - used in admin template.php
$config->set('client_logo', '/admin/images/op-logo-color.png');

// Get items from site info table
$info_model = new Model('site_info');
$info = $info_model->get_items();
foreach ($info as $i) {
	$config->set($i['name'], $i['value']);
}
?>
