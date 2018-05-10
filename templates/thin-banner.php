<?php
	if($url_segments[0] != '') {
		if(!isset($banner_image) || $banner_image == '') {
			$banner_image = 'hero-bg.jpg';
		}
	} else $banner_image = 'home-hero-mic.jpg';
?>

<div style="background-image: url('<?php $config->get('url'); ?>/images/<?php echo $banner_image; ?>')" class="hero <?php
	echo $url_segments[0] == '' ? 'home' : 'interior ';
	echo $url_segments[0];
?>">

	<!-- Home Hero only: -->
	<?php if($url_segments[0] == ''):  ?>
		<!-- <div id="banner">
      <video autoplay="" muted="" loop="" poster="images/home-hero00.jpg" id="home-video">
				<source src="/video/video-home-compressed.mp4" type="video/mp4">
			</video>
    </div> -->
	<?php endif; ?>
	<!-- end of Home Hero -only content -->

	<div class="columns no-marg">
		<!-- Nav template included: -->
		<!-- Hero text content starts here: -->
		<div class="row">
			<div class="columns large-12 small-centered">
				<h1 class="page-title text-center"><?php echo $page_title; ?></h1>
				<?php if(!empty($home_hero_content)): ?>

					<div class="row">
						<div class="large-12 columns text-center hero-content">
							<div class="shade-box">
								<div class="triangle tri-left show-for-medium"></div>
								<div class="triangle tri-right show-for-medium"></div>
								<?php echo $home_hero_content ?>

								<div class="text-center tb-pad-30 rel">
									<button id="showPlayer" class="button hollow large" style="width:400px;">Listen</button>
									<div id="playerGoesHere">
										<?php include($audioPlayer); ?>
									</div>
								</div>

							</div>
						</div>
					</div>

				<?php endif; ?>

			</div>
		</div>
		<!-- end of Hero text content -->
	</div>
</div>
