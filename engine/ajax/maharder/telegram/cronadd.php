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
define( 'ROOT_DIR', dirname( dirname( dirname( dirname( dirname(  __FILE__ ) ) ) ) ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

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

if($telebot['onof'] && $telebot['cron']) {
    require_once (DLEPlugins::Check(ENGINE_DIR . "/inc/maharder/telegram/functions.php"));

    if($telebot['cron_news'] > 0) $limit = "LIMIT " . $telebot['cron_news'];
    else $limit = "";
    $cron = $db->query("SELECT * FROM " . PREFIX . "_telegram_cron {$limit}" );
    if(count($cron) <= 0) return;
    else {
        while ($row = $db->get_row($cron)) {
            $news_id = intval($row['news_id']);
            $news = $db->super_query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON " . PREFIX . "_post.id = " . PREFIX . "_post_extras.news_id  WHERE id = '{$news_id}'");
            $news_time = $row['time'];
            if(!isset($telebot['cron_time'])) $telebot['cron_time'] = 0;
            $cron_time = $telebot['cron_time'];
            $cron_time = $cron_time * 60;
            $news_time = $news_time+$cron_time;
            $now_time = time();

            $xfields = explode('||', $news['xfields']);
            $xf = array();
            foreach ($xfields as $key => $value) {
                $sfields = explode('|', $value);
                $xf[$sfields[0]] = $sfields[1];
            }
            if($now_time >= $news_time) {
                if($row['type'] == "add")
                    $source = $telebot['cron_addnews'] ? $source : $telebot['addnews'];
                elseif ($row['type'] == "edit")
                    $source = $telebot['cron_editnews'] ? $source : $telebot['editnews'];

                if ($config['allow_alt_url']) {
                    if ($config['seo_type'] == 1 OR $config['seo_type'] == 2) {
                        if (intval($news['category']) and $config['seo_type'] == 2) {
                            $full_link = $config['http_home_url'] . get_url(intval($news['category'])) . "/" . $news_id . "-" . $news['alt_name'] . ".html";
                        } else {
                            $full_link = $config['http_home_url'] . $news_id . "-" . $news['alt_name'] . ".html";
                        }
                    } else {
                        $full_link = $config['http_home_url'] . date('Y/m/d/', strtotime($news['date'])) . $news['alt_name'] . ".html";
                    }
                } else {
                    $full_link = $config['http_home_url'] . "index.php?newsid=" . $news_id;
                }

                $temes = htmlspecialchars_decode($source);
                $temes = str_replace('%title%', $news['title'], $temes);
                $temes = str_replace('%link%', $full_link, $temes);
                $temes = str_replace('%descr%', "%short_descr%", $temes);
                $temes = str_replace('%short_descr%', $news['short_story'], $temes);
                $temes = str_replace('%full_descr%', $news['full_story'], $temes);
                $temes = str_replace('%autor%', $news['autor'], $temes);
                $temes = str_replace('[b]', '<b>', $temes);
                $temes = str_replace('[/b]', '</b>', $temes);
                $temes = str_replace('[i]', '<i>', $temes);
                $temes = str_replace('[/i]', '</i>', $temes);
                $temes = str_replace('[code]', '<code>', $temes);
                $temes = str_replace('[/code]', '</code>', $temes);
                if ($row['type'] == "add") $temes = str_replace('%editreason%', $news['reason'], $temes);
                $temes = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $temes);
                $temes = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $temes);
                preg_match_all("/%xf_(.*)%/", $temes, $tempFields);
                foreach ($tempFields[1] as $id => $value) {
                    $temes = str_replace('%xf_' . $value . '%', $xf[$value], $temes);
                }
                $temes = str_replace('<br>', "\r\n", $temes);

                $turl = "https://api.telegram.org/bot" . $telebot['token'] . "/sendMessage?chat_id=" . $telebot['chat'] . "&text=" . urlencode($temes) . "&parse_mode=HTML";
                sendMessage($turl);
                $cron_id = intval($row['cron_id']);
                $db->query("DELETE FROM " . PREFIX . "_telegram_cron WHERE cron_id = {$cron_id}");
            } else continue;
        }
    }

} else return;