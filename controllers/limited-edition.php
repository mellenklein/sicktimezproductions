<?php
$page_title = 'Limited Edition';
$banner_image = 'header-guitar.jpg';


ob_start();
?>
<?php include($thinBannerURL); ?>

	<!-- About page Content -->
	<div class="about-page">
		<div class="row tb-pad-60">
			<div class="large-10 large-centered columns">
				<h3 class="tb-pad-15">Limited Edition Details</h3>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			</div>
		</div>



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
