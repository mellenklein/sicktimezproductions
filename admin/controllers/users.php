<?php

//are we a super admin?
if(!$user->data['is_super_admin']){
	set_message('Sorry, but that page does not exist', 'fail');
	header('location:'.$config->get('admin_url'));
	exit();
}
$model = new Model('admins');
/*********************************************************/
/***********	CHECK / CREATE NECESSARY TABLES	**********/
/*********************************************************/
// Please be sure to update this with any changes to table structure
// Table creation for Admin tables is inside of Admin.php check_admin_tables() function
/*********************************************************/
/*****************	END TABLE CHECK	*******************/
/*********************************************************/
$items = $model->get_items(array('where' => 'is_fox_admin != 1', 'order' => 'first_name, last_name'));

$item = array();
$view = 'list';

$name_var = 'User';
$plural_var = "Users";

$admin_arr = array('Super Admin', 'Admin', 'Basic User');


// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : '';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

$access_model = new Model('admins_access');
if($id){
	$model->load($id);
	if($model->data['is_fox_admin']) {
		// FoxFuel account. No modifiying allowed
		header('location:'.$config->get('admin_url'));
		exit();
	}
	//get the particular access for this user
	$access_levels = $access_model->get_items( array('where'=>'admin_id = ?', 'params'=> array($id)) );
	$levels = array();
	foreach( $access_levels as $a ){
		$levels[$a['controller']] = $a['allow_edit'];
	}

}
//get the controllers 
$ctrl_model = new Model('cms_tools');
$controllers = $ctrl_model->get_items(array(
	'order'=>'sort'
));
/* ----------------------------------------------------------------------------
* ACTION: UPDATE
* ---------------------------------------------------------------------------*/
if($action == 'update'){
	//get the access levels out
	$access_levels = $_POST['access'];
	unset( $_POST['access'] );
	$fields = $_POST;
	
	$add = 0;
	
	if(!empty($fields['password'])) {
		$fields['wordpass']= sha1($fields['password']);
		unset($fields['password_confirm']);
		unset($fields['password']);
	} else{
		unset($fields['password_confirm']);
		unset($fields['password']);
	}
	//var_dump($fields);
	//var_dump($fields);
	//
	
	if(!empty($model->data['id'])){
		$result = $model->update($fields);
		$sql = 'DELETE FROM admins_access WHERE admin_id = ?';
		$model->db->delete($sql, array( $id ));
		//echo $result;
	}
	else{
		$add = 1;
		$result = $model->insert($fields);
		if($result !== FALSE){
			$id = $result;
		}
	}
	//now that we have an id, we need to update the user access. 
	foreach( $access_levels  as $k=>$v){
		if($v !='none'){
			$insert_arr = array(
			'admin_id'=>$id,
			'controller'=>$k,
			'allow_edit'=> ( ($v=='edit') ? 1:0 )
		);
		$access_model->insert( $insert_arr );		
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
		$sql = 'DELETE FROM admins_access WHERE admin_id = ?';
		$model->db->delete($sql, array( $id ));
	$result = $model->delete();
	if($result !== FALSE){
		set_message('The '.$name_var.' has been deleted successfully.', 'success');
	}
	else{
		set_message('There was a problem deleting the '.$name_var.'.', 'fail');
	}
	
	header('location:'.$module_url.'');
	exit();

}



/* ----------------------------------------------------------------------------
* ACTION: ADD/EDIT
* ---------------------------------------------------------------------------*/
if($action == 'add' || $action == 'edit'){

	$item = $model->data;
	$view = 'edit';

	


}
//print_r($pages);
ob_start();
?>

<?php
/* ----------------------------------------------------------------------------
* VIEW: EDIT
* ---------------------------------------------------------------------------*/
if($view == 'edit'):
?>

<script type="text/javascript">
$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
function showMessage(text, type){
	var html = '<div class="alert '+type+'">'+text+'</div>';
	$('#js_message').html(html);
}
	function validateForm(){
		
		$('#js_message').html('');
		var ok = 1;
		var message = '';
		//get data
		var form_data =$('#user-form').serializeObject();
		console.log( form_data );
		//did the user try to update the password? if so, we need to verify that their passwords are identical
		if( form_data.password != ''){
			if(form_data.password != form_data.password_confirm){
				ok = 0;
				message += 'Password and Password Confirm do not match.';
			}
		}
		<?php if ($id ==0 ):?>
		//form validation for passwords
		if( form_data.password == ''){
			ok = 0;
			message += '<br/>Password and Password Confirm are required for new users.';
		}
		<?php endif;?>
		//final validation
		if(ok){
			return true
		} else{
			showMessage(message, 'fail');
			return false;
		}
	}
$(function() {


	
	$('.is_admin').click( function(e){
		var val = $(this).val();
		//console.log(val);
		if( val == 1){
			$('.cms_access').hide();
			$('.is_super_message').show();
			
		} else{
			$('.cms_access').show();
			$('.is_super_message').hide();
		}
	});
	
	//check if we have one of the admin levels clicked on init
	var val = $('input[type="radio"][value="1"].is_admin').is(':checked');
	if(val){
		$('.cms_access').hide();
		$('.is_super_message').show();

	} else{
		$('.cms_access').show();
		$('.is_super_message').hide();
	}


	
//end all doc ready stuff
});
	
	
</script>

<div class="row bg-img interior">
	<div class="col-md-12">
		<h3><?php echo ucfirst($action); ?> <?php echo $name_var; ?></h3>
		<div class="clear"></div>
		<div id="js_message"></div>
		<div class="clear"></div>
		<form id="user-form" action="<?php echo $module_url.'update/'.$id; ?>" method="post" enctype="multipart/form-data" role="form" onsubmit="return validateForm();">
			<div class="row">
				<div class="col-md-9">
					<div class="form_field">
						<label>First Name</label>
						<input class="in_text" type="text" name="first_name" id="first_name" value="<?php echo $item['first_name']; ?>" />
					</div>
					<div class="form_field">
						<label>Last Name</label>
						<input class="in_text" type="text" name="last_name" id="last_name" value="<?php echo $item['last_name']; ?>" />
					</div>
					<div class="form_field">
						<label>Username</label>
						<input class="in_text" type="text" name="username" id="username" value="<?php echo $item['username']; ?>" />
					</div>
					<div class="form_field">
						<label>Password</label>
						<input class="in_text" type="text" name="password" id="password" value="<?php echo $item['password']; ?>" />
					</div>
					<div class="form_field">
						<label>Confirm Password</label>
						<input class="in_text" type="text" name="password_confirm" id="password_confirm" value="<?php echo $item['password_confirm']; ?>" />
					</div>
					<div class="form_field">
						<label>Admin Level</label>
						
						<label>
							<input class="is_admin" type="radio" name="is_super_admin" id="" value="1" <?php echo ($item['is_super_admin']) ? 'checked="checked"':'' ?>/>Super Admin
							
						</label>
						<label>
							<input class="is_admin" type="radio" name="is_super_admin" id="" value="0" <?php echo ($item['is_super_admin']) ? '':'checked="checked"' ?>/>Admin
						</label>
						
					</div>		
				
				
				</div><!--wide-->
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<h4>CMS Access</h4>
					<div class="form_field cms_access">
						
						<div class="row">
							<?php $n = 0; ?>
							<?php foreach($controllers as $c): ?>
							<div class="col-md-3">
								<h5><?php echo $c['title'] ?></h5>
									<label>
									<input class="" type="radio" name="access[<?php echo $c['controller'] ?>]" id="" value="none" <?php echo ($levels[$c['controller']]===null) ? 'checked="checked"':'' ?>/>No Access
	
								</label>		
								<label>
									<input class="" type="radio" name="access[<?php echo $c['controller'] ?>]" id="" value="read" <?php echo ($levels[$c['controller']]==='0') ? 'checked="checked"':'' ?>/>Read Access
	
								</label>
								<label>
									<input class="" type="radio" name="access[<?php echo $c['controller'] ?>]" id="" value="edit" <?php echo ($levels[$c['controller']]) ? 'checked="checked"':'' ?>/>Edit Access
								</label>	
							</div>
							<?php if(++$n % 4 == 0): ?>
							<div class="clearfix"></div>
							<?php endif; ?>
							
							<?php endforeach; ?>
						</div>
						
					</div>
					<div class="form_field is_super_message">
						Super Admins have <strong>Edit Access</strong> to all controllers, and the User Manager.
					</div>

				</div><!-- thin -->
			</div>
			

		  <div class="clear"></div>
		  <div class="row">
				<div class="col-sm-12">
					<div class="action_buttons">
						<input class="btn yes btn-primary" type="submit" id="submit" value="<?php echo($id ? 'Update' : 'Create'); ?>" /> &nbsp;&nbsp;&nbsp;
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
			<p>Add New <?php echo $name_var; ?></p>
		</a>

		<?php if(!empty($items)): ?>

		<table class="cms_table table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Level</th>
					<th>Actions</th>
				</tr>
			</thead>
		  
		   <tbody>
				<?php foreach($items as $i): ?>
				
				<tr>
					<td><?php echo $i['first_name'].' '.$i['last_name']; ?></td>
					<td><?php echo ($i['is_super_admin']) ? 'Super Admin' : 'Admin' ?></td>
					<td align="center">
						<a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a> | 
						<a onclick="return confirm('Are you sure you want to delete this item?')" href="<?php echo $module_url.'delete/'.$i['id']; ?>">Delete</a>
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