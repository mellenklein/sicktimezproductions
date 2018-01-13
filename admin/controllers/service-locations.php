<?php
$model = new Model('service_locations');
$items = $model->get_items(0, 'title');
$item = array();
$item_names = array(
	'singular'=>'Service Location',
	'plural'=>'Service Locations'
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
	$uploadfile = $uploaddir . basename($_FILES['service_locs_file']['name']);
	
	if (move_uploaded_file($_FILES['service_locs_file']['tmp_name'], $uploadfile)) {
		$result = array();
		
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($uploadfile);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($uploadfile);
		$data_arr = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$headers = array(
								'customer',
								'title',
								'address',
								'city',
								'state',
								'zip',
								'contact',
								'phone',
								'phone2',
								'fax',
								'email'
							);
		foreach($data_arr AS $line) {
			if ($line) {
				if (sizeof($line)==sizeof($headers)) {
					$result[] = array_combine($headers,$line);
				}
			}
		}
		
		$rCount = 0;
		if(!empty($result)) {
			foreach ($result AS $r) {
				$rCount++;
				foreach($r AS &$v) {
					$v = htmlentities(trim($v));
				}
				unset($v);
				
				if(!empty($r['address'])) {
					$loc_parts = array($r['address'], $r['city'], $r['state'], $r['zip']);
					foreach($loc_parts AS $k => $v) {
						if(empty($v)) {
							unset($loc_parts[$k]);
						}
					}
					$loc = implode(",", $loc_parts);
					$url = $config->get('google_map_api').urlencode($loc);
					$geo = json_decode(file_get_contents($url), TRUE);
					$r['lat'] = (!empty($geo['results'][0]['geometry']['location']['lat']) && $geo['results'][0]['geometry']['location']['lat'] != 'NULL') ? $geo['results'][0]['geometry']['location']['lat'] : '' ;
					$r['lng'] = (!empty($geo['results'][0]['geometry']['location']['lng']) && $geo['results'][0]['geometry']['location']['lng'] != 'NULL') ? $geo['results'][0]['geometry']['location']['lng'] : '' ;
				}
				
				$model->load($r['title'], 'title');
				if(empty($model->data)) {
					$model->insert($r);
				} else {
					$model->load($model->data['id'], 'id');
					$model->update($r);
				}
				if($rCount % 5 == 0) {
					sleep(1);
				}
			}
		}
	}
	header("Location: ".$config->get('admin_url').'service-locations');
}


/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	
	$fields = $_POST;
	$add = 0;
	
	if(!empty($_POST['address']) || !empty($_POST['state'])) {
		$loc_parts = array($_POST['address'], $_POST['city'], $_POST['state'], $_POST['zip'], $_POST['country']);
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
			<label>Preferred Provider</label>
			<select class="in_text" name="preferred_provider" id="preferred_provider">
				<option value="0" <?php echo ($item['preferred_provider'] == 0) ? 'selected="selected"' : '' ; ?>>No</option>
				<option value="1" <?php echo ($item['preferred_provider'] == 1) ? 'selected="selected"' : '' ; ?>>Yes</option>
			</select>
		</div>
	
		<div class="form_field">
			<label>Customer ID</label>
			<input class="in_text" type="text" name="customer" id="customer" value="<?php echo $item['customer']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo $item['title']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Address</label>
			<input class="in_text" type="text" name="address" id="" value="<?php echo $item['address']; ?>" />
		</div>
		
		<div class="form_field">
			<label>City</label>
			<input class="in_text" type="text" name="city" id="" value="<?php echo $item['city']; ?>" />
		</div>
		
		<div class="form_field">
			<label>State Abbreviation</label>
			<input class="in_text" type="text" name="state" id="" value="<?php echo $item['state']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Zip Code</label>
			<input class="in_text" type="text" name="zip" id="" value="<?php echo $item['zip']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Country</label>
			<input class="in_text" type="text" name="country" id="" value="<?php echo $item['country']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Phone</label>
			<input class="in_text" type="text" name="phone" id="" value="<?php echo $item['phone']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Phone 2</label>
			<input class="in_text" type="text" name="phone2" id="" value="<?php echo $item['phone2']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Fax</label>
			<input class="in_text" type="text" name="fax" id="" value="<?php echo $item['fax']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Email</label>
			<input class="in_text" type="text" name="email" id="" value="<?php echo $item['email']; ?>" />
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
		$("#rest_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>
<!--<br>
<br>
<form action="./service-locations" method="post" enctype="multipart/form-data">
    Select datafile to upload:
    <input type="file" name="service_locs_file" id="service_locs_file"><br>
    <input type="submit" value="Upload File" name="submit_file">
</form>
<br>
<br>
<br>-->
<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="rest_list">
  <thead>
  <tr>
  	<th>Title</th>
  	<th>Preferred Provider</th>
  	<th>City</th>
  	<th>State</th>
  	<th>Phone</th>
    <th>Actions</th>
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['title']; ?></td>
		<td><?php echo ($i['preferred_provider'] == 1) ? '<strong>Yes</strong>' : 'No' ; ?></td>
		<td><?php echo $i['city']; ?></td>
		<td><?php echo $i['state']; ?></td>
		<td><?php echo $i['phone']; ?></td>
		<td align="center">
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a> | <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
		</td>
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