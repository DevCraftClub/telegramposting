<?php
//	===============================
//	Скрипт отправки при добавлении новости
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );
global $db, $tg_post_id, $tg_post_type;
$codename = "telegram";

$news_id = (int) $tg_post_id;
if(!$news_id) return;
if (!in_array($tg_post_type, ['addnews', 'editnews'])) return;

@include (DLEPlugins::Check(ENGINE_DIR . '/data/'.$codename.'.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/assets/telegram.class.php'));

if($telebot['onof']) {

	if($telebot['cron']) $db->query("INSERT INTO " . PREFIX . "_telegram_cron (news_id, type) VALUES ('{$news_id}', '{$tg_post_type}')");
	else {
		$telegram = new Telegram($news_id, $telebot, $tg_post_type);
		$telegram->sendMessage();
	}
}