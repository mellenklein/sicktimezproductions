<?php
$page_title = 'Locations';
$banner_image = '';

ob_start();
/* Custom css or other header links/includes go here. */
?>

<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
/* Main page content goes here */
?>

<!-- Hero for interior pages: -->
<div class="hero interior">
	<div class="title-bar" data-responsive-toggle="homeMenu" data-hide-for="large">
		<div class="title-bar-title"><img src="/images/spark-logo.png" /></div>
		<button class="menu-icon fl-right" type="button" data-toggle></button>
	</div>
	<div class="top-bar" id="homeMenu">
		<div class="row">
			<div class="large-4 columns">
					<a href="/"><img class="spark-logo show-for-large" src="/images/spark-logo.png" alt="Spark"></a>
			</div>
			<div class="large-8 columns text-right">
				<ul class="dropdown menu" data-dropdown-menu>
					<li><a href="/leadership">Leadership</a></li>
					<li><a href="/locations">Locations</a></li>
					<li><a href="/academic">Academic</a></li>
					<li><a href="/about">About</a></li>
					<li><a href="#contact">Contact</a></li>
					<li class="show-for-large fl-right"><a href="#" id="searchbarTarget"><i class="fa fa-search"></i></a></li>
					<div class="searchContainer">
						<form class="" action="/search" method="post">
							<input type="text" class="show-for-large" placeholder="Search...">
							<input type="submit" class="button secondary" value="Search">
						</form>
					</div>

					<li class="mobile-search hide-for-large">
						<input type="text" name="mobileSearch" value="" placeholder="Search...">
						<a href="#"><i class="fa fa-search"></i></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="interior-hero">
	<div class="row">
		<div class="columns large-12">
			<div class="title-tab">
				<?php echo $page_title; ?>
			</div>
		</div>
	</div>
</div>
<!-- end of Hero for interior pages -->
<div class="spacer"></div>

<!-- Main content: -->
<div class="row">
	<div class="large-10 columns small-centered tb-pad-60 main-content">
		<?php echo $page->data['content']; ?>
		This is the locations page. Content is currently coming from the controller called <em>locations.php</em>.<br>
		This page will not use the normal <strong>pages</strong> template, because the mock up will be different than the other pages.
	</div>
</div>
<!-- end of main CMS content -->


<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
/* Custom JavaScript goes here. */
?>
<script type="text/javascript">

$(document).ready(function() {
	// any js that is specific to the Locations page goes here
});
</script>
<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
