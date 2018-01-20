<?php
	if($url_segments[0]!='') {
		if(!isset($banner_image) || $banner_image == '') {
			$banner_image = 'hero-bg.jpg';
		}
	} else $banner_image = 'home-bg.jpg';
?>

<div style="background-image: url('/images/<?php echo $banner_image; ?>')" class="hero <?php
	echo $url_segments[0] == '' ? 'home' : 'interior ';
	echo $url_segments[0];
?>">

	<!-- Home Hero only: -->
	<?php if($url_segments[0] == ''):  ?>
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
							<?php echo $home_hero_content ?>
							<div class="text-center tb-pad-30 rel">
								<button id="showPlayer" class="button hollow large" style="width:400px;">Listen</button>
								<!-- <a class="button hollow large" style="width:200px;" href="/music">Listen</a> -->
								<div id="playerGoesHere">
									<?php include($audioPlayer); ?>
								</div>
							</div>
						</div>
					</div>

				<?php endif; ?>
				<?php if(!empty($services_blurb)): ?>
					<hr class="hr-center" />
					<p class="text-center" style="padding-bottom:40px;"><?php echo $services_blurb ?></p>
				<?php endif; ?>
			</div>
		</div>
		<!-- end of Hero text content -->
	</div>
</div>
