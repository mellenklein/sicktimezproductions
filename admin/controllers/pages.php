<?php

$model = new Model('pages');
/*********************************************************/
/***********	CHECK / CREATE NECESSARY TABLES	**********/
/*********************************************************/
// Please be sure to update this with any changes to table structure
if(!$model->table_exists()) {
	$create_sql = "CREATE TABLE IF NOT EXISTS `pages` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `slug` varchar(200) NOT NULL,
						  `title` varchar(200) NOT NULL,
						  `content` text NOT NULL,
						  `banner_image` varchar(255) NOT NULL DEFAULT '' COMMENT 'Path from root (images/about-hero.jpg) No leading slash',
						  `stand_alone` tinyint(4) NOT NULL DEFAULT '1',
						  `lock_slug` tinyint(4) NOT NULL DEFAULT '0',
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$model->db->query($create_sql, array());
}
/*********************************************************/
/*****************	END TABLE CHECK	*******************/
/*********************************************************/
$items = $model->get_items(array('order' => 'title', 'where' => 'stand_alone = 1'));
$item = array();
$item_names = array(
	'singular'=>'Content',
	'plural'=>'Page Content'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : 'list';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
}
/* ----------------------------------------------------------------------------
* ACCESS CONTROL
* ---------------------------------------------------------------------------*/


// let's see if the user has access to use the action they are viewing
//for convience, we are using the default method.  Internally, this method calls createFirewall
$action = $user->checkAccess($ctrl, $action, $config->get('admin_url'));
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
	// Create URL tag
	$('#title').blur(function(){
		var title = $(this).val();
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'page_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#slug').blur(function(){
		var title = $(this).val();
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'page_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
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
						<label>Content</label>
						<textarea class="mce" name="content"><?php echo $item['content']; ?></textarea>
					</div>

				</div>
				<div class="col-md-3">
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
<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo $item_names['plural']; ?></h3>

		<a href="<?php echo $module_url; ?>add" class="icon-action">
			<div class="icon"><i class="fa fa-plus"></i></div>
			<p>Add New <?php echo $item_names['singular']; ?></p>
		</a>

		<?php if(!empty($items)): ?>

		<table class="cms_table table table-striped">
			<thead>
				<tr>
					<th>Title</th>
					<th>Link</th>
					<th>Locked</th>
					<th>Actions</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach($items as $i): ?>

					<tr>
						<td><?php echo $i['title']; ?></td>
						<td><a href="<?php echo $config->get('url').'/'.$i['slug']; ?>" target="_blank"><?php echo $config->get('url').'/'.$i['slug']; ?></td>
						<td><?php echo ($i['lock_slug'] == 1) ? "X" : "" ; ?></td>
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
