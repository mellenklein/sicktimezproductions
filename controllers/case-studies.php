<?php
require_once('classes/CaseStudy.php');
$page_title = 'Case Studies';
$banner_image = 'page-hero05.jpg';

$case_db = new CaseStudy();


$case_items = $case_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$case_db->load($url_segments[1], 'slug');
}

// if we find a case study:
if(!empty($case_db->data)) {
	$case_list = $case_db->get_items(array(
		'where' => 'active = 1 AND id != ?',
		'params' => array($case_db->data['id'])
	));
	$last_item = $case_db->get_items(array('where' => 'active = 1', 'order' => 'sort', 'limit' => 1));

	if(!empty($last_item)) {
		$lastSort = $last_item[0]['sort'];
		$fields['sort'] = $lastSort+1;
	} else {
		$fields['sort'] = 1;
	}

	$page_title = $case_db->data['title'];
	$banner_image = 'page-hero06.jpg';
	$case_study_blurb = $case_db->data['blurb'];
	if(!empty($case_db->data['dc_metadesc'])) {
		$meta_desc = htmlentities(strip_tags($case_db->data['dc_metadesc']));
	} else if (!empty($case_db->data['dc_blurb'])) {
		$meta_desc = htmlentities(strip_tags($case_db->data['dc_blurb']));
	}
	if(!empty($case_db->data['authors'][0]['name'])) {
		$meta_author = $case_db->data['authors'][0]['name'];
	}
	if(!empty($case_db->data['dc_keywords'])) {
		$meta_keys = $case_db->data['dc_keywords'];
	}
	if(!empty($case_db->data['featured_image'])) {
		$og_image = $config->get('url').'assets/images/dynamic_content/'.$case_db->data['featured_image'];
	}
} else {
	$case_items = $case_db->get_items(array('where' => 'active = 1', 'order' => 'sort'));
}
ob_start();
?>
<?php include($thinBannerURL); ?>

<?php if(empty($case_db->data)): ?>
	<!-- Case Studies list page Content: -->
	<div class="case-studies-page main-content">
		<?php foreach($case_items AS $i): ?>
		<div class="no-marg row tb-pad-100 overlay" style="background-image: url(<?php echo $config->get('url').'../../assets/images/case-studies/'.$i['featured_image']; ?>);">
			<div class="columns medium-12 no-pad">
				<div class="bg-img">
					<div class="columns medium-8 small-centered">
						<h4 class="name text-center"><?php echo $i['title']; ?></h4>
						<p class="text-center all-caps"><?php echo $i['address']; ?></p>
						<div class="text-center"><a class="button hollow" href="/case-studies/<?php echo $i['slug']; ?>" style="display: inline-block;">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<!-- End of Case Studies list page Content -->
<?php else: ?>

	<?php
		$curr_id = $case_db->data['id'];
		$curr_slug = $case_db->data['slug'];
		$total = count($case_items);

		foreach($case_items AS $key => $i) {
			$position = $key+1;
			if($position == 1) {
				$first = $i;
			}
			if($position == $total) {
				$last = $i;
			}
	 	}

		foreach($case_items AS $key => $i) {
			$position = $key+1;

			if($i['id'] == $curr_id) {
				$next = $position+1;
				$prev = $position-1;
				$total = count($case_items);

				$next_url = $case_items[$key+1]['slug'];
				$prev_url = $case_items[$key-1]['slug'];

				if($next > $total) {
					$next = 1;
					$next_url = $first['slug'];
				}
				if($prev < 1) {
					$prev_url = $last['slug'];
				}
			}
		}


		//when viewing a case study:
			//first, get the case study id number,
			//then, compare that id number to the team members cross table
			//create a new array that contains only the team member ids associated with the current case study
				//cycle through the team_members array, and find the photos and slugs of the team members with that id.
				// pull those photos/slugs onto the case study page.
	?>

	<!-- Case Study view content: -->
	<div class="pagination">
		<span class="arrow-left"><a href="<?php echo $prev_url ?>"><img src="<?php echo $config->get('url').'images/prev-arrow.png'?>" alt="Previous"></a><span class="show-for-medium">Previous</span></span>
		<span class="arrow-right"><a href="<?php echo $next_url ?>"><img src="<?php echo $config->get('url').'images/next-arrow.png'?>" alt="Previous"></a><span class="show-for-medium">Next</span></span>
	</div>
	<div class="case-studies-page main-content">
		<div class="row tb-pad-60">
			<div class="columns large-8 medium-9 small-centered">
				<h4><?php echo $case_db->data['address']; ?></h4>
				<p><?php echo $case_db->data['categories']; ?></p>
				<div class="no-marg row tb-pad-100 article" style="background-image: url(<?php echo $config->get('url').'../../assets/images/case-studies/'.$case_db->data['featured_image']; ?>);"></div>
				<?php echo $case_db->data['content']; ?>
				<?php if(!empty($case_db->data['team_members'])): ?>
					<h4>Key Team Members</h4>
					<?php foreach($case_db->data['team_members'] as $tm): ?>
						<a href="/team/<?php echo $tm['slug']; ?>"><span class="thumbnail" style="background-image:url('/assets/images/team/<?php echo $tm['featured_image']; ?>')"></span></a>
					<?php endforeach; ?>
				<?php endif; ?>

				<div><a class="button hollow" target="_blank" href="../assets/uploads/case-studies/<?php echo $case_db->data['file']; ?>" style="display: inline-block;">Download Case Study</a></div>
			</div>
		</div>
	</div>
	<!-- end of News article view content -->

<?php endif; ?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>
<script type="text/javascript">
$(document).ready(function() {
});
</script>
