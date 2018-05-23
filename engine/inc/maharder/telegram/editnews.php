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
global $db, $item_db;
$codename = "telegram";

$id = intval($item_db[0]);
if(!$id) return;

@include (ENGINE_DIR . '/data/'.$codename.'.php');
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/functions.php');

if($telebot['onof']) {
    $xfields = explode('||', $filecontents);
    $xf = array();
    foreach ($xfields as $key => $value) {
        $sfields = explode('|', $value);
        $xf[$sfields[0]] = $sfields[1];
    }
    $request = explode(':', $telebot['field']);
    $field = explode('|', $request[1]);

    if($request[0] == "xf") {
        if ($xf[$field[0]] != $field[1]) return;
    } elseif ($request[0] == "post") {
        $quest = $db->super_query("SELECT {$field[0]} FROM " . PREFIX . "_post WHERE id = '{$id}'");
        if($quest[$field[0]] != $field[1]) return;
    }

    if ($config['allow_alt_url']) {
        if ($config['seo_type'] == 1 OR $config['seo_type'] == 2) {
            if (intval($category_list) and $config['seo_type'] == 2) {
                $full_link = $config['http_home_url'] . get_url(intval($category_list)) . "/" . $id . "-" . $alt_name . ".html";
            } else {
                $full_link = $config['http_home_url'] . $id . "-" . $alt_name . ".html";
            }
        } else {
            $full_link = $config['http_home_url'] . date('Y/m/d/', strtotime($thistime)) . $alt_name . ".html";
        }
    } else {
            $full_link = $config['http_home_url'] . "index.php?newsid=" . $id;
    }
    $temes = htmlspecialchars_decode($telebot['editnews']);
    $temes = str_replace('%title%', $title, $temes);
    $temes = str_replace('%link%', $full_link, $temes);
    $temes = str_replace('%descr%', "%short_descr%", $temes);
    $temes = str_replace('%short_descr%', $short_story, $temes);
    $temes = str_replace('%full_descr%', $full_story, $temes);
    $temes = str_replace('%autor%', $item_db[1], $temes);
    $temes = str_replace('%editreason%', $editreason, $temes);
    $temes = str_replace('[b]', '<b>', $temes);
    $temes = str_replace('[/b]', '</b>', $temes);
    $temes = str_replace('[i]', '<i>', $temes);
    $temes = str_replace('[/i]', '</i>', $temes);
    $temes = str_replace('[code]', '<code>', $temes);
    $temes = str_replace('[/code]', '</code>', $temes);
    $temes = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $temes);
    $temes = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $temes);
    preg_match_all("/%xf_(.*)%/", $temes, $tempFields);

    foreach ($tempFields[1] as $id => $value) {
        $temes = str_replace('%xf_' . $value . '%', $xf[$value], $temes);
    }
    $temes = str_replace('<br>', "\r\n", $temes);
    $turl = "https://api.telegram.org/bot" . $telebot['token'] . "/sendMessage?chat_id=" . $telebot['chat'] . "&text=" . urlencode($temes) . "&parse_mode=HTML";
    sendMessage($turl);
}