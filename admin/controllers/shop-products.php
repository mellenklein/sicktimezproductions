<?php
$model = new ShopProduct();
$items = $model->get_items(array('order' => 'sort'));
$item = array();
$item_names = array(
	'singular'=>'Shop Product',
	'plural'=>'Shop Products'
);
$view = 'list';



$shop_colors_db = new Model('shop_colors');
$shop_colors = $shop_colors_db->get_items(array('order' => 'sort'));

$shop_sizes_db = new Model('shop_sizes');
$shop_sizes = $shop_sizes_db->get_items(array('order' => 'sort'));


// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;


$shop_p_colors_db = new Model('shop_product_colors');
$shop_p_sizes_db = new Model('shop_product_sizes');
$cur_color_ids = array();
$cur_size_ids = array();
if($id){
	$model->load($id);
	$shop_p_colors = $shop_p_colors_db->get_items(array('where' => 'product_id = '.$id));
	foreach($shop_p_colors AS $spc) {
		$cur_color_ids[] = $spc['color_id'];
	}
	$shop_p_sizes = $shop_p_sizes_db->get_items(array('where' => 'product_id = '.$id));
	foreach($shop_p_sizes AS $sps) {
		$cur_size_ids[] = $sps['size_id'];
	}
}

$product_db = new Model('shop_products');
$product_categories = $product_db->db->fetch('SELECT * FROM shop_categories');
$pcats_by_id = array();
foreach($product_categories AS $pc) {
	$pcats_by_id[$pc['id']] = $pc['title'];
}

/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	$shop_color_ids = array();
	if(isset($_POST['shop_color_ids'])) {
		$shop_color_ids = $_POST['shop_color_ids'];
		unset($_POST['shop_color_ids']);
	}
	$shop_size_ids = array();
	if(isset($_POST['shop_size_ids'])) {
		$shop_size_ids = $_POST['shop_size_ids'];
		unset($_POST['shop_size_ids']);
	}
	
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
	
	if($id) {
		$shop_p_colors_db->db->query('DELETE FROM shop_product_colors WHERE product_id = '.$id, array());
		if(!empty($shop_color_ids)) {
			foreach($shop_color_ids AS $c_id) {
				$shop_p_colors_db->insert(array('product_id' => $id, 'color_id' => $c_id));
			}
		}
		$shop_p_sizes_db->db->query('DELETE FROM shop_product_sizes WHERE product_id = '.$id, array());
		if(!empty($shop_size_ids)) {
			foreach($shop_size_ids AS $s_id) {
				$shop_p_sizes_db->insert(array('product_id' => $id, 'size_id' => $s_id));
			}
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
			<label>Price</label>
			<div class="note">Numbers only. Example: <strong>19.95</strong></div>
			<input class="in_text" type="text" name="price" id="price" value="<?php echo $item['price']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Weight <span style="font-size: 0.9em; font-weight: normal;">(used for shipping calculations)</span></label>
			<div class="note">Weight of product in pounds. Numbers only. For example 3/4lb: <strong>0.75</strong></div>
			<input class="in_text" type="text" name="weight" id="weight" value="<?php echo $item['weight']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Dimensions <span style="font-size: 0.9em; font-weight: normal;">(used for shipping calculations)</span></label>
			<div class="note">Example (LxWxH): <strong>3.25x2.75x7.75</strong></div>
			<input class="in_text" type="text" name="dimension" id="dimension" value="<?php echo $item['dimension']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Product Description</label>
			<textarea class="mce" name="description"><?php echo $item['description']; ?></textarea>
		</div>
	
	</div>
	
	<div class="thin_col">
		<fieldset>
			<legend>Colors</legend>
			<div class="note">Leave blank if there are no color choices</div>
			<?php foreach($shop_colors AS $c): ?>
				<label><input type="checkbox" name="shop_color_ids[]" value="<?php echo $c['id']; ?>" <?php echo (in_array($c['id'], $cur_color_ids)) ? 'checked="checked"' : '' ; ?>> <?php echo $c['title']; ?></label>
			<?php endforeach; ?>
		</fieldset>
		<br>
		<fieldset>
			<legend>Sizes</legend>
			<div class="note">Leave blank if there are no size choices</div>
			<?php foreach($shop_sizes AS $s): ?>
				<label><input type="checkbox" name="shop_size_ids[]" value="<?php echo $s['id']; ?>" <?php echo (in_array($s['id'], $cur_size_ids)) ? 'checked="checked"' : '' ; ?>> <?php echo $s['title']; ?></label>
			<?php endforeach; ?>
		</fieldset>
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
		<th>Price</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody class="sortable" rel="shop-products">
	<?php foreach($items as $i): ?>
	
	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['title']; ?></td>
		<td><?php echo $pcats_by_id[$i['category_id']]; ?></td>
		<td><?php echo '$'.$i['price']; ?></td>
		<td align="center">
			<a href="<?php echo $config->get('admin_url').'shop-product-photos?product_id='.$i['id']; ?>">Manage Photos</a> | 
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit Details</a> | 
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