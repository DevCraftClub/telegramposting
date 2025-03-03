<?php
//	===============================
//	Добавление новостей по крону
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://devcraft.club
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================
global $MHDB;
@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include(DLEPlugins::Check(ENGINE_DIR . '/data/config.php'));
date_default_timezone_set($config['date_adjust']);

if($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/maharder/telegram/cronadd.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once(DLEPlugins::Check(ENGINE_DIR . '/classes/mysql.php'));
require_once(DLEPlugins::Check(ENGINE_DIR . '/data/dbconfig.php'));
require_once(DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/_models/Cron.php'));
include_once(DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/helpers/sender.php'));

if($tg_config['onof'] && $tg_config['cron']) {

	if(empty($tg_config['cron_news'])) $tg_config['cron_news'] = 0;
	if(empty($tg_config['cron_waittime']) || !(int)$tg_config['cron_waittime']) $tg_config['cron_waittime'] = 5;

	if($MHDB->count(TgCron::class) <= 0) return;

	$cron_all = $MHDB->repository(TgCron::class)->findAll();

	$round = 0;
	foreach($cron_all as $c) {
		$news_time = (new DateTime())::createFromFormat('Y-m-d H:i:s', $c['time']->format('Y-m-d H:i:s'))->getTimestamp();
		if(empty($tg_config['cron_time'])) $tg_config['cron_time'] = 0;
		$cron_time = $tg_config['cron_time'] * 60;
		$news_time += $cron_time;
		$now_time = time();

		if($now_time >= $news_time) {
			if(in_array($c['type'], ['addnews', 'editnews'])) $c['type'] = "cron_{$c['type']}";
			if(!in_array($c['type'], ['addnews', 'editnews', 'cron_addnews', 'cron_editnews'])) $c['type'] = "addnews";
			$telegram = new Telegram($c['news_id'], $c['type']);
			$message = json_decode($telegram->sendMessage(), true);

			if($message['ok']) {
				$MHDB->delete(TgCron::class, $c['id']);

				$round++;
				if($round >= $tg_config['cron_news']) break;
			} else {
				if($tg_config['cron_autodelete']) $MHDB->delete(TgCron::class, $c['id']);

			}

			CacheControl::clearCache(TgCron::class);

			sleep($tg_config['cron_waittime']);
		}
	}


}
return;