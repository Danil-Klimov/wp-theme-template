<?php
$search_systems = ['google', 'yandex', 'mail.ru', 'rambler', 'bing'];
$important_source = false;
/** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
$source_array = [
	'medium' => null,
	'source' => null,
	'campaign' => null,
	'term' => null,
	'content' => null
];
if (isset($_GET['utm_medium'])) {
	$important_source = true;
	$source_array['medium'] = $_GET['utm_medium'];
	$source_array['source'] = isset($_GET['utm_source']) ? $_GET['utm_source'] : null;
	$source_array['campaign'] = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : null;
	$source_array['term'] = isset($_GET['utm_term']) ? $_GET['utm_term'] : null;
	$source_array['content'] = isset($_GET['utm_content']) ? $_GET['utm_content'] : null;
} else if (isset($_GET['yclid'])) {
	$source_array['medium'] = 'cpc';
	$source_array['source'] = 'yandex';
} else if (isset($_GET['gclid'])) {
	$source_array['medium'] = 'cpc';
	$source_array['source'] = 'google';
} else if (!empty($_SERVER['HTTP_REFERER'])) {
	preg_match('/(?:https?:\/\/)?(.*?)(?:\/|$)/', $_SERVER['HTTP_REFERER'], $referrer);
	$referrer = preg_match('/\.(.*\.\w*)/', $referrer[1], $new_referrer) ? $new_referrer[1] : $referrer[1];
	preg_match('/(?:https?:\/\/)?(.*?)(?:\/|$)/', $_SERVER['HTTP_HOST'], $host);
	$host = preg_match('/\.(.*\.\w*)/', $host[1], $new_host) ? $new_host[1] : $host[1];
	if ($host != $referrer) {
		$important_source = true;
		$source_array['source'] = $referrer;
		//Если реферал содержит маркет, проставляем канал контекст
		if (strpos($referrer, 'market.yandex') > -1) {
			$source_array['medium'] = 'cpc';
		} else {
			//Если реферал - не поисковая система проставляем канал рефералы
			$source_array['medium'] = 'referral';
			foreach ($search_systems as $search_system) {
				if (strpos($referrer, $search_system) > -1) {
					$organic = true;
					$source_array['medium'] = 'organic';
					$source_array['source'] = $search_system;
					break;
				}
			}
		}
	} else {
		$source_array['source'] = 'none';
		$source_array['medium'] = 'direct';
	}
} else {
	$source_array['source'] = 'none';
	$source_array['medium'] = 'direct';
}

if (!$important_source && isset($_COOKIE['source_cookie'])) {
	$source_cookie = unserialize(stripslashes($_COOKIE['source_cookie']));
	if ($source_cookie && !empty($source_cookie['medium'])) {
		$source_array = $source_cookie;
	}
}

setcookie("source_cookie", serialize($source_array), time() + 3600 * 24 * 30, "/");
$html = "Источник - $source_array[source], Канал - $source_array[medium]";
$html .= $source_array['campaign'] ? ", Кампания - $source_array[campaign]" : null;
$html .= $source_array['term'] ? ", Объявление - $source_array[term]" : null;
$html .= $source_array['content'] ? ", Ключевое слово - $source_array[content]" : null;
setcookie("traffic_source", $html, time() + 3600 * 24 * 30, "/");
$_SESSION['traffic_source'] = $html;

//Страница входа и посещенные страницы каталога
$cat_regex = '/uslugi\/.*/';
$visited_pages = $_COOKIE['visited_pages'];
$current_page = preg_replace('/\?.*/', '', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
if (!$visited_pages) {
	$visited_pages = [
		'categories' => [],
		'current' => $current_page,
		'lp' => $current_page
	];
} else {
	$visited_pages = json_decode($visited_pages, 1);
}

$visited_pages['current'] = $current_page;
if (!isset($visited_pages['lp'])) {
	$visited_pages['lp'] = $current_page;
}

preg_match($cat_regex, $current_page, $category_match);
if ($category_match && !in_array($category_match[0], $visited_pages['categories'])) {
	$visited_pages['categories'][] = $category_match[0];
}
setcookie("visited_pages", json_encode($visited_pages), time() + 3600 * 24 * 30, "/");
setcookie("landing_page", $visited_pages['lp'], time() + 3600 * 24 * 30, "/");
