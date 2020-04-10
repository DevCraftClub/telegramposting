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
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $content = curl_exec($ch);
            curl_close($ch);
        }

        return $content;
    }
}

if(!function_exists('getCategories')) {
    function getCategories ($news_id, $link = false) {
        global $db, $config, $PHP_SELF;

        $cat_name = array();
        $cats = $db->super_query("SELECT category FROM " . PREFIX . "_post WHERE id = '{$news_id}'");
        $cat = explode(',', $cats['category']);
        foreach ($cat as $category) {
            $temp_cat = $db->super_query("SELECT * FROM " . PREFIX . "_category WHERE id = '{$category}'");
            if($link) {
                if( $config['allow_alt_url'] ) {
                    $pid = $temp_cat['parentid'];
                    $url = "&lt;a href=\"" . $config['http_home_url'];
                    $parent_list = array();
                    if(isset($pid) && $pid != 0) {
                        while($pid != 0){
                            $par_id = $db->super_query("SELECT * FROM " . PREFIX . "_category WHERE id = '{$pid}'");
                            $parent_list[] = $par_id['alt_name'];
                            $pid = $par_id['parentid'];
                        }
                    }
                    rsort($parent_list);
                    $parent_list[] = $temp_cat['alt_name'];
                    $url .= implode('/', $parent_list) . "/\" &gt;{$temp_cat['name']}&lt;/a&gt;";
                    $cat_name[] = $url;
                } else {
                    $cat_name[] = "&lt;a href=\"{$PHP_SELF}?do=cat&amp;category={$temp_cat['alt_name']}\"&gt;{$temp_cat['name']}&lt;/a&gt;";
                }
            } else $cat_name[] = $temp_cat['name'];
        }

        return implode($config['category_separator'] ." ", $cat_name);

    }
}