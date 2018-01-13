<?php
$key = 'Filedata';
$filename = '';
if( !empty($_POST['sub_dir'])){
	$dir = './'.$_POST['sub_dir'];
} else{
	$dir = './'.$config->get('admin_upload_image_dir');
}

$allowed = array();
if($type == 'image') {
	$allowed = array('jpg','jpeg','gif','png');
}

$check_token = md5($config->get('sitename').$_POST['timestamp']);
$type = $_POST['type'];

// Set specific upload directory based on type
//if($type == 'banner'){
	//$dir .= 'banners/';
//}

if (!empty($_FILES) && $_POST['token'] == $check_token) {
	
	$file = upload_file($dir, $key, $filename, $allowed);
	$filepath = $dir.$file;

	if($file && file_exists($filepath)){
	
		// Image processing specific to type
		if($type == 'image'){
			image_resize($filepath, $filepath, 1200);
		}
		
    echo $file;
	}
	else{
		echo '0';		
	}
}
else{
	echo '0';
}
exit();
?>