<?php
$model = new Product();
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Product',
	'plural'=>'Products'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
}


$product_db = new Model('products');
$product_categories = $product_db->db->fetch('SELECT * FROM product_categories');
$pcats_by_id = array();
foreach($product_categories AS $pc) {
	$pcats_by_id[$pc['id']] = $pc['title'];
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
	// Create URL tag
	$('#title').blur(function(){
		var title = $(this).val();
		if(title != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'product_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#slug').blur(function(){
		var slug = $(this).val();
		if(slug != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'product_slug',slug:slug, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
});
</script>

<h1><?php echo ucfirst($action).' '.$item_names['singular']; ?></h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
	
		<div class="form_field">
			<label>Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo htmlentities($item['title']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Category</label>
			<select name="category_id" id="category_id" class="in_text">
				<?php foreach($product_categories AS $pc): ?>
					<option value="<?php echo $pc['id']; ?>" <?php echo ($pc['id'] == $item['category_id']) ? 'selected="selected"' : '' ; ?>><?php echo $pc['title']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="form_field">
			<label>URL Slug</label>
			<div class="note">Letters, numbers, and dashes only. No spaces. Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
			<input class="in_text" type="text" name="slug" id="slug" value="<?php echo $item['slug']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Product Features</label>
			<textarea class="mce" name="product_features"><?php echo $item['product_features']; ?></textarea>
		</div>
		
		<div class="form_field">
			<label>Specifications</label>
			<textarea class="mce" name="specifications"><?php echo $item['specifications']; ?></textarea>
		</div>
		
		<div class="form_field">
			<label>Optional Features</label>
			<textarea class="mce" name="optional_features"><?php echo $item['optional_features']; ?></textarea>
		</div>
		
		<div class="form_field">
			<label>Resources</label>
			<textarea class="mce" name="resources"><?php echo $item['resources']; ?></textarea>
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
		
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>

<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table" id="prod_list">
	<thead>
	<tr>
		<th>Sort</th>
		<th>Title</th>
		<th>Category</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody class="sortable" rel="products">
	<?php foreach($items as $i): ?>
	
	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['title']; ?></td>
		<td><?php echo $pcats_by_id[$i['category_id']]; ?></td>
		<td align="center">
			<a href="<?php echo $config->get('admin_url').'product-photos?product_id='.$i['id']; ?>">Manage Photos</a> | 
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit Content</a> | 
			<a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $item_names['singular']; ?>?');">Delete</a>
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