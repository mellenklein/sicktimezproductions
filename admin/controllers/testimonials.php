<?php
$model = new SortModel('testimonials');
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Testimonial',
	'plural'=>'Testimonials'
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
	$upload_dir = $config->get('admin_upload_img_dir').'testimonials/';

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
	// Create URL tag
	$('#name').blur(function(){
		var name = $(this).val();
		var company = $('#company').val();
		var spacer = '';
		if(name.length > 0 && company.length > 0) {
			spacer = ' ';
		}
		var title = name+spacer+company;
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'testimonial_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#company').blur(function(){
		var name = $('#name').val();
		var company = $(this).val();
		var spacer = '';
		if(name.length > 0 && company.length > 0) {
			spacer = ' ';
		}
		var title = name+spacer+company;
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'testimonial_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#slug').blur(function(){
		var slug = $(this).val();
		if(slug != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'testimonial_slug',slug:slug, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#logo_upload').uploadifive({
		'uploadScript' : '<?php echo $config->get('admin_url'); ?>upload',
		'onCancel' : function(event,fileObj,data) {
			$('#logo_filename').val('');
			$('#logo_view').html('');
		},
		'queueSizeLimit' : 1,
		'buttonText' : 'Upload Image',
		'height' : 24,
		'onUploadComplete' : function(file, data) {
			if(data != '0'){
				$('#logo_filename').val(data);
				$('#logo_view').html('<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir; ?>'+data+'" />');
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
			'type':'testimonial-logo',
			'sub_dir':'<?php echo $upload_dir ?>'
		}
	
	});
	$('form').on('click', '#logo_remove', function(evt){
		evt.preventDefault();
		removeImage($('#logo_filename'), 'logo', 'testimonials', $('#logo_view'));
	});
	function removeImage(file_ele, field, table, element) {
		var file = file_ele.val();
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
			<label>Speaker Full Name</label>
			<input class="in_text" type="text" name="name" id="name" value="<?php echo htmlentities($item['name']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Speaker Company</label>
			<input class="in_text" type="text" name="company" id="company" value="<?php echo htmlentities($item['company']); ?>" />
		</div>
		
		<div class="form_field">
			<label>URL Slug</label>
			<div class="note">Letters, numbers, and dashes only. No spaces. Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
			<input class="in_text" type="text" name="slug" id="slug" value="<?php echo $item['slug']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Speaker's Position at Company</label>
			<input class="in_text" type="text" name="position" id="position" value="<?php echo htmlentities($item['position']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Photo/Logo</label>
			<div id="logo_view">
				<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/testimonials/'.$item['logo']))): ?>
				<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['logo']; ?>" alt="" />
				<p><a href="#" id="logo_remove" > Remove this image</a></p>
				<?php endif; ?>
			</div>
			<input type="file" name="logo" id="logo_upload" />
			<input type="hidden" name="logo" id="logo_filename" value="<?php echo $item['logo']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Testimonial Quote</label>
			<p class="note">Do not include quotation marks. They will be added later.</p>
			<textarea class="in_text" name="quote" rows="10"><?php echo $item['quote']; ?></textarea>
		</div>
		
		<div class="form_field">
			<label>Page Content</label>
			<textarea class="mce" name="content"><?php echo $item['content']; ?></textarea>
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
	<th>Speaker</th>
  	<th>Company</th>
   <th>Actions</th>
  </tr>
  <tbody class="sortable" rel="testimonials">
	<?php foreach($items as $i): ?>
	
	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['name']; ?></td>
		<td><?php echo $i['company']; ?></td>
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