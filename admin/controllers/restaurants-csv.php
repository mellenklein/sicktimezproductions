<?php
$model = new Model('restaurants');
$items = $model->get_items(0, 'title');
$item = array();
$item_names = array(
	'singular'=>'Restaurant',
	'plural'=>'Restaurants'
);
$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
	$model->load($id);
}


$restaurant_cats_db = new Model('restaurant_categories');
$all_r_cats = $restaurant_cats_db->get_items();

$restaurant_locations_db = new Model('restaurant_locations');
$all_r_locs = array();
if($id !== 0) {
	$all_r_locs = $restaurant_locations_db->get_items(array('where' => 'restaurant_id = ?', 'params' => array($id)));
}
$cur_r_loc_count = 0;



if(isset($_POST["submit_file"])) {
	/** PHPExcel_IOFactory */
	require_once dirname(__FILE__) . '/../classes/PHPExcel/Classes/PHPExcel/IOFactory.php';
	
	
	$uploaddir = '/home/southpri/public_html/admin/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['restaurants_file']['name']);
	
	if (move_uploaded_file($_FILES['restaurants_file']['tmp_name'], $uploadfile)) {
		$result = array();
		
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($uploadfile);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($uploadfile);
		$data_arr = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$headers = array(
								'title',
								'address',
								'city',
								'state',
								'zip',
								'phone',
								'website',
								'description'
							);
		foreach($data_arr AS $line) {
			if ($line) {
				if (sizeof($line)==sizeof($headers)) {
					if(strpos(strtolower($line['B']), 'address') !== false) {
						
					} else {
						$result[] = array_combine($headers,$line);
					}
				}
			}
		}
		
		$rCount = 0;
		$first_occurrence = '';
		if(!empty($result)) {
			foreach ($result AS &$r) {
				$rCount++;
				foreach($r AS &$v) {
					$v = htmlentities(trim($v));
				}
				$r['website'] = (strpos($r['website'], 'http://') !== false || empty($r['website'])) ? $r['website'] : 'http://'.$r['website'] ;
				
				
				$restaurant = array(
								'title' => $r['title'],
								'website' => $r['website'],
								'description' => $r['description']
							);
				$location = array(
								'address' => $r['address'],
								'city' => $r['city'],
								'state' => $r['state'],
								'zip' => $r['zip'],
								'phone' => $r['phone']
							);
				$location['lat'] = '';
				$location['lng'] = '';
				if(!empty($location['address'])) {
					$loc_parts = array($location['address'], $location['city'], $location['state'], $location['zip']);
					foreach($loc_parts AS $k => $v) {
						if(empty($v)) {
							unset($loc_parts[$k]);
						}
					}
					$loc = implode(",", $loc_parts);
					$url = $config->get('google_map_api').urlencode($loc);
					$geo = json_decode(file_get_contents($url), TRUE);
					$location['lat'] = $geo['results'][0]['geometry']['location']['lat'];
					$location['lng'] = $geo['results'][0]['geometry']['location']['lng'];
				}
				
				$model->load($r['title'], 'title');
				if(empty($model->data)) {
					$slug_invalid = true;
					$tag = $restaurant['title'];
					$orig_tag = $tag;
					$tag_count = 0;
					while ($slug_invalid) {
						$model_db = new Model('restaurants', 'slug');
						$model_db->load($tag);
						if(!empty($model_db->data)) {
							$tag_count++;
							$tag = $orig_tag.'-'.$tag_count;
						} else {
							$slug_invalid = false;
						}
					}
					$restaurant['slug'] = $tag;
					$r_id = $model->insert($restaurant);
				} else {
					//$model->load($model->data['id'], 'id');
					$r_id = $model->data['id'];
				}
				
				$location['restaurant_id'] = $r_id;
				$restaurant_locations_db->insert($location);
				
				echo 'TITLE: '.$r['title'].' LAT: '.$location['lat'].' LNG: '.$location['lng'].' ADDRESS: '.$location['address'].', '.$location['city'].', '.$location['state'].', '.$location['zip'].'<hr>';
				if($rCount % 10 == 0) {
					sleep(1);
				}
			}
		}
		die();
	}
	header("Location: ".$config->get('admin_url').'restaurants-csv');
}



/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	
	$rlocs_arr = $_POST['rlocs_arr'];
	unset($_POST['rlocs_arr']);
	$fields = $_POST;
	$add = 0;
	
	if(!empty($_POST['address']) || !empty($_POST['state'])) {
		$loc_parts = array($_POST['address'], $_POST['city'], $_POST['state'], $_POST['zip']);
		foreach($loc_parts AS $k => $v) {
			if(empty($v)) {
				unset($loc_parts[$k]);
			}
		}
		$loc = implode(",", $loc_parts);
		$url = $config->get('google_map_api').urlencode($loc);
		$geo = json_decode(file_get_contents($url), TRUE);
		$fields['lat'] = $geo['results'][0]['geometry']['location']['lat'];
		$fields['lng'] = $geo['results'][0]['geometry']['location']['lng'];
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
	
	foreach($rlocs_arr AS $k => $v) {
		$v['restaurant_id'] = $id;
		$loc_parts = array($v['address'], $v['city'], $v['state'], $v['zip']);
		foreach($loc_parts AS $key => $val) {
			if(empty($val)) {
				unset($loc_parts[$key]);
			}
		}
		$loc = implode(",", $loc_parts);
		$url = $config->get('google_map_api').urlencode($loc);
		$geo = json_decode(file_get_contents($url), TRUE);
		$v['lat'] = $geo['results'][0]['geometry']['location']['lat'];
		$v['lng'] = $geo['results'][0]['geometry']['location']['lng'];
		
		if($v['id'] !== '') {
			$restaurant_locations_db->load($v['id']);
			unset($v['id']);
			$restaurant_locations_db->update($v);
		} else {
			unset($v['id']);
			$restaurant_locations_db->insert($v);
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
	$upload_dir = $config->get('admin_upload_img_dir').'restaurants/';

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
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'restaurant_slug',title:title, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	$('#slug').blur(function(){
		var slug = $(this).val();
		if(slug != ''){
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'restaurant_slug',slug:slug, id:<?php echo $id; ?>}, function(data){
				if(data != ''){
					$('#slug').val(data);
				}
			});
		}
	});
	
	
	$('#add-rloc').click(function(evt){
		evt.preventDefault();
		var cur_rloc_count = $(this).attr('data-rloc-count');
		var rloc_html = $('#rloc-template').html();
		cur_rloc_count++;
		rloc_html = rloc_html.replace(/\{{rloc_count}}/g, cur_rloc_count);
		$('#rlocs-shell').append(rloc_html);
		$(this).attr('data-rloc-count', cur_rloc_count);
	});
	$('#rlocs-shell').on( "click", ".cancel-rloc", function(evt) {
		evt.preventDefault();
		if(confirm("Cancle this location addition?")) {
			$(this).parents('fieldset.rloc_fieldset').remove();
		}
	});
	$('.delete-rloc').click(function(evt){
		evt.preventDefault();
		var rloc_id = $(this).attr('data-rloc-id');
		var delLinkEle = $(this);
		if(confirm("Delete this location?")) {
			$.post('<?php echo $config->get('admin_url'); ?>ajax', {action:'delete_rloc',id:rloc_id}, function(data){
				if(data !== false){
					delLinkEle.parents('fieldset.rloc_fieldset').remove();
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
		removeImage($('#featured_image_filename'), 'featured_image', 'restaurants', $('#featured_image_view'));
	});
	function removeImage(file_ele, field, table, element) {
		var file = '<?php echo dirname ( __FILE__ ).'/../../assets/images/restaurants/'; ?>'+file_ele.val();
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

<h1><?php echo ucfirst($action); ?> Content</h1>

<form action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data">
	
	<div class="wide_col">
	
		<div class="form_field">
			<label>Title</label>
			<input class="in_text" type="text" name="title" id="title" value="<?php echo $item['title']; ?>" />
		</div>
		
		<div class="form_field">
			<label>URL Slug</label>
			<div class="note">Letters, numbers, and dashes only. No spaces. Example: <?php echo $config->get('site_url'); ?><strong>my-url-tag</strong></div>
			<input class="in_text" type="text" name="slug" id="slug" value="<?php echo $item['slug']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Website URL</label>
			<input class="in_text" type="text" name="website" id="website" value="<?php echo $item['website']; ?>" />
		</div>
		
		<div class="form_field">
			<label>Description</label>
			<textarea class="mce" name="description"><?php echo $item['description']; ?></textarea>
		</div>
		<div id="rlocs-shell">
			<?php if(!empty($all_r_locs)): ?>
				<?php foreach($all_r_locs AS $rloc): ?>
					<?php $cur_r_loc_count++; ?>
					<fieldset id="rloc_<?php echo $cur_r_loc_count; ?>" class="rloc_fieldset">
						<legend>Location <?php echo $cur_r_loc_count; ?></legend>
						<input type="hidden" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][id]" value="<?php echo $rloc['id']; ?>">
						<div class="form_field">
							<label>Address</label>
							<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][address]" id="" value="<?php echo $rloc['address']; ?>" />
						</div>
						
						<div class="form_field">
							<label>City</label>
							<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][city]" id="" value="<?php echo $rloc['city']; ?>" />
						</div>
						
						<div class="form_field">
							<label>State Abbreviation</label>
							<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][state]" id="" value="<?php echo $rloc['state']; ?>" />
						</div>
						
						<div class="form_field">
							<label>Zip Code</label>
							<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][zip]" id="" value="<?php echo $rloc['zip']; ?>" />
						</div>
						
						<div style="text-align: right;"><a href="#delete-rloc" class="delete-rloc" data-rloc-id="<?php echo $rloc['id']; ?>">Delete Location</a></div> 
					</fieldset>
				<?php endforeach; ?>
			<?php else: ?>
				<?php $cur_r_loc_count++; ?>
				<fieldset id="rloc_<?php echo $cur_r_loc_count; ?>" class="rloc_fieldset">
					<legend>Location <?php echo $cur_r_loc_count; ?></legend>
					<input type="hidden" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][id]" value="">
					<div class="form_field">
						<label>Address</label>
						<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][address]" id="" value="" />
					</div>
					
					<div class="form_field">
						<label>City</label>
						<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][city]" id="" value="" />
					</div>
					
					<div class="form_field">
						<label>State Abbreviation</label>
						<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][state]" id="" value="" />
					</div>
					
					<div class="form_field">
						<label>Zip Code</label>
						<input class="in_text" type="text" name="rlocs_arr[<?php echo $cur_r_loc_count; ?>][zip]" id="" value="" />
					</div>
				</fieldset>
			<?php endif; ?>
		</div>
		<p style="margin-top: 20px;"><a href="#add-loc" data-rloc-count="<?php echo $cur_r_loc_count; ?>" id="add-rloc">+ Add Location</a></p>
	</div>
	
	<div class="thin_col">
		<div class="form_field">
			<label>Location Type/Category</label>
			<select name="restaurant_categorie_id" id="" class="in_text">
				<?php foreach($all_r_cats AS $rc): ?>
					<option value="<?php echo $rc['id']; ?>" <?php echo ($rc['id'] == $item['restaurant_categorie_id']) ? 'selected="selected"' : '' ; ?>><?php echo $rc['title']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="form_field">
			<label>Restaurant Featured Image</label>
			<div id="featured_image_view">
				<?php if(is_file((dirname ( __FILE__ ).'/../../assets/images/restaurants/'.$item['featured_image']))): ?>
				<img style="max-width: 100%;" src="<?php echo $config->get('admin_url').$upload_dir.$item['featured_image']; ?>" alt="" />
				<p><a href="#" id="featured_image_remove" > Remove this image</a></p>
				<?php endif; ?>
			</div>
			<input type="file" name="featured_image" id="featured_image_upload" />
			<input type="hidden" name="featured_image" id="featured_image_filename" value="<?php echo $item['featured_image']; ?>" />
		</div>
		
	</div>
	
	<div class="clear"><!-- x --></div>
  
  <div class="action_buttons">
		<input class="btn yes" type="submit" onClick="checkTextData();" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
  	<input class="btn no" type="button" value="Cancel" onclick="window.location='<?php echo $module_url; ?>';" />
	</div>
</form>

<div id="rloc-template" style="display: none;">
	<fieldset id="rloc_{{rloc_count}}" class="rloc_fieldset">
		<legend>Location {{rloc_count}}</legend>
		<input type="hidden" name="rlocs_arr[{{rloc_count}}][id]" value="">
		<div class="form_field">
			<label>Address</label>
			<input class="in_text" type="text" name="rlocs_arr[{{rloc_count}}][address]" id="" value="" />
		</div>
		
		<div class="form_field">
			<label>City</label>
			<input class="in_text" type="text" name="rlocs_arr[{{rloc_count}}][city]" id="" value="" />
		</div>
		
		<div class="form_field">
			<label>State Abbreviation</label>
			<input class="in_text" type="text" name="rlocs_arr[{{rloc_count}}][state]" id="" value="" />
		</div>
		
		<div class="form_field">
			<label>Zip Code</label>
			<input class="in_text" type="text" name="rlocs_arr[{{rloc_count}}][zip]" id="" value="" />
		</div>
		<div style="text-align: right; clear: both;"><a href="#cancel-rloc" class="cancel-rloc">Cancel</a></div> 
	</fieldset>
</div>

<?php
/* ----------------------------------------------------------------------------
* VIEW: LIST (DEFAULT)
* ---------------------------------------------------------------------------*/
else:
?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#rest_list").tablesorter({
			
		});
	});
</script>
<h1><?php echo $item_names['plural']; ?></h1>
<br>
<br>
<form action="./restaurants-csv" method="post" enctype="multipart/form-data">
    Select datafile to upload:
    <input type="file" name="restaurants_file" id="restaurants_file"><br>
    <input type="submit" value="Upload File" name="submit_file">
</form>
<br>
<br>
<br>
<p><strong><a href="<?php echo $module_url; ?>add">+ Add New <?php echo $item_names['singular']; ?></a></strong></p>

<?php if(!empty($items)): ?>

<table class="cms_table tablesorter" id="rest_list">
  <thead>
  <tr>
  	<th>Title</th>
  	<th>Website URL</th>
    <th>Actions</th>
  </tr>
  </thead>
  <tbody>
	<?php foreach($items as $i): ?>
	
	<tr>
		<td><?php echo $i['title']; ?></td>
		<td><a href="<?php echo $i['website']; ?>" target="_blank"><?php echo $i['website']; ?></a></td>
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