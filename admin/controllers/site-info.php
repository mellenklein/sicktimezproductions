<?php
$model = new Model('site_info');/*********************************************************/
/***********	CHECK / CREATE NECESSARY TABLES	**********/
/*********************************************************/
// Please be sure to update this with any changes to table structure
if(!$model->table_exists()) {	
	$create_sql = "CREATE TABLE IF NOT EXISTS `site_info` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(255) NOT NULL,
					  `value` varchar(255) NOT NULL,
					  `label` varchar(255) NOT NULL,
					  `import_to_config` tinyint(1) NOT NULL,
					  `show_in_cms` tinyint(1) NOT NULL,
					  PRIMARY KEY (`id`)
					  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$model->db->query($create_sql, array());	
	$insert_sql = "INSERT INTO `site_info` (`name`, `value`, `label`, `import_to_config`, `show_in_cms`) VALUES
					  ('sitename', 'FoxFuel CMS', 'Site Name', 1, 1),
					  ('primary_email', 'joe@foxfuelcreative.com', 'Primary email', 1, 1),
					  ('primary_number', '(662) 213-0260', 'Phone number', 1, 1),
					  ('google_nalytics', '', 'Google Analytics', 1, 1);";
	$model->db->query($insert_sql, array());
}
/*********************************************************/
/*****************	END TABLE CHECK	*******************/
/*********************************************************/
$args = array(
    'order'=>'name',
    'where'=>'show_in_cms = 1'
);if($user->data['is_fox_admin']) {	unset($args['where']);}
$items = $model->get_items($args);
$item = array();
$item_names = array(
    'singular'=>'Site Setting',
    'plural'=>'Site Settings'
);

$view = 'list';

// URL structure: [Base CMS URL]/controller/action/id
$action = !empty($url_segments[1]) ? $url_segments[1] : 'list';
$id = !empty($url_segments[2]) && ctype_digit($url_segments[2]) ? $url_segments[2] : 0;

if($id){
    $model->load($id);
    //if we are loaded, this would be a good time to make sure that this setting should be updated in the CMS
    if($model->data['show_in_cms']== 0 && $user->data['is_fox_admin'] == 0){
        header('location:'.$config->get('admin_url'));
        set_message('Sorry, that page does not exist', 'fail');
        exit();
    }
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
* ACTION: EDIT
* ---------------------------------------------------------------------------*/
if($action == 'edit'){
    $item = $model->data;
    $view = 'edit';
}
ob_start();
?>
<?php
/* ----------------------------------------------------------------------------
* VIEW: EDIT
* ---------------------------------------------------------------------------*/
if($view == 'edit'):
    ?>
    <script type="text/javascript" src="<?php echo $config->get('admin_url')?>plugins/masked-inputs/jquery.masked-input.min.js"></script>
    <script type="text/javascript">
        $(function() {
            $(".primary_number").mask("(999) 999-9999");
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
                            <label><?php echo $item['label'] ?></label>
                            <input class="in_text <?php echo $item['name'] ?>" type="text" name="value" value="<?php echo htmlentities($item['value']); ?>" />
                        </div>
                    </div>						  <div class="col-md-3">								<?php if($user->data['is_fox_admin']): ?>									<div class="form_field">										 <label>Show in CMS</label>										 <label><input class="" type="radio" name="show_in_cms" value="1" <?php echo ($item['show_in_cms'] == 1) ? 'checked="checked"' : '' ; ?> /> Yes</label> 										 <label><input class="" type="radio" name="show_in_cms" value="0" <?php echo (!isset($item['show_in_cms']) || $item['show_in_cms'] == 0) ? 'checked="checked"' : '' ; ?> /> No</label>									</div>								<?php endif; ?>						  </div>
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
            <?php if(!empty($items)): ?>
                <table class="cms_table table table-striped">
                    <thead>
                    <tr>
                        <th>Setting Name</th>
                        <th>Setting Value</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($items as $i): ?>
                        <tr>
                            <td><?php echo $i['label']; ?></td>
                            <td><?php echo $i['value']; ?></td>
                            <td class="action_block">
                                <a href="<?php echo $module_url.'edit/'.$i['id']; ?>">Edit</a>
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