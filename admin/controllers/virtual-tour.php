<?php
$model = new SortModel('virtual_tour');
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Virtual Tour Item',
	'plural'=>'Virtual Tour Items'
);
$view = 'list';

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
	
	if(!empty($model->data['id'])){
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
	$uploader = 1; // Include uploadify
	$timestamp = time(); // Needed for uploadify
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
	$('#featured_image_upload').uploadifive({
		'uploadScript' : '<?php echo $config->get('admin_url'); ?>upload',
		'onCancel' : function(event,fileObj,data) {
			$('#featured_image_filename').val('');
			$('#featured_image_view').html('');
		},
		'queueSizeLimit' : 1,
		'buttonText' : 'Upload Image',
		'height' : 24,
		'onUploadComplete' : function(file, data) {
			if(data != '0'){
				$('#featured_image_filename').val(data);
				$('#featured_image_view').html('<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir; ?>'+data+'" />');
			}
			else{
				alert('The file could not be uploaded. The file must be a valid JPG, GIF, or PNG image.');
			}
		},
		'onError' : function(errorType, file) {
			alert('The file ' + file.name + ' could not be uploaded: ' + errorType);
		},
		'formData' : {
			'timestamp':'<?php echo $timestamp;?>',
			'token':'<?php echo md5($config->get('sitename').$timestamp);?>',
			'type':'featured_image',
			'sub_dir':'<?php echo $upload_dir ?>'
		}
	
	});
	$('form').on('click', '#featured_image_remove', function(evt){
		evt.preventDefault();
		removeImage($('#featured_image_filename'), 'featured_image', 'virtual_tour', $('#featured_image_view'));
	});
	function removeImage(file_ele, field, table, element) {
		var file = '<?php echo dirname ( __FILE__ ).'/../../assets/images/virtual_tour/'; ?>'+file_ele.val();
		if (window.confirm("Do you really want to delete this image?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:field, table:table, id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					element.html('');
					file_ele.val('');
				}
			});
		}
	}
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
			<label>Description</label>
			<textarea class="mce" name="description"><?php echo $item['description']; ?></textarea>
		</div>
		
		<div class="form_field">
			<label>Tour Image</label>
			<div id="featured_image_view">
				<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/virtual_tour/'.$item['featured_image']))): ?>
				<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['featured_image']; ?>" alt="" />
				<p><a href="#" id="featured_image_remove" > Remove this image</a></p>
				<?php endif; ?>
			</div>
			<input type="file" name="featured_image" id="featured_image_upload" />
			<input type="hidden" name="featured_image" id="featured_image_filename" value="<?php echo $item['featured_image']; ?>" />
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
   <th>Actions</th>
  </tr>
  <tbody class="sortable" rel="virtual-tour">
	<?php foreach($items as $i): ?>
	
	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['title']; ?></td>
		<td><img style="max-width: 100%; width: 120px;" src="<?php echo $config->get('admin_upload_img_dir').'virtual_tour/'.$i['featured_image']; ?>" alt="" /></td>
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