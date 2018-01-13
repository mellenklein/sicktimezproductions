<?php
$page_title = 'Investment Services';
$banner_image = 'page-hero08.jpg';

$services_blurb = 'OakPoint is a premier real estate investment and advisory firm based in Nashville, Tennessee.  The firm’s founding partners have completed over $2.5 billion of transactions on behalf of their clients.  By targeting both value-add and core investment opportunities in the multifamily, office, residential, and retail sectors, we provide our investors with real estate diversification under the radar screen of institutional capital.  We identify assets with a unique story, those where we can create value through an entrepreneurial approach to repositioning and management.  Over the last five years, OakPoint has purchased roughly $215 million in real estate, and have been entrusted with more than $52 million in equity.';

if(isset($url_segments[1])) {
	$case_db->load($url_segments[1], 'slug');
}

ob_start();
?>
<?php include($thinBannerURL); ?>


	<!-- Services page Content: -->
	<div class="services-page main-content">
		<div class="row">
			<div class="columns large-4 medium-5 large-offset-1 text-right">
				<div class="tb">
					<div class="vm">
						<h4>Acquisition Profile</h4>
						<hr class="hr-right" />
						<p>Both stable and value-added / repositioning opportunities in the multifamily, office, residential, and retail sectors.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 end">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services01.jpg' ?>);"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="columns large-4 medium-push-7 medium-5 text-left end">
				<div class="tb">
					<div class="vm">
						<h4>Strategy</h4>
						<hr class="hr-left" />
						<p>Employing an entrepreneurial investment strategy designed to minimize competition with other capital sources while maximizing value through intensive post-acquisition management.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 large-push-1 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 medium-pull-7 large-offset-1">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services05.jpg' ?>);"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="columns large-4 medium-5 large-offset-1 text-right">
				<div class="tb">
					<div class="vm">
						<h4>Process and Capacity</h4>
						<hr class="hr-right" />
						<p>Investment decisions are made by the firm’s principals, providing an efficient decision-making process.  In addition, our lead investor has the ability to close on an all-cash basis.  Our investment capacity is up to $50MM with an emphasis on deals sized from $5MM to $25MM.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 end">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services06.jpg' ?>);"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End of Services page Content -->

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>
<script type="text/javascript">
$(document).ready(function() {
});
</script>
