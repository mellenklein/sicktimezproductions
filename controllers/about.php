<?php
$page_title = $page->data['title'];
$banner_image = 'header-studio.jpg';

$testimonials_db = new Model('testimonials');
$testimonials = $testimonials_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

ob_start();
?>
<link rel="stylesheet" href="/plugins/slick/slick.css">
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
				<?php echo $page->data['content']; ?>
			</div>
		</div>

		<?php if(!isset($testimonials_db->data) || empty($testimonials_db->data)): ?>
			<div class="blue-bg">
				<div class="row tb-pad-60 testimonials">
					<div class="columns large-10 small-centered">
						<div class="testimonials slick tb-pad-60">
							<?php foreach($testimonials AS $t): ?>
								<div class="slide">
									<div class="row" data-equalizer>
										<div class="large-8 small-9 small-centered columns" data-equalizer-watch>
											<div class="caption">
												<div class="caption-centered">
													<div class="quote">
														<div class="mark text-center">&ldquo;</div>
														<?php echo $t['quote']; ?>
														<p class="person"><em><?php echo $t['person'].' &ndash; '.$t['title']; ?></em></p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
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
