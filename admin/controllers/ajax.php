<?php
$response = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if($action == 'page_slug'){
	$slug_invalid = true;
	$tag = (isset($_POST['title']) && !empty($_POST['title'])) ? url_me($_POST['title']) : $_POST['slug'] ;
	$orig_tag = $tag;
	$tag_count = 0;
	while ($slug_invalid) {
		$pages_db = new Model('pages', 'slug');
		$pages_db->load($tag);
		if(!empty($pages_db->data)) {
			if($_POST['id'] != 0 && $pages_db->data['id'] == $_POST['id']) {
				$slug_invalid = false;
			} else {
				$tag_count++;
				$tag = $orig_tag.'-'.$tag_count;
			}
		} else {
			$slug_invalid = false;
		}
	}
	$response = $tag;
}
if($action == 'news_slug'){
	$slug_invalid = true;
	$tag = (isset($_POST['title']) && !empty($_POST['title'])) ? url_me($_POST['title']) : $_POST['slug'] ;
	$orig_tag = $tag;
	$tag_count = 0;
	while ($slug_invalid) {
		$news_db = new Model('news', 'slug');
		$news_db->load($tag);
		if(!empty($news_db->data)) {
			if($_POST['id'] != 0 && $news_db->data['id'] == $_POST['id']) {
				$slug_invalid = false;
			} else {
				$tag_count++;
				$tag = $orig_tag.'-'.$tag_count;
			}
		} else {
			$slug_invalid = false;
		}
	}
	$response = $tag;
}

if($action == 'delImage'){
    $temp_model = new Model($_POST['table']);
    if(!empty($_POST['id']) && ctype_digit($_POST['id'])) {
        $temp_model->load($_POST['id']);
        $response = $temp_model->update(array($_POST['field'] => ''));
        unlink($_POST['file']);
    }
}

echo $response;
exit();
?>
