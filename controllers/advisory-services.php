<?php
$page_title = 'Advisory Services';
$banner_image = 'page-hero07.jpg';

$services_blurb = 'Our approach to the real estate business is consultative and relational first and foremost.  Rather than focusing on any one transaction, we take a holistic view of how real estate impacts our clients’ business.  Our solutions are intelligent, creative, and delivered with precise execution.  This is the standard we set for everything we do.<br/>Our professionals offer expert counsel based on decades of experience, and OakPoint’s commitment to providing results-oriented solutions and unparalleled customer service has quickly earned us a reputation for excellence.';

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
						<h4>Corporate Advisory</h4>
						<hr class="hr-right" />
						<p>We assist clients in making intelligent real estate decisions that align their space needs and business goals by leveraging years of experience, extensive industry contacts, current research, and the latest technology to provide the best information regarding availability, pricing, market conditions, and industry best practices. Corporate advisory services include tenant representation, site selection, surplus property sales and sale leaseback transactions.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 end">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services02.jpg' ?>);"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="columns large-4 medium-push-7 medium-5 text-left end">
				<div class="tb">
					<div class="vm">
						<h4>Acquisition &amp; Disposition</h4>
						<hr class="hr-left" />
						<p>Our team utilizes the latest research, tracks sales transactions, and monitors emerging capital sources.  We are constantly updating our database of local, regional, and national, and buyers to provide our clients with the most knowledgeable advice needed to get transactions done right.  Acquisition and disposition services involve the sale of land as well as income and non-income producing properties.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 large-push-1 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 medium-pull-7 large-offset-1">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services03.jpg' ?>);"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row tb-pad-100">
			<div class="columns large-4 medium-5 large-offset-1 text-right">
				<div class="tb">
					<div class="vm">
						<h4>Landlord Leasing</h4>
						<hr class="hr-right" />
						<p>We utilize traditional and non-traditional strategies to ensure maximum exposure and leasing activity at our clients’ properties, and we relentlessly pursue high-quality tenants.  We incorporate strategic consulting, marketing, lease negotiations, financial analysis, and oversight of building improvements.  All are delivered as part of a landlord representation package that focuses on maximizing asset value.</p>
					</div>
				</div>
			</div>
			<div class="columns medium-2 text-center show-for-medium">&nbsp;</div>
			<div class="columns large-4 medium-5 end">
				<div class="tb">
					<div class="vm">
						<div class="bg-img" style="background-image: url(<?php echo $config->get('url').'images/services04.jpg' ?>);"></div>
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
