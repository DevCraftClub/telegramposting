<?php

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );

require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/classes/telegram.class.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/_includes/classes/Ajax.php'));

$mh_data = new Ajax();
$tg_config = $mh_data->getConfig('telegram', ENGINE_DIR . '/inc/maharder/_config', 'telebot');

function sendTelegram($id, $type = 'addnews') {
	global $tg_config;

	$news_id = (int) $id;
	if(!$news_id) return;

	if(!in_array($type, ['addnews', 'editnews', 'cron_addnews', 'cron_editnews'])) $type = 'addnews';

	if($tg_config['onof']) {

		if($tg_config['cron']) {
			$cron = new Cron();
			$cron->create([
				              'news_id' => $news_id,
				              'type' => $type
			              ]);
		} else {
			$telegram = new Telegram($news_id, 'addnews');
			$telegram->sendMessage();
		}
	}
}