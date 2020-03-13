<?
include('yandex_images.php');

try {
	$images_by_text = new yandex_images_api;
	$images_by_text->search_text($text='кот', $page=0);
	if(get_object_vars($images_by_text))
		printf('<img src="%s" height=100 alt>'.PHP_EOL, $images_by_text->text_res[0]->thumb->url);
} catch (Exception $e) {echo $e->getMessage();}

try {
	$images_by_url = new yandex_images_api;
	$images_by_url->search_url($url='https://www.selfgrowth.info/photos/free-cats-photos-HD/best-cats-pictures-without-background5538.jpg', $page=0);
	if($images_by_url->found)
		printf('<img src="%s" alt="%s" height=100>', 	$images_by_url->similar_thumbs_url[0],
														implode(', ',$images_by_url->tags));
} catch (Exception $e) {echo $e->getMessage();}
