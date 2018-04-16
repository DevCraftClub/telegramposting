<?php

//	===============================
//	Скрипт отправки при редактировании новости
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );
global $db;
$codename = "telegram";

$id = intval($_GET['id']);
if(!$id) return;

@include (ENGINE_DIR . '/data/'.$codename.'.php');
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');

$request = explode(':', $telebot['field']);
if($request[0] == 'post') {
	$field = explode('|', $request[1]);
	$post = $db->super_query("SELECT * FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$field[0]} = '{$field[1]}' AND id = '{$id}'");
} elseif($request[0] == 'xf') {
	$field = explode('|', $request[1]);
	$post = $db->super_query("SELECT * FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE xfields LIKE '%{$field[0]}|{$field[1]}%' AND id = '{$id}'");
}
if(count($post) > 0) {
	if( $config['allow_alt_url'] ) {
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
			if( intval( $post['category'] ) and $config['seo_type'] == 2 ) {
				$full_link = $config['http_home_url'] . get_url( intval( $post['category'] ) ) . "/" . $id . "-" . $post['alt_name'] . ".html";
			} else {
				$full_link = $config['http_home_url'] . $id . "-" . $post['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $post['date'] ) ) . $post['alt_name'] . ".html";
		}
	} else {
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $id;
	}
	$temes = htmlspecialchars_decode($telebot['editnews']);
	$temes = str_replace('%title%', $post['title'], $temes);
	$temes = str_replace('%link%', $full_link, $temes);
	$temes = str_replace('%descr%', $post['short_story'], $temes);
	$temes = str_replace('%autor%', $post['autor'], $temes);
	$temes = str_replace('%editreason%', $post['reason'], $temes);
	$temes = str_replace('[b]', '<b>', $temes);
	$temes = str_replace('[/b]', '</b>', $temes);
	$temes = str_replace('[i]', '<i>', $temes);
	$temes = str_replace('[/i]', '</i>', $temes);
	$temes = str_replace('[code]', '<code>', $temes);
	$temes = str_replace('[/code]', '</code>', $temes);
	$temes = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $temes);
	$temes = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $temes);
	preg_match_all("/%xf_(.*)%/", $temes, $tempFields);
	$xfields = explode( '||', $post['xfields']);
	$xf = array();
	foreach ($xfields as $key => $value) {
		$sfields = explode( '|', $value);
		$xf[$sfields[0]] = $sfields[1];
	}
	foreach ($tempFields[1] as $id => $value) {
		$temes = str_replace('%xf_'. $value .'%', $xf[$value], $temes);
	}
	$temes = str_replace('<br>', "\r\n", $temes);
	$turl = "https://api.telegram.org/bot". $telebot['token'] ."/sendMessage?chat_id=". $telebot['chat'] ."&text=" . urlencode ( $temes ) . "&parse_mode=HTML";
	file_get_contents($turl);
}
