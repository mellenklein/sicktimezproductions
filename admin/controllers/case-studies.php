<?php
require_once('../classes/CaseStudy.php');

$model = new CaseStudy();
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Case Study',
	'plural'=>'Case Studies'
);
$view = 'list';

// Name of cross table:
$case_x_members_db = new Model('case_x_members');
$team_db = new Model('team');
$case_members = $team_db->get_items();
//array of everything in team table - get for checklist
//as you loop through case_members, determine whether they should be checked


$tmembers_by_id = array();
foreach($case_members AS $m) {
	$tmembers_by_id[$m['id']] = $m['title'];
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

	$team_member_ids = array();
	if(!empty($_POST['team_member_ids'])) {
		$team_member_ids = $_POST['team_member_ids'];
		unset($_POST['team_member_ids']);
	}

	$fields = $_POST;
	$add = 0;

	if(!empty($_FILES["file"]['name'])) {
		$fields['file'] = upload_file('./../assets/uploads/case-studies/', 'file');
	}

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
	//updating any changes to associated team_members:
	if($id) {
		$case_x_members_db->db->query('DELETE FROM case_x_members WHERE case_study_id = '.$id, array());
		// var_dump($team_member_ids);
		// die;
		foreach($team_member_ids AS $tm_id) {
			$case_x_members_db->insert(array('case_study_id'=>$id, 'team_member_id'=>$tm_id));
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
	$upload_dir = $config->get('admin_upload_img_dir').'case-studies/';
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
	$('#title').blur(function(){
		var title = $(this).val();
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'news_slug',title:title, id:<?php echo $id; ?>}, function(data){
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
		removeImage($('#featured_image_filename'), 'featured_image', 'case-studies', $('#featured_image_view'));
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
	$('#rmv-file').click(function(evt){
		evt.preventDefault();
		var file = '/home/oakpoi/public_html/assets/uploads/case-studies/'+ $('#cur_file').attr('data-file');
		if (window.confirm("Do you really want to delete this file?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delImage',file:file, field:'file', table:'case_studies', id:<?php echo json_encode($id); ?>}, function(data){
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
						<input class="in_text" type="text" name="title" id="title" value="<?php echo htmlentities($item['title']); ?>" />
					</div>


					<div class="form_field">
						<label>URL Slug</label>
						<div class="note">Letters, numbers, and dashes only. No spaces. Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
						<input class="in_text" type="text" <?php if(!isset($item['lock_slug']) || $item['lock_slug'] == 0): ?>name="slug" id="slug"<?php endif; ?> value="<?php echo $item['slug']; ?>" <?php echo ($item['lock_slug'] == 1) ? 'disabled="disabled"' : "" ; ?> />
					</div>

					<div class="form_field">
						<label>Address</label>
						<input class="in_text address" type="text" name="address" id="address" value="<?php echo $item['address']; ?>" />
					</div>

					<div class="form_field">
						<label>Categories</label>
						<input class="in_text categories" type="text" name="categories" id="categories" value="<?php echo $item['categories']; ?>" />
					</div>

					<div class="form_field">
						<label>PDF File</label>
							<?php if(is_file((dirname ( __FILE__ ).'/../../assets/uploads/case-studies/'.$item['file']))): ?>
								<p id="cur_file" data-file="<?php echo $item['file']; ?>">
									<?php echo $item['file']; ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<a href="#remove-file" id="rmv-file">Remove File</a>
								</p>
							<?php endif; ?>
						<input type="file" name="file" id="file" />
					</div>

					<div class="form_field">
						<label>Case Study Featured Image</label>
						<div id="featured_image_view">
							<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/case-studies/'.$item['featured_image']))): ?>
							<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['featured_image']; ?>" alt="" />
							<p><a href="#" id="featured_image_remove" > Remove this image</a></p>
							<?php endif; ?>
						</div>
						<input type="file" name="featured_image" id="featured_image_upload" />
						<input type="hidden" name="featured_image" id="featured_image_filename" value="<?php echo $item['featured_image']; ?>" />
					</div>

					<div class="form_field">
						<label>Case Study Blurb</label>
						<div class="note">Short summary</div>
						<textarea class="mce" name="blurb" id="blurb"><?php echo $item['blurb']; ?></textarea>
					</div>

					<div class="form_field">
						<label>Case Study Content</label>
						<div class="note">Full case study content</div>
						<textarea class="mce" name="content"><?php echo $item['content']; ?></textarea>
					</div>

				</div>
				<div class="col-md-3">

					<div class="form_field">
						<h3>Key Team Members</h3>

						<?php foreach($case_members AS $cm): ?>
							<label>
								<input type="checkbox" class="single_model" value="<?php echo $cm['id']; ?>" name="team_member_ids[]" <?php echo (isset($item['id']) && in_array($cm['id'], $item['team_ids'])) ? 'checked="checked"' : '' ; ?> >
								<?php echo $cm['name']; ?>
							</label>
						<?php endforeach; ?>
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
		$("#news_list").tablesorter({

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

		<table class="cms_table table table-striped tablesorter" id="news_list">
			<thead>
				<tr>
					<th>Sort</th>
					<th>Title</th>
					<th>Link</th>
					<th>Address</th>
					<th>Actions</th>
				</tr>
			</thead>

			<tbody class="sortable" rel="case-studies">
				<?php foreach($items as $i): ?>

					<tr id="sort_<?php echo $i['id']; ?>">
						<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
						<td><?php echo $i['title']; ?></td>
						<td><a href="<?php echo $config->get('url').'case-studies/'.$i['slug']; ?>" target="_blank"><?php echo $config->get('url').'case-studies/'.$i['slug']; ?></a></td>
						<td><?php echo $i['address']; ?></td>
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
