<?php
$model = new Model('songs');
$items = $model->get_items(0, 'name');
$item = array();
$item_names = array(
	'singular'=>'Song',
	'plural'=>'Songs'
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

	if(!empty($_FILES["file"]['name'])) {
		$fields['file'] = upload_file('./../assets/uploads/songs/', 'file');
	}

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
	$uploader = 1; // Include uploadify
	$timestamp = time(); // Needed for uploadify
	$upload_dir = $config->get('admin_upload_img_dir').'songs/';
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
		removeImage($('#featured_image_filename'), 'featured_image', 'songs', $('#featured_image_view'));
	});
	function removeImage(file_ele, field, table, element) {
		var file = '/home/oakpoi/public_html/assets/images/songs/'+file_ele.val();
		if (window.confirm("Do you really want to delete this image?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:field, table:table, id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){
					element.html('');
					file_ele.val('');
				}
			});
		}
	}
	$('#rmv-file').click(function(evt){
		evt.preventDefault();
		var file = '/home/oakpoi/public_html/assets/uploads/songs/'+ $('#cur_file').attr('data-file');
		if (window.confirm("Do you really want to delete this file?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:'file', table:'songs', id:<?php echo json_encode($id); ?>}, function(data){
				if(data > 0){

					$('#cur_file').remove();
					$('#cur_file').html('');
					$('#file_view').html('');
				}
			});
		}
	});
});
</script>

<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo ucfirst($action); ?> Content</h3>
		<div class="clear"><!-- x --></div>
		<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-9">

					<div class="form_field">
						<label>Title</label>
						<input class="in_text" type="text" name="name" id="name" value="<?php echo htmlentities($item['name']); ?>" />
					</div>
					<div class="form_field">
						<label>Artist</label>
						<input class="in_text" type="text" name="artist" id="artist" value="<?php echo htmlentities($item['artist']); ?>" />
					</div>

					<div class="form_field">
						<label>Album</label>
						<input class="in_text" type="text" name="album" id="album" value="<?php echo htmlentities($item['album']); ?>" />
					</div>

					<div class="row">
						<!-- <div class="col-sm-6">
							<div class="form_field">
								<label>City</label>
								<label style="display: inline-block; margin-right: 14px;"><input class="" type="radio" name="city" id="city" value="Nashville, TN" <?php echo ((!isset($id) || empty($id)) || $item['city'] == "Nashville, TN") ? 'checked="checked"' : '' ; ?> /> Nashville, TN</label>

								<label style="display: inline-block;"><input class="" type="radio" name="city" id="city" value="Austin, TX" <?php echo (isset($item['city']) && $item['city'] == "Austin, TX") ? 'checked="checked"' : '' ; ?> /> Austin, TX</label>
							</div>
						</div> -->
						<div class="col-sm-6">
							<div class="form_field">
								<label>Featured on Home Page</label>
								<label style="display: inline-block; margin-right: 14px;"><input class="" type="radio" name="status" id="status" value="1" <?php echo ((!isset($id) || empty($id)) || $item['status'] == "1") ? 'checked="checked"' : '' ; ?> /> Yes</label>

								<label style="display: inline-block;"><input class="" type="radio" name="status" id="status" value="0" <?php echo (isset($item['status']) && $item['status'] == "0") ? 'checked="checked"' : '' ; ?> /> No</label>
							</div>
						</div>
					</div>

					<div class="form_field">
						<label>Purchase URL</label>
						<input class="in_text" type="text" name="website" id="website" value="<?php echo htmlentities($item['website']); ?>" />
					</div>

					<div class="form_field">
						<label>WAV or MP3 File</label>
							<?php if(is_file((dirname ( __FILE__ ).'/../../assets/uploads/songs/'.$item['file']))): ?>
								<p id="cur_file" data-file="<?php echo $item['file']; ?>">
									<?php echo $item['file']; ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<a href="#remove-file" id="rmv-file">Remove File</a>
								</p>
							<?php endif; ?>
						<input type="file" name="file" id="file" />
					</div>

				</div>
				<div class="col-md-3">
					<div class="form_field">
						<label>Featured Image</label>
						<div id="featured_image_view">
							<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/songs/'.$item['featured_image']))): ?>
							<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['featured_image']; ?>" alt="" />
							<p><a href="#" id="featured_image_remove"> Remove this image</a></p>
							<?php endif; ?>
						</div>
						<input type="file" name="featured_image" id="featured_image_upload" />
						<input type="hidden" name="featured_image" id="featured_image_filename" value="<?php echo $item['featured_image']; ?>" />
					</div>
					<?php if($user->data['is_fox_admin']): ?>
						<div class="form_field">
							 <label>Lock Slug</label>
							 <p class="note">Used to prevent non FoxFuel accounts from removing page.</p>
							 <label><input class="" type="radio" name="lock_slug" value="1" <?php echo ($item['lock_slug'] == 1) ? 'checked="checked"' : '' ; ?> /> Yes</label>
							 <label><input class="" type="radio" name="lock_slug" value="0" <?php echo (!isset($item['lock_slug']) || $item['lock_slug'] == 0) ? 'checked="checked"' : '' ; ?> /> No</label>
						</div>
					<?php endif; ?>
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
		$("#songs_list").tablesorter({

		});
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

		<table class="cms_table table table-striped tablesorter" id="songs_list">
			<thead>
				<tr>
					<th>Title</th>
					<th>Artist</th>
					<th>Featured Image</th>
					<th>Actions</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach($items as $i): ?>

					<tr>
						<td><?php echo $i['name']; ?></td>
						<td><?php echo $i['artist']; ?></td>
						<td>
							<div class="table-img" style="background-image: url(<?php echo $config->get('url').'/assets/images/songs/'.$i['featured_image']; ?>)">
							</div>
						</td>
						<td class="action_block">
							<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a>
							<?php if($i['lock_slug'] != 1): ?>
							| <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
							<?php endif; ?>
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
