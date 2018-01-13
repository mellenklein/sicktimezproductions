<?php 
	if(!isset($contentCallouts) || empty($contentCallouts)) {
		$contentCallouts = new ContentCallouts();
		$callout_items = $contentCallouts->get_callouts();
	} else {
		$callout_items = $contentCallouts->get_callouts();
	}
?>
<?php if(!empty($callout_items)): ?>
	<div class="wrapper-news" id="news">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php foreach($callout_items AS $callout_item): ?>
							<div class="col-sm-4">
								<h3><?php echo $callout_item['callout_data']['callout_title']; ?></h3>
								<a href="<?php echo $callout_item['callout_data']['callout_article_url']; ?>">
									<div class="img" style="background-image: url('<?php echo $callout_item['callout_data']['callout_image_url']; ?>')">
										<div class="border-top"></div>
										<div class="overlay"></div>
									</div>
								</a>
								<a href="<?php echo $callout_item['callout_data']['callout_article_url']; ?>"><h2><?php echo $callout_item['callout_data']['callout_article_title']; ?></h2></a>
								<p><?php echo $callout_item['callout_data']['callout_article_blurb']; ?></p>
								<p><a class="btn btn-primary btn-lg" href="<?php echo $callout_item['callout_data']['callout_article_url']; ?>" role="button">Read More</a></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>