<?php
$model = new Model('testimonials');
$args = array(
	'order'=>'sort'
);
$items = $model->get_items($args);
$item = array();
$view = 'list';

$name_var = 'Testimonial';
$plural_var = "Testimonials";

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

	if(!empty($fields['featured_remove'])){
		$fields['image'] = '';
	}
	unset($fields['featured_remove']);
	//var_dump($fields);

	if(!empty($model->data['id'])){
		$result = $model->update($fields);
	} else {
		$lastItem = $model->get_items(array('order' => 'sort DESC', 'limit' => 1));

		$lastSort = (isset($lastItem[0]['sort'])) ? $lastItem[0]['sort'] : 0 ;
		$fields['sort'] = $lastSort+1;

		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}

	if($result !== FALSE){
		set_message('The '.$name_var.' has been '.($add ? 'added' : 'updated').' successfully.', 'success');
	}
	else{
		set_message('There was a problem '.($add ? 'adding' : 'updating').' the '.$name_var.'.', 'fail');
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
		set_message('The '.$name_var.' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$name_var.'.', 'fail');
	}

	header('location:'.$module_url);
	exit();

}
/* ----------------------------------------------------------------------------
* ACTION: SORT
* ---------------------------------------------------------------------------*/
if($action == 'sort'){

	$model->sort($_POST['sort']);
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
	// if($(':radio[name="has_btn"]').filter(':checked').val() == 0) {
	// 	$('.button-container').hide();
	// }
	//
	// $(':radio[name="has_btn"]').change(function() {
	//   var btn = $(this).filter(':checked').val();
	// 	if (btn == 1) {
	// 		$('.button-container').slideDown();
	// 	} else if(btn == 0) {
	// 		$('.button-container').slideUp();
	// 		$('input[name="button_text"]').val('');
	// 		$('input[name="button_url"]').val('');
	// 	}
	// });

});
</script>

<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo ucfirst($action); ?> <?php echo $name_var; ?> </h3>
		<div class="clear"><!-- x --></div>
		<form id="form" action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-9">

					<div class="form_field">
						<label>Quotation</label>
						<textarea class="mce" name="quote"><?php echo $item['quote']; ?></textarea>
					</div>

					<div class="form_field">
						<label>Person</label>
						<input class="in_text" type="text" name="person" id="person" value="<?php echo $item['person']; ?>">
					</div>

					<div class="form_field">
						<label>Title</label>
						<input class="in_text" type="text" name="title" value="<?php echo $item['title']; ?>" />
					</div>

				</div>
				<div class="col-md-3">
					<div class="form_field">
						<label>Active</label>
						<label style="display: inline-block; margin-right: 14px;"><input class="" type="radio" name="active" id="active" value="1" <?php echo ((!isset($id) || empty($id)) || $item['active'] == 1) ? 'checked="checked"' : '' ; ?> /> Yes</label>
						<label style="display: inline-block;"><input class="" type="radio" name="active" id="active" value="0" <?php echo (isset($item['active']) && $item['active'] == 0) ? 'checked="checked"' : '' ; ?> /> No</label>
					</div>
				</div>
			</div>
			<div class="clear"><!-- x --></div>

			<div class="row">
				<div class="col-sm-12">
					<div class="action_buttons">
						<input class="btn primary-btn yes" type="submit" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
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
		<h3><?php echo $plural_var; ?></h3>

		<a href="<?php echo $module_url; ?>add" class="icon-action">
			<div class="icon"><i class="fa fa-plus"></i></div>
			<p>Add New <?php echo $item_names['singular']; ?></p>
		</a>

		<div class="clear"><!-- x --></div>

		<?php if(!empty($items)): ?>

		<table class="cms_table table table-striped responsive">
			<thead>
				<tr>
			  	<th class="hide-for-mobile">Sort</th>
					<th>Person</th>
			    <th>Quotation</th>
			    <th>Actions</th>
			  </tr>
			</thead>

		  <tbody class="sortable" rel="testimonials">
				<?php foreach($items as $i): ?>
					<tr id="sort_<?php echo $i['id']; ?>">
						<td class="sorter hide-for-mobile"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
						<td><?php echo $i['person']; ?></td>
						<td><?php echo strip_tags($i['quote']); ?></td>

						<td align="center">
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
