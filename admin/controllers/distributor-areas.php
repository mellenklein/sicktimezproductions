<?php
$model = new Model('distributor_areas');
$items = $model->get_items(0, 'title');
$item = array();
$item_names = array(
	'singular'=>'Distributor Area',
	'plural'=>'Distributor Areas'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
}

if(isset($_POST["submit_file"])) {
	/** PHPExcel_IOFactory */
	require_once dirname(__FILE__) . '/../classes/PHPExcel/Classes/PHPExcel/IOFactory.php';
	
	
	$uploaddir = '/home/southpri/public_html/admin/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['distributor_areas_file']['name']);
	
	if (move_uploaded_file($_FILES['distributor_areas_file']['tmp_name'], $uploadfile)) {
		$result = array();
		
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($uploadfile);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($uploadfile);
		$data_arr = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$headers = array(
								'zip',
								'county',
								'state',
								'territory',
								'sys_id'
							);
		foreach($data_arr AS $line) {
			if ($line) {
				if (sizeof($line)==sizeof($headers)) {
					if(strpos(strtolower($line['A']), 'zip') !== false) {
						
					} else {
						$result[] = array_combine($headers,$line);
					}
				}
			}
		}
		
		
		/*$fp = fopen($uploadfile,'r');
		if (($headers = fgetcsv($fp, 0, ",")) !== FALSE) {
			if ($headers) {
				$headers = array(
									'zip',
									'county',
									'state',
									'territory',
									'sys_id'
								);
				while (($line = fgetcsv($fp, 0, ",")) !== FALSE) {
					if ($line) {
						if (sizeof($line)==sizeof($headers)) {
							$result[] = array_combine($headers,$line);
						}
					}
				}
			}
		}
		fclose($fp);*/
		if(!empty($result)) {
			$sql = "TRUNCATE TABLE distributor_areas";
			$testit = $model->db->query($sql, array());
			foreach ($result AS &$r) {
				foreach($r AS &$v) {
					$v = htmlentities(trim($v));
				}
				$model->insert($r);
			}
		}
	}
	header("Location: ".$config->get('admin_url').'distributor-areas');
}

/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	
	$fields = $_POST;
	$add = 0;
	
	if(!empty($_POST['address']) || !empty($_POST['state'])) {
		$loc_parts = array($_POST['address'], $_POST['city'], $_POST['state'], $_POST['zip']);
		foreach($loc_parts AS $k => $v) {
			if(empty($v)) {
				unset($loc_parts[$k]);
			}
		}
		$loc = implode(",", $loc_parts);
		$url = $config->get('google_map_api').urlencode($loc);
		$geo = json_decode(file_get_contents($url), TRUE);
		$fields['lat'] = $geo['results'][0]['geometry']['location']['lat'];
		$fields['lng'] = $geo['results'][0]['geometry']['location']['lng'];
	}
	
	
	if(!empty($model->data['id'])){
		$result = $model->update($fields);
	} else {
		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}
	
	if($result !== FALSE){
		set_message('The '.$item_names['singular'].' has been '.($add ? 'added' : 'updated').' successfully.', 'success');
	} else {
		set_message('There was a problem '.($add ? 'adding' : 'updating').' the '.$item_names['singular'].'.', 'fail');
	}
	
	header('location:'.$module_url.($id ? 'edit/'.$id : ''));
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: DELETE
* ---------------------------------------------------------------------------*/
if($action == 'delete'){

	$result = $model->delete();
	if($result !== FALSE){
		set_message('The '.$item_names['singular'].' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$item_names['singular'].'.', 'fail');
	}
	
	header('location:'.$module_url);
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: ADD/EDIT
* ---------------------------------------------------------------------------*/
if($action == 'add' || $action == 'edit'){

	$item = $model->data;
	$view = 'edit';
	$rte = 1; // Include tinymce

}

ob_start();
?>

<?php
/* ----------------------------------------------------------------------------
* VIEW: EDIT
* ---------------------------------------------------------------------------*/
if($view == 'edit'):
?>

<script type="text/javascript">
$(function() {
	
});
</script>

<h1><?php echo ucfirst($action); ?> Content</h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
	
		<div class="form_field">
			<label>Territory</label>
			<input class="in_text" type="text" name="territory" id="territory" value="<?php echo $item['territory']; ?>" />
		</div>
	</div>
	
	<div class="clear"><!-- x --></div>
  
  <div class="action_buttons">
		<input class="btn yes" type="submit" onClick="checkTextData();" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
  	<input class="btn no" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
	</div>
</form>

<?php
/* ----------------------------------------------------------------------------
* VIEW: LIST (DEFAULT)
* ---------------------------------------------------------------------------*/
else:
?>
<script type="text/javascript">
	$(document).ready(function(){
		
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>
<br>
<br>
<form action="./distributor-areas" method="post" enctype="multipart/form-data">
    Select datafile to upload:
    <input type="file" name="distributor_areas_file" id="distributor_areas_file"><br>
    <input type="submit" value="Upload File" name="submit_file">
</form>
<br>
<br>
<br>
<!--<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>-->

<?php if(!empty($items)): ?>
<p>Total Areas: <?php echo count($items); ?></p>
<table class="cms_table" id="rest_list">
  <thead>
  <tr>
  	<th>Zip</th>
  	<th>County</th>
  	<th>State</th>
	<th>Territory</th>
  	<th>sys ID</th>
    <!--<th>Actions</th>-->
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['zip']; ?></td>
		<td><?php echo $i['county']; ?></td>
		<td><?php echo $i['state']; ?></td>
		<td><?php echo $i['territory']; ?></td>
		<td><?php echo $i['sys_id']; ?></td>
		<!--<td align="center">
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a> | <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
		</td>-->
	</tr>
	
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>

<?php
/* ----------------------------------------------------------------------------
* END VIEWS
* ---------------------------------------------------------------------------*/
endif;
?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>