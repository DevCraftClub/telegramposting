<?php

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );


$mh_data = new MhAjax();
$tg_config = DataManager::getConfig('telegram');

function sendTelegram($id, $type = 'addnews') {
	global $tg_config, $MHDB;

	$news_id = (int) $id;
	if(!$news_id) return;

	if(!in_array($type, ['addnews', 'editnews', 'cron_addnews', 'cron_editnews'])) $type = 'addnews';

	if($tg_config['onof']) {

		if($tg_config['cron']) {
			$cron = new TgCron();

			$cron->news_id = $news_id;
			$cron->type = $type;
			$cron->time = (new DateTimeImmutable())->add(new DateInterval("P{$tg_config['cron_time']}M"));

			$MHDB->create($cron);
		} else {
			$telegram = new Telegram($news_id, $type);
			$telegram->sendMessage();
		}
	}
}