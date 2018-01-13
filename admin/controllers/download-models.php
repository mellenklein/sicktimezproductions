<?php
$model = new SortModel('download_models');
$items = $model->get_items(array('order' => 'title'));
$item = array();
$item_names = array(
	'singular'=>'Download Model',
	'plural'=>'Download Models'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$series_id = !empty($url_segments[1]) && ctype_digit($url_segments[1]) ? $url_segments[1] : 0;
$action = !empty($url_segments[2]) ? $url_segments[2] : '';
$id = !empty($url_segments[3]) && ctype_digit($url_segments[3]) ? $url_segments[3] : 0;

$series_db = new Model('download_series');
if($series_id) {
	$series_db->load($series_id);
	$items = $model->get_items(array('order' => 'title', 'where' => 'series_id = ?', 'params' => array($series_id)));
	$module_url .= $series_id.'/';
} else if($action == 'add' || $action == 'edit') {
	
}

if($id){
	$model->load($id);
}

/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){

	$fields = $_POST;
	$add = 0;
	
	if(!empty($model->data['id'])){
		$result = $model->update($fields);
	}
	else{
		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}
	
	if($result !== FALSE){
		set_message('The '.$item_names['singular'].' has been '.($add ? 'added' : 'updated').' successfully.', 'success');
	}
	else{
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
		$model->db->query("DELETE from download_x_model WHERE model_id = ?", array($id));
		set_message('The '.$item_names['singular'].' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$item_names['singular'].'.', 'fail');
	}
	
	header('location:'.$module_url);
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: SORT
* ---------------------------------------------------------------------------*/
if($action == 'sort'){

	$ret = $model->sort($_POST['sort']);
	echo $ret;
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

<h1><?php echo ucfirst($action); ?> Model in <?php echo $series_db->data['title']; ?></h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="series_id" value="<?php echo $series_id; ?>">
	
	<div class="wide_col">
		<div class="form_field">
			<label>Model Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo htmlentities($item['title']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Model Production Years</label>
			<input class="in_text" type="text" name="production_years" id="production_years" value="<?php echo htmlentities($item['production_years']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Model Currently Supported</label>
			<label><input type="radio" name="is_supported" value="1" <?php echo (!isset($item['is_supported']) || $item['is_supported'] == 1) ? 'checked="checked"' : '' ; ?>> Yes</label> <label><input type="radio" name="is_supported" value="0" <?php echo (isset($item['is_supported']) && $item['is_supported'] == 0) ? 'checked="checked"' : '' ; ?>> No</label>
		</div>
		
		<div class="form_field">
			<label>Is this a smoker model?</label>
			<label><input type="radio" name="is_model" value="1" <?php echo (!isset($item['is_model']) || $item['is_model'] == 1) ? 'checked="checked"' : '' ; ?>> Yes</label> <label><input type="radio" name="is_model" value="0" <?php echo (isset($item['is_model']) && $item['is_model'] == 0) ? 'checked="checked"' : '' ; ?>> No</label>
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
		$("#model_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?><?php echo ($series_id) ? ' - '.$series_db->data['title'] : ' - All Series' ; ?></h1>
<p><strong><a href="<?php echo $config->get('admin_url'); ?>download-series"> &laquo; Manage Series</a></strong><?php if($series_id): ?> | <strong><a href="<?php echo $config->get('admin_url'); ?>download-models">View All Models</a></strong><?php endif; ?></p>
<?php if($series_id): ?>
<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?> to <?php echo $series_db->data['title']; ?></a></strong></p>
<?php endif; ?>
<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="model_list" style="width: auto;">
  <thead>
  <tr>
	<th>Model Title</th>
	<th>Series</th>
    <th>Actions</th>
  </tr>
  </thead>
  
  <tbody>
	<?php foreach($items as $i): ?>
	<?php
		$series_db->load($i['series_id']);
	?>
	<tr>
		<td><?php echo $i['title']; ?></td>
		<td><?php echo ($series_id) ? $series_db->data['title'] : '<a href="'.$module_url.$series_db->data['id'].'/">'.$series_db->data['title'].'</a>' ; ?></td>
		<td align="center">
			<a href="<?php echo ($series_id) ? $module_url.'edit/'.$i['id'] : $module_url.$i['series_id'].'/edit/'.$i['id'] ; ?>">Edit</a> | <a href="<?php echo ($series_id) ? $module_url.'delete/'.$i['id'] : $module_url.$i['series_id'].'/delete/'.$i['id'] ; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
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