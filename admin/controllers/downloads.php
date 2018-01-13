<?php
$model = new Download();
$items = $model->get_items(array('order' => 'title'));
$item = array();
$item_names = array(
	'singular'=>'Download Item',
	'plural'=>'Download Items'
);
$view = 'list';

$download_category_db = new Model('download_categories');
$download_categories = $download_category_db->get_items(array('order' => 'sort'));

$download_x_model_db = new Model('download_x_model');
$download_models = $model->db->fetch("SELECT m.*, s.title AS stitle FROM download_models m, download_series s WHERE m.series_id = s.id ORDER BY s.title, m.title");

$dcats_by_id = array();
foreach($download_categories AS $c) {
	$dcats_by_id[$c['id']] = $c['title'];
}

$dmodels_by_id = array();
foreach($download_models AS $m) {
	$dmodels_by_id[$m['id']] = $m['stitle'].' - '.$m['title'];
}


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
	
	$download_model_ids = $_POST['download_model_ids'];
	unset($_POST['download_model_ids']);
	if(!isset($_POST['show_on_all_models'])) {
		$_POST['show_on_all_models'] = 0;
	}
	
	
	$fields = $_POST;
	$add = 0;
	
	if(!empty($_FILES["download_file"]['name'])) {
		$fields['file'] = upload_file('./../assets/downloads/', 'download_file');
	}
	
	if(!empty($model->data['id'])){
		if(!empty($model->data['file']) && isset($fields['file'])) {
			unlink(dirname ( __FILE__ ).'/../../assets/downloads/'.$model->data['file']);
		}
		$result = $model->update($fields);
	} else {
		/*$lastItem = $model->get_items(array('order' => 'sort DESC', 'limit' => 1));
		
		$lastSort = $lastItem[0]['sort'];
		$fields['sort'] = $lastSort+1;*/
		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}
	
	if($result !== FALSE){
		$download_x_model_db->db->query("DELETE FROM download_x_model WHERE download_id = ?", array($id));
		if($_POST['show_on_all_models'] == 0) {
			foreach($download_model_ids AS $dmi) {
				$download_x_model_db->insert(array('download_id' => $id, 'model_id' => $dmi));
			}
		}
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

	if(!empty($model->data['file'])) {
		unlink(dirname ( __FILE__ ).'/../../assets/downloads/'.$model->data['file']);
	}
	
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
	$upload_dir = $config->get('admin_upload_img_dir').'virtual_tour/';

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
	$('#remove-file').click(function(evt){
		evt.preventDefault();
		var file = '<?php echo dirname ( __FILE__ ).'/../../assets/downloads/'; ?>'+$('#cur_file').attr('data-file');
		if (window.confirm("Do you really want to delete this file?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:'file', table:'downloads', id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					$('#cur_file').remove();
				}
			});
		}
	});
	$('#all_models').change(function(evt){
		$('.single_model').prop('checked', false);
		if(!$('#all_models').prop('checked')) {
			$('#all_models').prop('checked', true);
		}
	});
	$('.single_model').change(function(evt){
		$('#all_models').prop('checked', false);
		if($('.single_model:checked').length <= 0) {
			$('#all_models').prop('checked', true);
		}
	});
});
</script>

<h1><?php echo ucfirst($action); ?> Content</h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
		
		<div class="form_field">
			<label>Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo htmlentities($item['title']); ?>" />
		</div>
		
		<div class="form_field">
			<label>File</label>
			<?php if(isset($item['file']) && !empty($item['file'])): ?>
				<p id="cur_file" data-file="<?php echo $item['file']; ?>">
					<?php echo $item['file']; ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<a href="#remove-file" id="remove-file">Remove File</a>
				</p>
			<?php endif; ?>
			<input type="file" name="download_file" id="download_file" />
		</div>
		
		<div class="form_field">
			<label>Category</label>
			<select name="download_category_id" class="in_text" id="download_category_id">
				<?php foreach($download_categories AS $dc): ?>
					<option value="<?php echo $dc['id']; ?>" <?php echo ($item['download_category_id'] == $dc['id']) ? 'selected="selected"' : '' ; ?>><?php echo $dc['title']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="action_buttons">
			<input class="btn yes" type="submit" onClick="checkTextData();" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
			<input class="btn no" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
		</div>
	
	</div>
	
	<div class="thin_col">
		<div class="form_field">
			<h3>Series / Models</h3>
			<label><input type="checkbox" id="all_models" value="1" name="show_on_all_models" <?php echo ($item['show_on_all_models'] == 1 || $action == 'add') ? 'checked="checked"' : '' ; ?>> All Models</label>
			<hr>
			<?php foreach($download_models AS $dm): ?>
				<label><input type="checkbox" class="single_model" value="<?php echo $dm['id']; ?>" name="download_model_ids[]" <?php echo (isset($item['models_ids']) && in_array($dm['id'], $item['models_ids'])) ? 'checked="checked"' : '' ; ?>> <?php echo $dm['stitle'].' - '.$dm['title']; ?></label>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="clear"><!-- x --></div>
</form>

<?php
/* ----------------------------------------------------------------------------
* VIEW: LIST (DEFAULT)
* ---------------------------------------------------------------------------*/
else:
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#downloads_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>

<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="downloads_list">
  <thead>
  <tr>
	<th>Title</th>
	<th>File</th>
	<th>Category</th>
   <th>Actions</th>
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['title']; ?></td>
		<td><a href="<?php echo $config->get('url').'assets/downloads/'.$i['file']; ?>"><?php echo $i['file']; ?></a></td>
		<td><?php echo $dcats_by_id[$i['download_category_id']]; ?></td>
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