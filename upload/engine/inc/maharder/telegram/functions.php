<?php
//	===============================
//	Скрипт функций модуля
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if(!function_exists('sendMessage')) {
    function sendMessage ($url) {
        global $telebot;
        if($telebot['proxytype'] == "socks") $telebot['method'] = 2;
        if($telebot['method'] == 1) {
            if($telebot['proxy']) {
                if($telebot['proxyauth']) {
                    $auth = base64_encode($telebot['proxyuser'] . ':' . $telebot['proxypass']);
                    $opts = array(
                        'http' => array (
                            'method'=>'GET',
                            'proxy'=>'tcp://' . $telebot['proxyip'] . ':' . $telebot['proxyport'],
                            'request_fulluri' => true,
                            'header'=> array("Proxy-Authorization: Basic $auth", "Authorization: Basic $auth")

                        ),
                        'https' => array (
                            'method'=>'GET',
                            'proxy'=>'tcp://' . $telebot['proxyip'] . ':' . $telebot['proxyport'],
                            'request_fulluri' => true,
                            'header'=> array("Proxy-Authorization: Basic $auth", "Authorization: Basic $auth")
                        )
                    );
                } else {
                    $opts = array(
                        'http' => array (
                            'method'=>'GET',
                            'proxy'=>'tcp://' . $telebot['proxyip'] . ':' . $telebot['proxyport'],
                            'request_fulluri' => true

                        ),
                        'https' => array (
                            'method'=>'GET',
                            'proxy'=>'tcp://' . $telebot['proxyip'] . ':' . $telebot['proxyport'],
                            'request_fulluri' => true
                        )
                    );
                }
                $context = stream_context_create($opts);
                $content = file_get_contents($url, false, $context);
            } else {
                $content = file_get_contents($url);
            }
        } elseif ($telebot['method'] == 2) {
            if($telebot['proxy']) $proxy = $telebot['proxyip'] . ':' . $telebot['proxyport'];
            if($telebot['proxytype'] == "socks") $proxy = "socks5://" . $proxy;
            if($telebot['proxyauth']) $proxyauth = $telebot['proxyuser'] . ':' . $telebot['proxypass'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if($telebot['proxytype'] == "socks") curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            if($telebot['proxy']) curl_setopt($ch, CURLOPT_PROXY, $proxy);
            if($telebot['proxyauth']) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            $content = curl_exec($ch);
            curl_close($ch);
        }

        return $content;
    }
}

if(!function_exists('load_categories')) {
    function load_categories () {
        global $db;

        $cat_name = array();
        $cats = $db->query("SELECT * FROM " . PREFIX . "_category");
        foreach ($cats as $cat) {
			$cat_name[] = [
				'id'=> $cat['id'],
				'name' => $cat['name'],
				'alt' => $cat['alt_name']
			];
        }

        return $cat_name;

    }
}
