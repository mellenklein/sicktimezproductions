<?php
$page_title = 'Videos';
$banner_image = 'header-video.jpg';

$videos_db = new Model('videos');
$videos = $videos_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$videos_db->load($url_segments[1], 'slug');
}

ob_start();
?>
<?php include($thinBannerURL); ?>
<?php if(!isset($videos_db->data) || empty($videos_db->data)): ?>
	<!-- Team page Content -->
	<div class="music-page">
		<div class="row">
			<div class="large-12 columns">
				<p class="text-center" id="errorMsg"></p>
			</div>
		</div>
		<?php foreach($videos AS $v): ?>
		<div class="row tb-pad-60 song">
			<div class="columns large-6 no-pad">
				<iframe width="560" height="315" src="<?php echo $v['url']; ?>" frameborder="0" allowfullscreen></iframe>
			</div>
			<div class="columns large-5 medium-offset-1 scale-height">
				<h3 class="name"><?php echo $v['name']; ?></h3>
				<h4><?php echo $v['artist']; ?></h4>
				<h4><?php echo $v['album']; ?></h4>
				<?php if($v['website'] != ''): ?>
					<p style="margin-top:30px;"><a target="_blank" class="button hollow dark" href="<?php echo $v['website']; ?>">Website</a></p>
				<?php endif; ?>
					<p style="margin-top:30px;"><a target="_blank" class="button hollow dark" href="<?php echo $v['purchase_link']; ?>">Download Song</a></p>
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
