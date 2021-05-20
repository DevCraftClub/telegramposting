<?php
//	===============================
//	Добавление новостей по крону
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', dirname( dirname( dirname( dirname(__DIR__) ) ) ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

require_once (ENGINE_DIR . '/classes/plugins.class.php');
include_once (DLEPlugins::Check(ENGINE_DIR . '/inc/include/functions.inc.php'));
include (DLEPlugins::Check(ENGINE_DIR . "/data/telegram.php"));
include (DLEPlugins::Check(ENGINE_DIR . '/data/config.php'));
date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
    $config['http_home_url'] = explode( "engine/ajax/maharder/telegram/cronadd.php", $_SERVER['PHP_SELF'] );
    $config['http_home_url'] = reset( $config['http_home_url'] );
    $config['http_home_url'] = "https://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];
}

require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/mysql.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/data/dbconfig.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . "/inc/maharder/telegram/assets/telegram.class.php"));

if($telebot['onof'] && $telebot['cron']) {

    if (!isset($telebot['cron_news'] ) || empty($telebot['cron_news'] )) $telebot['cron_news'] = 0;
    if($telebot['cron_news'] > 0) $limit = "LIMIT " . $telebot['cron_news'];
    else $limit = "";

    $cron = $db->query("SELECT * FROM " . PREFIX . "_telegram_cron {$limit}" );
    if(count($cron) <= 0) return;
    else {
        while ($row = $db->get_row($cron)) {
            $news_id = (int) $row['news_id'];
            $news_time = strtotime(stripslashes($row['time']));
			$telebot['cron_time'] = (isset($telebot['cron_time']) || !empty($telebot['cron_time'])) ? $telebot['cron_time'] : 0;
            $cron_time = $telebot['cron_time'];
            $cron_time = $cron_time * 60 * 60;
            $news_time = $news_time+$cron_time;
            $now_time = time();

            if($now_time >= $news_time) {

				$type = $row['type'] === 'addnews' ? 'cron_addnews' : 'cron_editnews';

				$telegram = new Telegram($news_id, $telebot, $type);
				$telegram->sendMessage();

                $cron_id = (int)$row['cron_id'];
                $db->query("DELETE FROM " . PREFIX . "_telegram_cron WHERE cron_id = {$cron_id}");
            }
            sleep(5);
		}
    }

} else return;