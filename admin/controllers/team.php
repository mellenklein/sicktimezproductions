<?php
$model = new Model('team');
$items = $model->get_items(array('order' => 'active, sort'));
$item = array();
$item_names = array(
	'singular'=>'Team Member',
	'plural'=>'Team Members'
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
	}
	else{
		$lastItem = $model->get_items(array('order' => 'sort DESC', 'limit' => 1));

		if(!empty($lastItem)) {
			$lastSort = $lastItem[0]['sort'];
			$fields['sort'] = $lastSort+1;
		} else {
			$fields['sort'] = 1;
		}


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
	$upload_dir = $config->get('admin_upload_img_dir').'team/';
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
		if(name != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {
				action:'news_slug',
				name:name,
				id:<?php echo $id; ?>
			},
			function(data){
				console.log(data);
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#slug').blur(function(){
		var slug = $(this).val();
		if(slug != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'news_slug',slug:slug, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
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
		removeImage($('#featured_image_filename'), 'featured_image', 'team', $('#featured_image_view'));
	});
	
	function removeImage(file_ele, field, table, element) {
		var file = '../assets/images/team/'+file_ele.val();
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

<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo ucfirst($action); ?> Team Members</h3>
		<div class="clear"><!-- x --></div>
		<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-9">

					<div class="row">
						<div class="col-sm-6">
							<div class="form_field">
								<label>Active</label>
								<label style="display: inline-block; margin-right: 14px;"><input class="" type="radio" name="active" id="active" value="1" <?php echo ((!isset($id) || empty($id)) || $item['active'] == 1) ? 'checked="checked"' : '' ; ?> /> Yes</label>
								<label style="display: inline-block;"><input class="" type="radio" name="active" id="active" value="0" <?php echo (isset($item['active']) && $item['active'] == 0) ? 'checked="checked"' : '' ; ?> /> No</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form_field">
								<label>Partner</label>
								<label style="display: inline-block; margin-right: 14px;"><input class="" type="radio" name="partner" id="partner" value="1" <?php echo ((!isset($id) || empty($id)) || $item['partner'] == 1) ? 'checked="checked"' : '' ; ?> /> Yes</label>
								<label style="display: inline-block;"><input class="" type="radio" name="partner" id="partner" value="0" <?php echo (isset($item['partner']) && $item['partner'] == 0) ? 'checked="checked"' : '' ; ?> /> No</label>
							</div>
						</div>
					</div>

					<div class="form_field">
						<label>Name</label>
						<input class="in_text" type="text" name="name" id="name" value="<?php echo $item['name']; ?>" />
					</div>
					<div class="form_field">
						<label>Title</label>
						<input class="in_text" type="text" name="title" id="title" value="<?php echo $item['title']; ?>" />
					</div>

					<div class="form_field">
						<label>Email</label>
						<input class="in_text" type="text" name="email" id="email" value="<?php echo $item['email']; ?>" />
					</div>

					<div class="form_field">
						<label>Phone</label>
						<input class="in_text" type="text" name="phone" id="phone" value="<?php echo $item['phone']; ?>" />
					</div>

					<div class="form_field">
						<label>About Section</label>
						<div class="note">A short list for the about content</div>
						<textarea class="mce" name="about"><?php echo $item['about']; ?></textarea>
					</div>

					<div class="form_field">
						<label>Short Bio</label>
						<div class="note">A teaser blurb for the team page</div>
						<textarea class="mce" name="blurb"><?php echo $item['blurb']; ?></textarea>
					</div>

					<div class="form_field">
						<label>Full Bio</label>
						<div class="note">Full bio content</div>
						<textarea class="mce" name="content"><?php echo $item['content']; ?></textarea>
					</div>

				</div>

				<div class="col-md-3">

					<div class="form_field">
						<label>URL Slug</label>
						<div class="note">Letters, numbers, and dashes only. No spaces.<br>Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
						<input class="in_text" type="text" <?php if(!isset($item['lock_slug']) || $item['lock_slug'] == 0): ?>name="slug" id="slug"<?php endif; ?> value="<?php echo $item['slug']; ?>" <?php echo ($item['lock_slug'] == 1) ? 'disabled="disabled"' : "" ; ?> />
					</div>

					<div class="form_field">
						<label>Featured Image</label>
						<p class="note">Image must be 960 x 576 dimensions.</p>
						<div id="featured_image_view">
							<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/team/'.$item['featured_image']))): ?>
							<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['featured_image']; ?>" alt="" />
							<p><a href="#" id="featured_image_remove" > Remove this image</a></p>
							<?php endif; ?>
						</div>
						<input type="file" name="featured_image" id="featured_image_upload" />
						<input type="hidden" name="featured_image" id="featured_image_filename" value="<?php echo $item['featured_image']; ?>" />
					</div><br/>
				</div>
				<div class="clear"><!-- x --></div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="action_buttons">
						<input class="btn yes primary-btn" type="submit" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
						<input class="btn no btn-warning" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
					</div>
				</div>
			</div>
		  <!-- <div class="action_buttons">
				<input class="btn yes" type="submit" onClick="checkTextData();" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
		  	<input class="btn no" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
			</div> -->
		</form>
	</div>
</div>

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

<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo $item_names['plural']; ?></h3>

		<a href="<?php echo $module_url; ?>add" class="icon-action">
			<div class="icon"><i class="fa fa-plus"></i></div>
			<p>Add New <?php echo $item_names['singular']; ?></p>
		</a>

		<?php if(!empty($items)): ?>

		<table class="cms_table table table-striped tablesorter" id="team_list">
		  <thead>
		  	<tr>
			  	<th>Sort</th>
					<th>Name</th>
					<th>Title</th>
			  	<th>URL</th>
			  	<th>Active</th>
			    <th>Actions</th>
		  	</tr>
		  </thead>

		  <tbody class="sortable" rel="team">
				<?php foreach($items as $i): ?>

					<tr id="sort_<?php echo $i['id']; ?>">
						<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
						<td><?php echo $i['name']; ?></td>
						<td><?php echo $i['title']; ?></td>
						<td><a href="<?php echo $config->get('url').'team/'.$i['slug']; ?>" target="_blank"><?php echo $config->get('url').'team/'.$i['slug']; ?></a></td>
						<td style="text-align: center; <?php echo ($i['active'] == 1) ? 'background-color: #d9ffd0;' : '' ; ?>"><?php echo ($i['active'] == 1) ? 'Yes' : 'No' ; ?></td>
						<td align="center">
							<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a> | <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php endif; ?>
	</div>
</div>


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
