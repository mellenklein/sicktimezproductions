<?php
$model = new Model('shop_promos');
$items = $model->get_items(array('order' => 'promo_code'));
$item = array();
$item_names = array(
	'singular'=>'Shop Promo',
	'plural'=>'Shop Promos'
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
	
	header('location:'.$module_url);
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
	$('.perc_or_flat').change(function(){
		if($('.perc_or_flat:checked').val() == 'perc') {
			$('#percent_shell').show();
			$('#flat_amount_shell').hide();
			$('#flat_amount').val('');
		} else {
			$('#percent_shell').hide();
			$('#flat_amount_shell').show();
			$('#percent').val('');
		}
	});
});
</script>

<h1><?php echo ucfirst($action); ?> Content</h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
	
		<div class="form_field">
			<label>Promo Code</label>
			<input class="in_text" type="text" name="promo_code" id="promo_code" value="<?php echo htmlentities($item['promo_code']); ?>" />
		</div>
		
		<div class="form_field">
			<label>Discount</label>
			<label style="display: inline-block; margin-right: 10px;"><input class="perc_or_flat" type="radio" name="perc_or_flat" value="perc" <?php echo (!isset($item['perc_or_flat']) || $item['perc_or_flat'] == 'perc') ? 'checked="checked"' : '' ; ?>> Percentage</label>
			<label style="display: inline-block;"><input class="perc_or_flat" type="radio" name="perc_or_flat" value="flat" <?php echo ($item['perc_or_flat'] == 'flat') ? 'checked="checked"' : '' ; ?>> Flat Amount</label>
			
			<div id="percent_shell" style="<?php echo (isset($item['perc_or_flat']) && !empty($item['perc_or_flat']) && $item['perc_or_flat'] != 'perc') ? 'display: none;' : '' ; ?>">
				<p class="note">Enter numbers only. For a 15% discount enter: <strong>0.15</strong></p>
				<input class="in_text" type="text" name="percent" id="percent" value="<?php echo htmlentities($item['percent']); ?>" />
			</div>
			<div id="flat_amount_shell" style="<?php echo (!isset($item['perc_or_flat']) || $item['perc_or_flat'] != 'flat') ? 'display: none;' : '' ; ?>">
				<p class="note">Enter numbers only. For a $10.00 discount enter: <strong>10.00</strong> or <strong>10</strong></p>
				<input class="in_text" type="text" name="flat_amount" id="flat_amount" value="<?php echo htmlentities($item['flat_amount']); ?>" />
			</div>
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
		$("#promo_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>
<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>
<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="promo_list">
  <thead>
  <tr>
	<th>Promo Code</th>
	<th>Discount</th>
    <th>Actions</th>
  </tr>
  </thead>
  
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['promo_code']; ?></td>
		<td><?php echo ($i['perc_or_flat'] == 'perc') ? ($i['percent']*100).'%' : '$'.$i['flat_amount'] ; ?></td>
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