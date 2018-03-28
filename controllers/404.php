<?php
$page_title = 'Page Not Found';
$banner_image = 'header-studio.jpg';

ob_start();
?>
<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
/* Main page content goes here */
?>
<?php include($thinBannerURL); ?>
	<!-- About page Content -->
	<div class="about-page">
		<div class="row tb-pad-60">
			<div class="large-10 large-centered columns">
				<h3 class="text-center"><span style="text-decoration: line-through">808</span> 404 Error: Page Not Found</h3>
				<p class="text-center tb-pad-30"><a href="/" class="button hollow large">Back To Home</a></p>
			</div>
		</div>
	</div>


<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
?>
<script type="text/javascript" src="/plugins/slick/slick.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('.testimonials').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: true,
			autoplay: false,
			speed: 400,
			dots: false,
			adaptiveHeight: true,
			customPaging : function(slider, i) {
			 return '<img src="/images/dot.png" /><img src="/images/dot-active.png" />';
			},
			prevArrow: '<button type="button" class="slick-prev"><i class="fa fa-chevron-circle-left"></button>',
			nextArrow: '<button type="button" class="slick-next"><i class="fa fa-chevron-circle-right"></i></button>',
			cssEase: 'linear',
		});

		var testimonalHeight = $('.caption').height();
		$('.testimonials .bg-img').height((testimonalHeight+80)+'px');
	}); // end of document.ready
</script>
<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
