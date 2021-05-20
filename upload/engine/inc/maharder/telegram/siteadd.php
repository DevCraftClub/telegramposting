<?php

//	===============================
//	Скрипт отправки при добавлении новости с сайта
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );
global $db, $row;
$codename = "telegram";

if(!file_exists((DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/assets/functions.php')))) {
	die("Неустановлен модуль MaHarder Assets. Последняя версия: <a href=\"https://github.com/Gokujo/myAssetsDLE\">https://github.com/Gokujo/myAssetsDLE</a>");
}

$news_id = (int)$row['id'];
if(!$news_id) return;

@include (DLEPlugins::Check(ENGINE_DIR . '/data/'.$codename.'.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/assets/telegram.class.php'));

if($telebot['onof']) {

	if($telebot['cron']) $db->query("INSERT INTO " . PREFIX . "_telegram_cron (news_id, type, time) VALUES ('{$news_id}', 'addnews')");
	else {
		$telegram = new Telegram($news_id, $telebot, 'addnews');
		$telegram->sendMessage();

    }
}
