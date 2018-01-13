<?php
$model = new SortModel('bulletins');
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Service Bulletin',
	'plural'=>'Service Bulletins'
);
$view = 'list';

$download_category_db = new Model('bulletins_categories');
$download_categories = $download_category_db->get_items(array('order' => 'sort'));

$dcats_by_id = array();
foreach($download_categories AS $c) {
	$dcats_by_id[$c['id']] = $c['title'];
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

	$fields = $_POST;
	$add = 0;

	if(!empty($_FILES["download_file"]['name'])) {
		$fields['file'] = upload_file('./../assets/bulletins/', 'download_file');
	}

	if(!empty($model->data['id'])){
		if(!empty($model->data['file']) && isset($fields['file'])) {
			unlink(dirname ( __FILE__ ).'/../../assets/bulletins/'.$model->data['file']);
		}
		$result = $model->update($fields);
	} else {
		$lastItem = $model->get_items(array('order' => 'sort DESC', 'limit' => 1));

		$lastSort = $lastItem[0]['sort'];
		$fields['sort'] = $lastSort+1;
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

	if(!empty($model->data['file'])) {
		unlink(dirname ( __FILE__ ).'/../../assets/bulletins/'.$model->data['file']);
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
		var file = '<?php echo dirname ( __FILE__ ).'/../../assets/bulletins/'; ?>'+$('#cur_file').attr('data-file');
		if (window.confirm("Do you really want to delete this file?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:'file', table:'bulletins', id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					$('#cur_file').remove();
				}
			});
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
			<select name="bulletin_category_id" class="in_text" id="bulletin_category_id">
				<?php foreach($download_categories AS $dc): ?>
					<option value="<?php echo $dc['id']; ?>" <?php echo ($item['bulletin_category_id'] == $dc['id']) ? 'selected="selected"' : '' ; ?>><?php echo $dc['title']; ?></option>
				<?php endforeach; ?>
			</select>
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

<h1><?php echo $item_names['plural']; ?></h1>

<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table">
  <tr>
  	<th>Sort</th>
	<th>Title</th>
	<th>File</th>
	<th>Category</th>
   <th>Actions</th>
  </tr>
  <tbody class="sortable" rel="bulletins">
	<?php foreach($items as $i): ?>

	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['title']; ?></td>
		<td><a href="<?php echo $config->get('url').'assets/bulletins/'.$i['file']; ?>"><?php echo $i['file']; ?></a></td>
		<td><?php echo $dcats_by_id[$i['bulletin_category_id']]; ?></td>
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
