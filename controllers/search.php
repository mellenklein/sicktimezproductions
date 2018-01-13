<?php
// require_once('classes/Resource.php');
// require_once('classes/News.php');
// $resource = new Resource();
// $news = new News();
$search = '';
$page_title = 'Search Results';

// Process post and redirect
if (isset($_POST['search'])) {
	$_SESSION['search'] = $_POST['search'];
	header('location:'.$config->get('url').'search');
	exit();
}

// Read from session
if (!empty($_SESSION['search'])) {
	$search = $_SESSION['search'];
}

if ($search != '') {

	// Search pages
	$args = array(
		'where' => "(title LIKE ? OR content LIKE ?) AND title != 'Privacy Policy'",
		'order' => 'title',
		'params' => array('%'.$search.'%', '%'.$search.'%')
	);
	$pages = $page->get_items($args);

}

ob_start();
/* Custom css or other header links/includes go here. */
?>

<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
/* Main page content goes here */
?>

<div class="callout-row" style="background:#404040; color:#ffffff;">
	<div class="row">
		<div class="columns medium-10 medium-offset-1 tb-pad-30">
			<h1><?php echo $page_title; ?> for <em><?php echo $search; ?></em></h1>
		</div>
	</div>
</div>

<?php if(empty($pages)): ?>
<div class="row">
	<div class="columns medium-10 medium-offset-1 tb-pad-30">
		<p style="padding-top:30px;padding-bottom:150px;">No results found.</p>
	</div>
</div>

<?php else: ?>

<div class="resources-wrapper">

	<?php if (!empty($pages)): ?>
	<div class="callout-row">
		<div class="row">
			<div class="columns medium-10 medium-offset-1 tb-pad-30">
				<h1>Pages</h1>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="columns large-8 medium-10 medium-centered tb-pad-30 resource-rows">
			<?php foreach ($pages as $i): ?>
			<a href="<?php echo $config->get('url').$i['slug']; ?>">
				<div class="row">
					<div class="columns small-2">
						<img src="/images/doc-icon.png" class="icon">
					</div>
					<div class="columns small-10">
						<div class="row">
							<div class="columns medium-12 heading">
								<?php echo $i['title']; ?>
							</div>
							<div class="columns medium-12">
								<?php echo substr(strip_tags($i['content']), 0, 200); ?>
							</div>
						</div>
					</div>
				</div>
			</a>
			<hr>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>


</div>

<?php endif; ?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
/* Custom JavaScript goes here. */
?>

<script>
	$(document).ready(function(){

	});
</script>

<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
