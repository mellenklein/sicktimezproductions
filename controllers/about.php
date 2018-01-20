<?php
$page_title = 'About';
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
				<h3 class="tb-pad-15">Meet David Joshua Adkins</h3>
				<p>A multi-instrumentalist from the age of five, producer David Joshua Adkins immersed himself in the music industry when he moved to Nashville in 2007.  At Belmont University, he majored in Commercial Music and soon discovered a passion for producing.  He established Sick Timez Productions in 2011 and has consistently produced music on the cutting edge of modern recording trends.  His original production style blends powerful arrangements, creative rhythms, and tasteful melodic passages to create a unique and commercially viable product for the Artist.  He specializes in Electropop, and has production experience covering the genres of jazz, hard rock, metal, and choral music.  And, as founder of <a href="https://www.facebook.com/groups/TheNewNashvilleSound/" target="_blank">The New Nashville Sound</a>, his influence in Nashville's pop music production scene continues to inspire his peers.</p>
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
									<div class="medium-8 small-centered columns" data-equalizer-watch>
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
