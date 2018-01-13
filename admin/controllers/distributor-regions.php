<?php
$model = new Model('distributor_regions');
$items = $model->get_items(0, 'title');
$item = array();
$item_names = array(
	'singular'=>'Distributor Region',
	'plural'=>'Distributor Regions'
);
$view = 'list';


$distributors_db = new Model('distributors');
$allDistributors = $distributors_db->get_items(array('order' => 'territory'));


// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
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
			<select class="in_text" name="territory" id="territory">
				<?php foreach($allDistributors AS $d): ?>
					<option value="<?php echo $d['territory']; ?>" <?php echo ($item['territory'] == $d['territory']) ? 'selected="selected"' : '' ; ?>><?php echo $d['territory']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="form_field">
			<label>Region Title</label>
			<input class="in_text" type="text" name="region" id="" value="<?php echo $item['region']; ?>" />
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
<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="rest_list">
  <thead>
  <tr>
  	<th>Territory</th>
  	<th>Region</th>
   <th>Actions</th>
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['territory']; ?></td>
		<td><?php echo $i['region']; ?></td>
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