<?php
$page_title = 'Team';
$banner_image = 'page-hero03.jpg';

$team_db = new Model('team');
$team_members = $team_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$team_db->load($url_segments[1], 'slug');
}

ob_start();
?>
<?php include($thinBannerURL); ?>
<?php if(!isset($team_db->data) || empty($team_db->data)): ?>
	<!-- Team page Content -->
	<div class="team-page">
		<?php foreach($team_members AS $tm): ?>
		<div class="no-marg row tb-pad-60" id="<?php echo $tm['slug']; ?>">
			<div class="columns large-6 no-pad">
				<a href="<?php echo $config->get('url').'team/'.$tm['slug']; ?>">
					<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'assets/images/team/'.$tm['featured_image']; ?>);"></div>
				</a>
			</div>
			<div class="columns large-5 medium-offset-1">
				<h2 class="name"><?php echo $tm['name']; ?></h2>
				<h4><?php echo ($tm['partner'] == 1) ? '<span style="font-size:22.5px;">Partner</span><br/>' : '' ; ?><?php echo $tm['title']; ?></h4>
				<hr class="hr-left">
				<div class="tb-pad-30 team-contact">
					<a href="tel:<?php echo $tm['phone']; ?>"><?php echo $tm['phone']; ?></a><br />
					<a class="all-caps" href="mailto:<?php echo $tm['email']; ?>"><?php echo $tm['email']; ?></a>
			 	</div>
				<p class="bio"><?php echo strip_tags($tm['blurb']); ?></p>
				<h3>About <?php echo $tm['name']; ?></h3>
				<p><?php echo $tm['about'] ?></p>
				<h3><a class="btn btn-primary btn-lg bio-btn" href="<?php echo $config->get('url').'team/'.$tm['slug']; ?>" role="button">Full Bio</a></h3>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<!-- End of Team page Content -->
<?php else: ?>
	<!-- Team Member Content -->
	<div class="team-page single">
		<div class="row tb-pad-60">
			<div class="columns large-12 small-centered">
				<?php if(!empty($team_db->data['featured_image'])): ?>
					<div class="bg-img" style="background-image: url('<?php echo $config->get('url'); ?>assets/images/team/<?php echo $team_db->data['featured_image']; ?>');"></div>
				<?php endif; ?>

				<h2 class="name"><?php echo $team_db->data['name']; ?></h2><br>
				<hr class="hr-left"><br>
				<h4><?php echo $team_db->data['title']; ?></h4><br>
				<div class="tb-pad-30 team-contact">
				 <a href="tel:<?php echo $team_db->data['phone']; ?>"><?php echo $team_db->data['phone']; ?></a><br />
				 <a href="mailto:<?php echo $team_db->data['email']; ?>"><?php echo $team_db->data['email']; ?></a>
			 </div>
				<p class="bio"><?php echo $team_db->data['content']; ?></p>

			</div>
		</div>
	</div>

	<!-- End of Team Member Content -->
<?php endif; ?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>
