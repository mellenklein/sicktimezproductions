<?php
$page_title = 'Current songs';
$banner_image = 'header-mic.jpg';

$songs_db = new Model('songs');
$songs = $songs_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$songs_db->load($url_segments[1], 'slug');
}

ob_start();
?>
<?php include($thinBannerURL); ?>
<?php if(!isset($songs_db->data) || empty($songs_db->data)): ?>
	<!-- Team page Content -->
	<div class="music-page">
		<div class="row">
			<div class="large-12 columns">
				<p class="text-center" id="errorMsg"></p>
			</div>
		</div>
		<?php foreach($songs AS $li): ?>
		<div class="no-marg row tb-pad-60 song">
			<div class="columns large-6 no-pad">
				<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'/assets/images/songs/'.$li['featured_image']; ?>);"></div>
			</div>
			<div class="columns large-5 medium-offset-1 scale-height">
				<h3 class="name"><?php echo $li['name']; ?></h3>
				<h4><?php echo $li['artist']; ?></h4>
				<h4><?php echo $li['album']; ?></h4>
				<?php if($li['website'] != ''): ?>
					<p style="margin-top:30px;"><a target="_blank" class="button hollow dark" href="<?php echo $li['website']; ?>">Website</a></p>
				<?php endif; ?>
				<p style="margin-top:30px;"><a target="_blank" class="button hollow dark" href="<?php echo $config->get('url').'/assets/uploads/songs/'.$li['file']; ?>">Download Song</a></p>

			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<!-- End of Team page Content -->
<?php endif; ?>



<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
?>
<script type="text/javascript">

</script>
<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
