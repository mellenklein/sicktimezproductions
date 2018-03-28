<?php
	$html_title = (!empty($page_title) ? $page_title.' | ' : '').$config->get('meta_title');
	$banner_image = (isset($banner_image) && !empty($banner_image)) ? $banner_image : '' ;
	$meta_desc = (isset($meta_desc) && !empty($meta_desc)) ? $meta_desc : $config->get('meta_desc') ;
	$meta_keys = (isset($meta_keys) && !empty($meta_keys)) ? $meta_keys : $config->get('meta_keys') ;
	$meta_author = (isset($meta_author)) ? $meta_author : '' ;
	$og_image = (isset($og_image) && !empty($og_image)) ? $og_image : '' ;
	$jq_validate = (isset($jq_validate)) ? $jq_validate : false ;
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>

    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $html_title; ?></title>
		<meta name="keywords" content="<?php echo $meta_keys; ?>">
		<meta name="description" content="<?php echo $meta_desc; ?>" />
		<meta name="author" content="<?php echo $meta_author; ?>">
		<link href="https://fonts.googleapis.com/css?family=Lato:400,700,900" rel="stylesheet">
		<link rel="stylesheet" href="/css/app.css">
		<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.2.0/animate.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css">
		<!--[if lte IE 9]>
	    <style>
	      label.show-for-ie9 {
	        display: block !important;
	        text-align: left !important;
	      }
	    </style>
		<![endif]-->
		<link rel="icon" href="/favicon.png">
		<meta property="og:site_name" content="Sick Timez Productions">
		<meta property="og:title" content="<?php echo $html_title; ?>">
		<meta property="og:description" content="<?php echo $meta_desc; ?>">
		<meta property="og:type" content="website">
		<?php if(!empty($og_image)): ?>
		<meta property="og:image" content="<?php echo $og_image; ?>">
		<meta property="og:image:width" content="400">
		<?php endif; ?>
		<?php echo $output['head']; ?>
  </head>

	<body>

		<script>
			// Google analytics goes here
		</script>


		<div id="main_nav_wrapper">
			<div id="main_nav_bg"></div>
			<div id="main_nav" class="closed ">
				<div>
					<a href="/" id="go_home">
						<div class="logo-container">
							<h3 class="logo">Sick Timez</h3>
							<h6 class="logo">Productions</h6>
						</div>
					</a>

					<div id="global-nav-items" class="show-for-medium">
						<ul>
							<li><a href="/about">About</a></li>
							<li><a href="/music">Music</a></li>
							<li><a href="/videos">Video</a></li>
							<li><a href="/limited-edition">Limited Edition</a></li>
							<li><a href="/contact">Contact</a></li>
						</ul>
					</div>
					<div id="main-hamburger" class="hide-for-medium">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</div>
				<div id="main_nav_items">
					<ul class="menu vertical text-center">
						<li><a href="/about">About</a></li>
						<li><a href="/music">Music</a></li>
						<li><a href="/videos">Video</a></li>
						<li><a href="/limited-edition">Limited Edition</a></li>
						<li><a href="/contact">Contact</a></li>
					</ul>
				</div>
			</div>
		</div>
		<!-- Main content goes here -->
		<div id="mainContentBox">
			<?php echo $output['content']; ?>
		</div>
		<!-- end of Main content -->



		<!-- Footer starts here: -->
		<footer class="tb-pad-60" style="background: #475362;">
			<div class="row">
				<div class="columns large-12 text-center">
					<h4><a  href="/contact">Contact</a></h4>
					<div class="social">
						<a href="http://www.twitter.com/sicktimez" target="_blank"><i class="fa fa-twitter"></i></a>
						<a href="https://www.facebook.com/profile.php?id=614638324" target="_blank"><i class="fa fa-facebook"></i></a>
						<a href="https://www.instagram.com/sicktimez" target="_blank"><i class="fa fa-instagram"></i></a>
					</div>
					<p class="copy">&copy; <?php echo date("Y"); ?> Sick Timez Productions, All Rights Reserved.</p>
				</div>
			</div>
		</footer>
		<!-- end of Footer -->

		<script src="/bower_components/jquery/dist/jquery.js"></script>
		<!-- <script src="/plugins/masonry/masonry.min.js"></script> -->
    <script src="/bower_components/what-input/what-input.js"></script>
    <script src="/bower_components/foundation-sites/dist/js/foundation.js"></script>
    <script src="/js/app.js"></script>

		<script>
			$(document).ready(function(){

				// Smooth scrolling on link click:
				$(function() {
					$('a[href*="#"]:not([href="#"])').click(function() {
						if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
							var target = $(this.hash);
							target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
							if (target.length) {
								$('html, body').animate({
									scrollTop: target.offset().top
								}, 1000);
								return false;
							}
						}
					});
				});

				//Opening the full page nav menu
				$('#main-hamburger').click(function() {
					$(this).toggleClass('open');
					$('#main_nav').toggleClass('open').toggleClass('closed').removeClass('nav-up');
					$('#main_nav_items a').toggleClass('open').toggleClass('closed');
				});

				//Turn the top bar black on scroll:
				$(window).scroll(function(){
					var height = $(window).scrollTop;
					if(height > 50) {
						$('#main_nav_bg').css({
							'background': '#000000',
						});
					}
					if(height <= 50) {
						$('#main_nav_bg').css({
							'background': 'transparent',
						});
					}
				});



			});
		</script>
		<?php echo $output['footer_js']; ?>
	</body>
</html>
