<?php
$news_categories_db = new Model('news_categories');
$news_categories = $news_categories_db->get_items(array('order' => 'sort'));
?>

<ul class="news-cats">
	<?php foreach($news_categories AS $nc): ?>
		<a href="/<?php echo $nc['page_callname']; ?>" class="<?php echo ($url_segments[0] == $nc['page_callname']) ? 'active-tab ' : 'inactive-tab ' ; ?><?php echo $nc['page_callname']; ?>"><li><?php echo $nc['page_name']; ?></li></a>
		<?php endforeach; ?>
</ul>
