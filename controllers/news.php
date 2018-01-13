<?php
require_once('classes/News.php');
$page_title = 'News';
$banner_image = 'page-hero04.jpg';

$news_db = new News();


$news_items = $news_db->get_items(array('order' => 'id', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$news_db->load($url_segments[1], 'slug');
}

// if we find an article:
if(!empty($news_db->data)) {
	$news_sidebar = $news_db->get_items(array(
		'order' => 'date DESC',
		'where' => 'active = 1 AND id != ?',
		'limit' => 5,
		'params' => array($news_db->data['id'])
	));
	//list of all articles for browsing arrows:
	$last_item = $news_db->get_items(array('where' => 'active = 1', 'order' => 'date DESC', 'limit' => 1));

	if(!empty($last_item)) {
		$lastSort = $last_item[0]['sort'];
		$fields['sort'] = $lastSort+1;
	} else {
		$fields['sort'] = 1;
	}


	$page_title = $news_db->data['title'];
	$banner_image = 'page-hero02.jpg';
	if(!empty($news_db->data['dc_metadesc'])) {
		$meta_desc = htmlentities(strip_tags($news_db->data['dc_metadesc']));
	} else if (!empty($news_db->data['dc_blurb'])) {
		$meta_desc = htmlentities(strip_tags($news_db->data['dc_blurb']));
	}
	if(!empty($news_db->data['authors'][0]['name'])) {
		$meta_author = $news_db->data['authors'][0]['name'];
	}
	if(!empty($news_db->data['dc_keywords'])) {
		$meta_keys = $news_db->data['dc_keywords'];
	}
	if(!empty($news_db->data['featured_image'])) {
		$og_image = $config->get('url').'assets/images/dynamic_content/'.$news_db->data['featured_image'];
	}
} else {
	$news_items = $news_db->get_items(array('order' => 'date DESC', 'where' => 'active = 1'));
}
ob_start();
?>
<?php include($thinBannerURL); ?>

<?php if(empty($news_db->data)): ?>
	<!-- News list page Content: -->
	<div class="news-page main-content">
		<?php foreach($news_items AS $i): ?>
		<div class="no-marg row tb-pad-100 overlay" style="background-image: url(<?php echo $config->get('url').'images/'.$i['featured_image']; ?>);">
			<div class="columns medium-12 no-pad">
				<div class="bg-img">
					<h4 class="name text-center"><?php echo $i['title']; ?></h4>
					<p class="text-center"><?php echo date("F j, Y", strtotime($i['date'])); ?></p>
					<div class="text-center"><a class="button hollow" href="/news/<?php echo $i['slug']; ?>" style="display: inline-block;">Read Article</a></div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<!-- End of News list page Content -->
<?php else: ?>

	<?php
		$curr_id = $news_db->data['id'];
		$curr_slug = $news_db->data['slug'];
		$total = count($news_items);

		foreach($news_items AS $key => $i) {
			$position = $key+1;
			if($position == 1) {
				$first = $i;
			}
			if($position == $total) {
				$last = $i;
			}
	 	}

		foreach($news_items AS $key => $i) {
			$position = $key+1;

			if($i['id'] == $curr_id) {
				$next = $position+1;
				$prev = $position-1;
				$total = count($news_items);

				$next_url = $news_items[$key+1]['slug'];
				$prev_url = $news_items[$key-1]['slug'];

				if($next > $total) {
					$next = 1;
					$next_url = $first['slug'];
				}
				if($prev < 1) {
					$prev_url = $last['slug'];
				}
			}
		}
	?>
	<!-- News article view content: -->
	<div class="pagination">
		<span class="arrow-left"><a href="<?php echo $prev_url ?>"><img src="<?php echo $config->get('url').'images/prev-arrow.png'?>" alt="Previous"></a><span class="show-for-medium">Previous</span></span>
		<span class="arrow-right"><a href="<?php echo $next_url ?>"><img src="<?php echo $config->get('url').'images/next-arrow.png'?>" alt="Previous"></a><span class="show-for-medium">Next</span></span>
	</div>
	<div class="news-page main-content">
		<div class="row tb-pad-120">
			<div class="columns large-8 large-offset-1 large-push-3 medium-8 medium-push-4">
				<div class="bg-img article" style="background-image: url(<?php echo $config->get('url').'images/'.$news_db->data['featured_image']; ?>);"></div>
				<p class="blurb"><?php echo $news_db->data['blurb'] ?></p>
				<p><?php echo $news_db->data['content'] ?></p>
				<?php if(!empty($news_db->data['team_members'])): ?>
					<h4>Key Team Members</h4>
					<?php foreach($news_db->data['team_members'] as $tm): ?>
						<a href="/team/<?php echo $tm['slug']; ?>"><span class="thumbnail" style="background-image:url('/assets/images/team/<?php echo $tm['featured_image']; ?>')"></span></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="columns large-3 medium-4 large-pull-9 medium-pull-8 sidebar">
				<h4>Other Articles</h4>
				<hr class="hr-left">
				<?php foreach($news_sidebar AS $i): ?>
					<div class="row">
						<a href="/news/<?php echo $i['slug']; ?>">
							<div class="columns large-12 tb-pad-15">
								<div class="title"><?php echo $i['title']; ?></div>
								<p class="date"><?php echo date("F j, Y", strtotime($i['date'])); ?></p>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<!-- end of News article view content -->

<?php endif; ?>

<?php
$output['content'] = ob_get_contents();
ob_clean();
?>
