<?
class yandex_images_api {
	// $found, $orig_size, $big_sizes, $medium_sizes, $small_sizes, $tags, $similar_thumbs_url, $other
	// $text_res

	public function __construct() {	}

	protected function c($u) {
		$c = curl_init($u);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:73.0) Gecko/20100101 Firefox/73.0','Accept: text/javascript, application/javascript, application/ecmascript, application/x-ecmascript, */*; q=0.01','Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3','X-Requested-With: XMLHttpRequest','DNT: 1','Connection: keep-alive','Referer: https://yandex.ru/images/search?text=qweqwe5','Cookie: yandexuid=7704760081505223352;_ym_uid=1505332256242160375; my=YwA=; yandex_gid=35; _ym_d=1579611313; yandex_login=admin@yandex.ru; mda=0; gdpr=0; _ym_isad=2','Accept-Encoding: deflate'));
		$z = curl_exec($c);
		curl_close($c);
		return $z;
	}

	public function search_url(string $url, int $page=0) {
		if(strlen($url)<7) throw new Exception('short url');
		$b = json_decode($this->c('https://yandex.ru/images/search?format=json&request={"blocks":[{"block":"content_type_search-by-image"}]}&rpt=imageview&url='.urlencode($url).'&p='.$page));
		if(!$b)
			throw new Exception('response error');
		$html = str_replace('&amp;', '&', $b->blocks[0]->html);
		if(strpos($html, 'Картинка не загрузилась')!==false)
			throw new Exception('yandex timeout, try again'); // try again
		$this->found = strpos($html, 'Таких же картинок не найдено')===false;
		preg_match_all('#<div class="original-image__thumb-info">(.+?)</div>#', $html, $orig_size);
		if($orig_size[1])
			$this->orig_size = $orig_size[1][0];
		else
			throw new Exception('wtf');
	
		// big_sizes
		preg_match_all('#<ul class="other-sizes__list other-sizes__list_size_large">(.+?)</ul>#', $html, $big_sizes_html);
		if($big_sizes_html[1]) {
			$big_sizes_html = urldecode($big_sizes_html[1][0]);
			preg_match_all('#<div class="other-sizes__resolution">(.+?)<div#', $big_sizes_html, $big_sizes_x);
			preg_match_all('#</div>(.+?)</div>#', $big_sizes_html, $big_sizes_y);
			preg_match_all('#href="(.+?)"#', $big_sizes_html, $big_sizes_url);
			[$big_sizes['x'], $big_sizes['y'], $big_sizes['url']] = [$big_sizes_x[1], $big_sizes_y[1], $big_sizes_url[1]];
			$this->big_sizes = $big_sizes;
		} else $this->big_sizes = null;
	
		// medium_sizes
		preg_match_all('#<ul class="other-sizes__list other-sizes__list_size_medium">(.+?)</ul>#', $html, $medium_sizes_html);
		if($medium_sizes_html[1]) {
			$medium_sizes_html = urldecode($medium_sizes_html[1][0]);
			preg_match_all('#<div class="other-sizes__resolution">(.+?)<div#', $medium_sizes_html, $medium_sizes_x);
			preg_match_all('#</div>(.+?)</div>#', $medium_sizes_html, $medium_sizes_y);
			preg_match_all('#href="(.+?)"#', $medium_sizes_html, $medium_sizes_url);
			[$medium_sizes['x'], $medium_sizes['y'], $medium_sizes['url']] = [$medium_sizes_x[1], $medium_sizes_y[1], $medium_sizes_url[1]];
			$this->medium_sizes = $medium_sizes;
		} else $this->medium_sizes = null;
	
		// small_sizes
		preg_match_all('#<ul class="other-sizes__list other-sizes__list_size_small">(.+?)</ul>#', $html, $small_sizes_html);
		if($small_sizes_html[1]) {
			$small_sizes_html = urldecode($small_sizes_html[1][0]);
			preg_match_all('#<div class="other-sizes__resolution">(.+?)<div#', $small_sizes_html, $small_sizes_x);
			preg_match_all('#</div>(.+?)</div>#', $small_sizes_html, $small_sizes_y);
			preg_match_all('#href="(.+?)"#', $small_sizes_html, $small_sizes_url);
			[$small_sizes['x'], $small_sizes['y'], $small_sizes['url']] = [$small_sizes_x[1], $small_sizes_y[1], $small_sizes_url[1]];
			$this->small_sizes = $small_sizes;
		} else $this->small_sizes = null;
	
		// tags
		preg_match_all('#<div class="tags__wrapper">(.+?)</div>#', $html, $tags_html);
		if($tags_html[1]) {
			preg_match_all('#>(.+?)</a>#', $tags_html[1][0], $tags);
			$tags = $tags[1];
			$this->tags = $tags;
		} else $this->tags = null;
	
		// similar_thumbs
		preg_match_all('#<ul class="similar__thumbs">(.+?)</ul>#', $html, $similar_thumbs_html);
		if($similar_thumbs_html[1]) {
			$similar_thumbs_html = urldecode($similar_thumbs_html[1][0]);
			preg_match_all('#url=(.+?)&#', $similar_thumbs_html, $similar_thumbs_url);
			$similar_thumbs_url = array_values(array_unique($similar_thumbs_url[1]));
			preg_match_all('#src="(.+?)"#', $similar_thumbs_html, $similar_thumbs_url_others);
			array_push($similar_thumbs_url, ...$similar_thumbs_url_others[1]);
			$this->similar_thumbs_url = $similar_thumbs_url;
		} else $this->similar_thumbs_url = null;
	
		// other
		preg_match_all('#<ul class="other-sites__container">(.+?)</ul>#', $html, $other_html);
		if($other_html[1]) {
			$other_html = $other_html[1][0];
			preg_match_all('#<a class="other-sites__preview-link" href="(.+?)"#', $other_html, $other_img_url);
			preg_match_all('#<img class="other-sites__thumb" src="(.+?)"#', $other_html, $other_thumb_url);
			preg_match_all('#<div class="other-sites__meta">(.+?)</div>#', $other_html, $other_sizes);
			preg_match_all('#<a class="link link_theme_normal other-sites__title-link i-bem" data-bem=\'{"link":{}}\' rel="noopener" target="_blank" tabindex="0" href="(.+?)">(.+?)</a>#', $other_html, $other_site_redirurl_title);
			preg_match_all('#<a class="link link_theme_outer other-sites__outer-link i-bem" data-bem=\'{"link":{}}\' rel="noopener" target="_blank" tabindex="0" href="(.+?)">(.+?)</a>#', $other_html, $other_site_redirurl_site_name);
			preg_match_all('#<div class="other-sites__desc">(.+?)</div>#', $other_html, $other_desc);
			[$other['img_url'],
			$other['thumb_url'],
			$other['sizes'],
			$other['site_redirurl'],
			$other['title'],
			$other['site_name'],
			$other['desc']] = [$other_img_url[1], $other_thumb_url[1], $other_sizes[1], $other_site_redirurl_title[1], $other_site_redirurl_title[2], $other_site_redirurl_site_name[2], $other_desc[1]];
			$this->other = $other;
		} else $this->other = null;
	}

	public function search_text(string $text, int $page=0) {
		$b = json_decode($this->c('https://yandex.ru/images/search?format=json&request={"blocks":[{"block":"content_type_search"}]}&text='.urlencode($text).'&p='.$page));
		if(!$b)
			throw new Exception('response error');
		$html = str_replace('&amp;', '&', $b->blocks[0]->html);
		preg_match_all('#justifier__item i-bem" data-bem=\'(.+?)\'#', $html, $blocks);

		for($i=0; $i<count($blocks[1]); $i++) {
			$q[] = json_decode($blocks[1][$i])->{'serp-item'};
			$this->text_res = $q;
		}
	}
}