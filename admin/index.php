<?php
session_start();

// Load dependencies
require_once('../classes/Singleton.php');
require_once('../classes/Registry.php');
require_once('../classes/Database.php');
require_once('../classes/Model.php');
// require_once('../classes/Member.php');
require_once('classes/Admin.php');
require_once('../includes/helper_functions.php');
require_once('../config.php');

// Deal with magic quotes
if(get_magic_quotes_gpc()) {
	$_GET = strip_slashes_deep($_GET);
	$_POST = strip_slashes_deep($_POST);
	$_COOKIE = strip_slashes_deep($_COOKIE);
}

// Create user object to check authentication
$user = new Admin();

// Set default controller based on logged in status
$ctrl = $user->id ? 'home' : 'login';

if((!isset($user->id) || empty($user->id) || !isset($_SESSION['dashboard_bg'])) && !isset($_POST['username'])) {
	$dashboard_bg = "FOXF_CMS".rand(1, 16).".jpg";
	$_SESSION['dashboard_bg'] = $dashboard_bg;
}

// Store URL segments in array
$url_segments = explode('/', $_GET['qs']);

if(!empty($url_segments[0]) && $url_segments[0] != 'login'){
	// Attempting to access controller. Are we logged in?

	if($user->id){
		// Yep, try to match the requested controller
		$filepath = 'controllers/'.$url_segments[0].'.php';
		if(is_file($filepath)){
			$ctrl = $url_segments[0];
		}
	} else {
		// Nope, redirect to login
		header('location:'.$config->get('admin_url'));
		exit();
	}
}

$module_url = $config->get('admin_url').$ctrl.'/';

$filepath = 'controllers/'.$ctrl.'.php';
if(is_file($filepath)){
	include($filepath);
}

$nav_active = (isset($nav_active)) ? $nav_active : $ctrl ;
// Output template
include('template.php');
?>
