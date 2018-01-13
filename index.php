<?php
session_start();
// phpinfo();
//    die();

// Load dependencies
require_once('classes/Singleton.php');
require_once('classes/Registry.php');
require_once('classes/Database.php');
require_once('classes/Model.php');
// require_once('classes/Member.php');

require_once('config.php');
require_once('includes/helper_functions.php');
// require_once('includes/about_content.php');

$thinBannerURL = 'templates/thin-banner.php';
$heroNav = 'templates/nav.php';
$audioPlayer = 'templates/audio-player.php';
// $newsSidebar = 'templates/news-sidebar.php';

// Store url segments in array
$url_segments = array();
if (!empty($_GET['qs'])) {
	$url_segments = explode('/', $_GET['qs']);
}

// Routing - match first segment to:
// 1. Controller filename
// 2. Page

$page = new Model('pages') ;
$ctrl = 'home';

if (!empty($url_segments[0])) {
  $filepath = 'controllers/'.$url_segments[0].'.php';
  if (is_file($filepath)) {
    $ctrl = $url_segments[0];
  }
	else {
		$page->load($url_segments[0], 'slug');
		if ($page->id) {
			$ctrl = 'page';
		}
	}
}

$filepath = 'controllers/'.$ctrl.'.php';
if (is_file($filepath)) {
  include($filepath);
}


// Output template
include('templates/template.php');
?>
