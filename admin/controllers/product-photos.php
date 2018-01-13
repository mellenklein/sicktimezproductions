<?php

/*

$g_model = new Model('gallery_x_photo');
$new_photo = new Model('new_photo');
$model = new Model('photo');
$photos = $g_model->get_items(0,'id');

foreach($photos as $p){
	
	$model->load($p['photo_id']);
	if($model->data === null){
	
	}else{
		//var_dump($model->data);
		//die();
		$data = $model->data;
		$data['product_id'] = $p['product_id'];
		unset($data['id']);
		$result = $new_photo->insert($data);
		//var_dump($result);
		//die();
	}
	



}*/


$model = new SortModel('product_images');
$product_model = new Model ('products');
$galleries = $product_model->get_items();


if( isset($_GET['product_id']) ){
	
	$sql="SELECT  p.* FROM product_images p WHERE p.product_id = ? ORDER BY p.sort";
	$items = $model->db->fetch($sql, array($_GET['product_id']));
//	var_dump($model->db->dbo->error);
	$product_model->load($_GET['product_id']);
} else{
	$items = $model->get_items(0, 'sort');	
}




$item = array();
$view = 'list';

$name_var = 'Photo';
$plural_var = "Photos";

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

/*
//CUSTOM gallery functions
	function deleteGalleryConnections($id, $model){
		$delete_sql ='DELETE FROM gallery_x_photo WHERE photo_id='.$id;
		//echo $delete_sql;
		$model->specialCommand($delete_sql);			
	}
	
	function createGalleryFunctions($gallery_array, $photo_id, $model){
		foreach($gallery_array as $gallery){
			$sql = 'INSERT INTO gallery_x_photo (product_id, photo_id) VALUES ('.$gallery.','.$photo_id.')';
			$model->specialCommand($sql);
		}
	}

*/


if($id){
	$model->load($id);
}

/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	
	$fields = $_POST;
	$add = 0;

	//var_dump($fields);
	
	
	if(!empty($fields['photo_remove'])){
		$fields['file'] = '';
	}
	unset($fields['photo_remove']);
	//var_dump($fields);
	//unset galleries
	unset($fields['galleries']);
	
	if(!empty($model->data['id'])){
		$result = $model->update($fields);
		$id = $model->data['id'];
	}
	else{
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
		set_message('There was a problem '.($add ? 'adding' : 'updating').' the '.$name_var.'', 'fail');
	}
	
	header('location:'.$module_url.($id ? 'edit/'.$id : '').'?product_id='.$model->data['product_id']);
	exit();

}

/* ----------------------------------------------------------------------------
* ACTION: DELETE
* ---------------------------------------------------------------------------*/
if($action == 'delete'){
	$product_id = $model->data['product_id'];
	$result = $model->delete();
	if($result !== FALSE){
		set_message('The '.$name_var.' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$name_var.'.', 'fail');
	}
	
	header('location:'.$module_url.'?product_id='.$product_id);
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
* ACTION: SORT
* ---------------------------------------------------------------------------*/
if($action == 'multi-add'){
	if( (isset($_POST['product_id'])) && !empty($_POST['image']) ){
		$items = $model->get_items(array('limit' => 1, 'order' => 'sort DESC', 'where' => 'product_id = '.$_POST['product_id']));
		$last_sort = (!empty($items)) ? $items[0]['sort'] : 0 ;
		foreach($_POST['image'] as $i){
			
			$data = array(
				'file'=>$i,
				'sort'=>++$last_sort, 
				'product_id'=>$_POST['product_id']
			);
			$result = $model->insert($data);
			//var_dump($result);
			//die();
		}
		set_message('Photos have been added to this gallery.', 'success');
	}else{
		set_message('Photos have not been added to this gallery.', 'FAIL');
	}
	
	header('location:'.$module_url.'?product_id='.$_POST['product_id']);
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
	$upload_dir = $config->get('admin_upload_img_dir').'products/';

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



	// Image uploaders
	 var upload_check = $('#photo_upload').uploadifive({
		'uploadScript' : '<?php echo $config->get('admin_url'); ?>upload',
		'queueSizeLimit' : 1,
		'buttonText' : 'Upload Image',
		'height' : 24,
		'onUploadComplete' : function(file, data) {
			if(data != '0'){
				$('#photo_filename').val(data);
				$('#photo_view').html('<img class="full_img" src="<?php echo $config->get('admin_url').$upload_dir; ?>'+data+'" /><p><input type="checkbox" name="photo_remove" /> Remove this image</p>');
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
			'type':'photo',
			'sub_dir':'<?php echo $upload_dir ?>'
		}
	
	});
	});
</script>
<?php 


?>
<h1><?php echo ucfirst($action); ?> <?php echo $name_var; ?></h1>

<form id="form"  action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
	
		<div class="form_field">
			<label>Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo $item['title']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Photo Upload</label>
			<div id="photo_view">
				<?php if(!empty($item['file'])): ?>
				<img class="full_img" src="<?php echo $config->get('admin_url').$upload_dir.$item['file']; ?>" alt="" />
				<p><input type="checkbox" name="photo_remove" /> Remove this image</p>
				<?php endif; ?>
			</div>
			<input type="file" name="file" id="photo_upload" />
			<input type="hidden" name="file" id="photo_filename" value="<?php echo $item['file']; ?>" />
		</div>		
		

	
	</div>

	<div class="thin_col">



		

	</div>
	
	
	
	<div class="clear"><!-- x --></div>
  
  <div class="action_buttons">
		<input class="btn yes" type="submit" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
  	<input class="btn no" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
	</div>
</form>

<?php
/* ----------------------------------------------------------------------------
* VIEW: LIST (DEFAULT)
* ---------------------------------------------------------------------------*/
else:
$chosen = true;

	$uploader = 1; // Include uploadify
	$timestamp = time(); // Needed for uploadify
	$upload_dir = $config->get('admin_upload_img_dir').'products/';
?>
<script type="text/javascript">
$(function() {

	$(".chosen-select").chosen({disable_search_threshold: 10, width: '500px'});
	$('.chosen-select').on('change', function(evt, params) {
		if(params['selected'] != '--'){
			window.location.href = '<?php echo 'product-photos?product_id=' ?>'+params['selected'];
		}
	
	});

// Image uploaders
	var counter=-1;
	 var upload_check = $('#featured_upload').uploadifive({
		'uploadScript' : '<?php echo $config->get('admin_url'); ?>upload',
		'queueSizeLimit' : 50,
		'buttonText' : 'Upload Photos',
		'height' : 24,
		'onUploadComplete' : function(file, data) {
			if(data != '0'){
				counter = counter + 1;
				var hidden_input = '<input type="hidden" class="removable_'+counter+'" name="image[]" id="featured_filename" value="'+data+'" />';
			//	var preview = '<img class="full_img" src="/<?php echo $upload_dir ?>'+data+'" />';
				
				$('#featured_view').append(hidden_input );
				
				$('a.close').click(function(){
					
					var digit_str = $(this).parent().attr('id');
					
					var digit = digit_str.replace('uploadifive-featured_upload-file-', '');
					
					$('.removable_'+digit).remove();
				});
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
			'type':'',
			'sub_dir':'<?php echo $upload_dir ?>'
		}, 
		'debug':false,
		'onCancel': function(file){
			//console.log(file);
		},
		'onFallback' : function() {
		
		//uploadify_ele.uploadify(uploadify_options);
	}
	
	});	


});
</script>
<h1><?php echo $plural_var; ?>: <?php echo $product_model->data['title']?></h1>


<div class="wide_col">




		<div class="form_field">
			<label>Select a product:</label>
			<select class="chosen-select" name="classId">
				
				<?php foreach ($galleries as $i): ?>
				<?php 
					$selected;
					if($i['id'] == $_GET['product_id']){
						$selected = 'selected="selecgted"';
					} else{
						$selected = '';
					}
				?>
				<option <?php echo $selected ?> value="<?php echo $i['id'] ?>" ><?php echo $i['title']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>	  
<?php if(!empty($items)): ?>
<table class="cms_table">
  <tr>
 	 <th>Sort</th>
  	<th>Title</th>
  	<th>Image</th>
  	
    <th>Actions</th>
  </tr>
  <tbody class="sortable" rel="product-photos">
	<?php foreach($items as $i): ?>

	
	<tr id="sort_<?php echo $i['id']; ?>">
		<td class="sorter"><img src="<?php echo $config->get('admin_url'); ?>images/sort.png" alt="sort" /></td>
		<td><?php echo $i['title']; ?></td>
		<td><img style="width: 200px" src="<?php echo $config->get('admin_url').$upload_dir.$i['file']; ?>" alt="" /></td>
		<td align="center">
			<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a>
			| <a href="<?php echo $module_url.'delete/'.$i['id']; ?>" onclick="return confirm('Are you sure you want to delete this <?php echo $name_var; ?>?');">Delete</a>
		</td>
	</tr>
	
	<?php endforeach; ?>
	</tbody>
</table>
	<?php endif; ?>
</div><!-- wide col -->
<div class="thin_col">
		
		<form id="form"  action="<?php echo $module_url.'multi-add/'.$_GET['product_id']; ?>" method="post" enctype="multipart/form-data">
			<label>Add Photos</label>
			<input type="hidden" name="product_id" value="<?php echo $_GET['product_id']; ?>"/>
			
	
		

			<div id="featured_view">

			</div>
			
			<input type="file" name="image" id="featured_upload" />
  <div class="action_buttons" style="margin-top: 20px;">
		<input class="btn yes" type="submit" value="Add Photos" /> &nbsp;&nbsp;&nbsp;

	</div>
		</form>
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