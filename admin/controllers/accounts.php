<?php
$model = new Model('accounts');
$items = $model->get_items(0, 'title');
$item = array();
$item_names = array(
	'singular'=>'Account',
	'plural'=>'Accounts'
);
$view = 'list';


echo phpinfo();
die();


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
		var slug = $(this).val();
		if(slug != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'page_slug',slug:slug, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
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
			<label>URL Slug</label>
			<div class="note">Letters, numbers, and dashes only. No spaces. Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
			<input class="in_text" type="text" name="slug" id="slug" value="<?php echo $item['slug']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Content</label>
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
  	<th>Title</th>
  	<th>URL</th>
    <th>Actions</th>
  </tr>
  
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['title']; ?></td>
		<td><a href="<?php echo $config->get('url').$i['slug']; ?>"><?php echo $config->get('url').$i['slug']; ?></a></td>
		<td align="center">
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a> | <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
		</td>
	</tr>
	
	<?php endforeach; ?>

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