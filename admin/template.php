<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>CMS | <?php echo $config->get('sitename'); ?></title>
	<meta description="" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!--[if lte IE 9]>
	<style>
		label.show-for-ie {
			display: block !important;
		}
	</style>
	<![endif]-->


	<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>-->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
   <script>window.jQuery || document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"><\/script>')</script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

	<script src="<?php echo $config->get('admin_url'); ?>plugins/jquery-ui/js/jquery-ui-1.9.2.custom.min.js"></script>
	<script src="<?php echo $config->get('admin_url'); ?>plugins/jquery-ui/js/jquery-ui-timepicker-addon.js"></script>
	<script src="<?php echo $config->get('admin_url'); ?>plugins/responsive-tables/responsive-tables.js"></script>
	<script src="<?php echo $config->get('admin_url'); ?>plugins/modsec_fix/modSecurityFix.js"></script>
	<script type="text/javascript" src="<?php echo $config->get('admin_url'); ?>js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="<?php echo $config->get('admin_url'); ?>js/app.js"></script>

	<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>css/normalize.css" />
	<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" media="screen" title="no title" charset="utf-8">
	<link href='https://fonts.googleapis.com/css?family=Raleway:400,500,700,800,300' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:600' rel='stylesheet' type='text/css'>
   <link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>css/select.css">
   <link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>css/main.css">
	<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>plugins/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.min.css" />
	<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>plugins/jquery-ui/css/jquery-ui-timepicker-addon.css" />
	<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>plugins/responsive-tables/responsive-tables.css">
	<script type="text/javascript">
	$( function(){
		// Date/time pickers
		$('.date').datepicker({
			dateFormat: "m/d/yy"
		});

		$('.datetime').datetimepicker({
			dateFormat: "m/d/yy",
			timeFormat: "h:mm tt"
		});

		$('.time').datetimepicker({
			timeOnly: true,
			timeFormat: "h:mm tt"
		});

		// Sortables
		$('.sortable').sortable({
			axis: 'y',
			update: function(event, ui){
				var ctrl = $(this).attr('rel');
				var list = $(this).sortable('serialize');
				$.post('<?php echo $config->get('admin_url'); ?>'+ctrl+'/sort', list);
			}}).disableSelection();
		$('form').not('.no-modsec-fix').submit(function(){
			checkTextData();
		});
	});
	</script>


	<?php if($rte): ?>
	<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
	<script type="text/javascript">
	tinymce.init({
		selector:'.mce',
		height:400,
		plugins:[
			"advlist link image lists charmap hr anchor searchreplace code media table paste textcolor"
		],
		image_advtab: true,
		content_css: "/admin/css/rte.css",
		toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media",
		external_filemanager_path:"/admin/plugins/filemanager/",
		filemanager_title:"Filemanager",
		filemanager_access_key:"f0xb0x",
		external_plugins: { "filemanager" : "/admin/plugins/filemanager/plugin.min.js"},
		relative_urls: false
	});
	tinymce.init({
		selector:'.mce-small',
		height:100,
		menubar: false,
		plugins:[
			"link"
		],
		content_css: "/admin/css/rte.css",
		toolbar: "undo redo | bold italic | link",
		relative_urls: false
	});
	</script>
	<?php endif; ?>

	<?php if($uploader): ?>
	<link rel="stylesheet" href="<?php echo $config->get('admin_url').'plugins/uploadify/uploadify.css'; ?>" />
	<link rel="stylesheet" href="<?php echo $config->get('admin_url').'plugins/uploadify/uploadifive.css'; ?>" />
	<script type="text/javascript" src="<?php echo $config->get('admin_url').'plugins/uploadify/jquery.uploadify.min.js'; ?>"></script>
	<script type="text/javascript" src="<?php echo $config->get('admin_url').'plugins/uploadify/jquery.uploadifive.min.js'; ?>"></script>
	<?php endif; ?>

	<?php if(!empty($chosen)): ?>
		<link rel="stylesheet" href="<?php echo $config->get('admin_url'); ?>plugins/chosen/chosen.css" />
		<script type="text/javascript" src="/admin/plugins/chosen/chosen.jquery.js"></script>
		<script type="text/javascript" src="/admin/plugins/chosen/prism.js"></script>
	<?php endif; ?>

	<style>
		#content_login .row .col-sm-12 {
			padding: 0;
		}
		#content_login .toggle-nav {
			display: none !important;
		}
		.clear {
			clear: both;
		}
		.bg-img:not(.interior) {
			background-image: url("<?php echo $config->get('admin_url'); ?>images/landing_page_bg_imgs/<?php echo $_SESSION['dashboard_bg']; ?>");
		}
	</style>

	<?php echo $output['head']; ?>

</head>

<body class="<?php echo $ctrl; ?>">
	<?php if (!empty($user->nav)): ?>
		<div class="container-sidebar col-lg-2">
			<a href="#" class="close-menu clearfix"><i class="fa fa-times"></i></a>
				<?php
					$navCat = '';
				?>
				<div id="nav">
					<?php foreach($user->nav as $n): ?>
						<?php if($navCat != $n['cat_title']): ?>
							<?php if($navCat != ''): ?>
								</ul>
							<?php endif; ?>
							<?php $navCat = $n['cat_title']; ?>
							<h5><?php echo $n['cat_title']; ?></h5>
							<ul>
						<?php endif; ?>
						<?php $class = ($nav_active == $n['controller'] ? 'class="nav_on"' : ''); ?>
						<a <?php echo $class; ?> href="<?php echo $config->get('admin_url').$n['controller']; ?>"><li><?php echo $n['title']; ?></li></a>
					<?php endforeach; ?>
					</ul>

					<?php if($user->data['is_super_admin']): ?>
						<h5>Admin Tools</h5>
						<ul>
						<?php $class = ($nav_active == 'users' ? 'class="nav_on"' : ''); ?>
						<a <?php echo $class; ?> href="<?php echo $config->get('admin_url')?>users" ><li>Users</li></a>
						</ul>
					<?php endif; ?>

				</div>
		</div>
	<?php endif; ?>

	<div class="container-main <?php echo ($user->id) ? 'col-md-12 col-lg-10' : 'col-sm-12' ; ?>" id="content<?php echo($user->id ? '' : '_login'); ?>">
		<div class="off-canvas-overlay"></div>
		<div class="row" id="topNavBar">
			<div class="col-md-9 col-xs-8 topNav">
				<a href="#" class="toggle-nav"><i class="fa fa-bars"></i></a>
				<a href="<?php echo $config->get('admin_url'); ?>"><img class="ff-logo" src="<?php echo $config->get('admin_url'); ?>images/FoxDen-logo.png" alt="Welcome to the FoxDen"></a>
				<?php if($user->id): ?>
					<img class="client-logo" src="<?php echo $config->get('client_logo'); ?>" alt="<?php echo $config->get('sitename'); ?>">
				<?php endif; ?>
			</div>
			<div class="col-md-3 col-xs-4 login">
				<?php if($user->id): ?>
					<h5><?php echo '<span id="log_first_name">'.$user->data['first_name'].'</span><span id="log_space"> </span><span id="log_last_name">'.$user->data['last_name'].'</span>'; ?></h5>
					<a id="logout" href="<?php echo $config->get('admin_url'); ?>logout">Logout</a>
				<?php else: ?>
					<img class="client-logo" src="<?php echo $config->get('client_logo'); ?>" alt="<?php echo $config->get('sitename'); ?>">
				<?php endif; ?>
			</div>
		</div>
		<?php echo show_message(); ?>
		<?php echo $output['content']; ?>
		<div class="clear"><!-- x --></div>
	</div>

	<div class="clear"><!-- x --></div>

	<footer>

	</footer>

</body>
</html>
