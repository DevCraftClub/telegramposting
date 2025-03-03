<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: /engine/ajax/maharder/telegram/master.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

global $MHDB;
if(!defined('DATALIFEENGINE')) {
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../../../../');
	exit('Hacking attempt!');
}

global $is_logged, $dle_login_hash, $config, $mh_admin;

if(!$is_logged) {
	exit('error');
}

if('' == $_REQUEST['user_hash'] or $_REQUEST['user_hash'] != $dle_login_hash) {
	exit('error');
}

$method = $_POST['method'];
if(!$method) {
	exit();
}

if(!is_array($_POST['data'])) parse_str($_POST['data'], $_data); else $_data = $_POST['data'];
foreach($_data as $id => $d) {
	$_data[$id] = htmlspecialchars($d);
}
$_data = filter_var_array($_data);

if(!function_exists('sendMessage')) {
	function sendMessage($url) {
		global $mh_admin;

		$telebot = DataManager::getConfig('telegram', ENGINE_DIR . '/inc/maharder/_config', 'telebot');

		if($telebot['proxy']) $proxy = $telebot['proxyip'] . ':' . $telebot['proxyport'];
		if($telebot['proxytype'] == "socks") $proxy = "socks5://" . $proxy;
		if($telebot['proxyauth']) $proxyauth = $telebot['proxyuser'] . ':' . $telebot['proxypass'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if($telebot['proxytype'] == "socks") curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		if($telebot['proxy']) curl_setopt($ch, CURLOPT_PROXY, $proxy);
		if($telebot['proxyauth']) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}
}


switch($method) {
	case 'send_cron_data':

		include_once DLEPlugins::Check(MH_MODULES . '/classes/telegram.class.php');

		$telegram = new Telegram($_data['news_id'], "cron_{$_data['type']}");
		$message = json_decode($telegram->sendMessage(), true);

		if($message['ok']) {
			try {
				$MHDB->delete(TgCron::class, $_data['cron_id']);
				echo (new SuccessResponseAjax(203))->setData(__('Запись удалена!'))->send();
			} catch (Exception|Throwable $e) {
				echo (new ErrorResponseAjax())->setData($e->getMessage())->send();
			}
		} else {
			echo json_encode(['success' => false, 'message' => $message['message'] ?? $message['description']]);
		}

		CacheControl::clearCache(TgCron::class);

		break;

	case 'delete_cron_data':

		try {
			$MHDB->delete(TgCron::class, $_data['cron_id']);
			echo (new SuccessResponseAjax(203))->setData(__('Запись удалена!'))->send();
		} catch (Exception|Throwable $e) {
			echo (new ErrorResponseAjax())->setData($e->getMessage())->send();
		}

		$mh_admin->clear_cache();

		break;

	case 'save_cron_data':

		try {
			$cron = $MHDB->get(TgCron::class, $_data['cron_id']);
			$cron->news_id = $_data['news_id'];
			$cron->time = new DateTimeImmutable($_data['time']);
			$cron->type = $_data['type'];

			$MHDB->update($cron);
			echo (new SuccessResponseAjax(201))->setData(__('Запись успешно сохранена!'))->setMeta((array) $cron)->send();
		} catch (Exception|Throwable $e) {
			echo (new ErrorResponseAjax())->setData($e->getMessage())->send();

		}

		CacheControl::clearCache(TgCron::class);

		break;

	case 'cron_new_entry':

		try {
			$cron          = new TgCron();
			$cron->news_id = $_data['news_id'];
			$cron->time    = new DateTimeImmutable($_data['time']);
			$cron->type    = $_data['type'];

			$MHDB->create($cron);

			echo (new SuccessResponseAjax())->setData(__('Запись успешно создана!'))->setMeta((array) $cron)->send();
		} catch (Exception|Throwable $e) {
			echo (new ErrorResponseAjax())->setData($e->getMessage())->send();
		}
		break;

	case 'get_news':

		try {
			echo json_encode([
				                 'success' => true, 'news' => $mh_admin->load_data('Post', [
					'table' => 'post'
				])
			                 ], JSON_UNESCAPED_UNICODE);
		} catch(JsonException $e) {
			echo json_encode([
				                 'success' => false, 'news' => []
			                 ]);
		}

		break;

	case 'get_chat_id':
		$answer = sendMessage("https://api.telegram.org/bot" . $_data['bot'] . "/getUpdates");
		$answer = json_decode($answer, true);

		echo json_encode($answer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		break;

	case 'send_message':

		$date_now = (new \DateTime())->format('Y-m-d H:i:s');

		$message = <<<HTML
<b>Тестовое сообщение</b> [by Telegram Posting]
Отправлено с сайта: <b>{$config['http_home_url']}</b>
<b>Дата отправления</b>: {$date_now}
HTML;
		$message = str_replace(['<br>', '<br />', '<br/>'], PHP_EOL, $message);
		$turl = "https://api.telegram.org/bot" . $_data['bot'] . "/sendMessage?chat_id=" . $_data['chat'] . "&text="
		        . urlencode($message) . "&parse_mode=HTML";

		$antwort = json_decode(trim(sendMessage($turl)), true);

		echo json_encode($antwort, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		break;

	case 'settings':

		$file = MH_CONFIG . '/' . $_POST['module'] . '.json';


		if(empty($_data['list_count']) || !isset($_data['list_count'])) {
			$_data['list_count'] = $config['news_number'];
		}
		
		if(empty($_data['tag_separator']) || !isset($_data['tag_separator'])) {
			$_data['tag_separator'] = $config['tags_separator'];
		}

		if(empty($_data['hashtag_separator']) || !isset($_data['hashtag_separator'])) {
			$_data['hashtag_separator'] = $config['tags_separator'];
		}
		
		if(empty($_data['category_separator']) || !isset($_data['category_separator'])) {
			$_data['category_separator'] = $config['category_separator'];
		}

		if(isset($_data['logs_telegram_type'])) {
			$temp_type = explode(',', $_data['logs_telegram_type']);
			foreach($temp_type as $type) {
				if($type === 'all') {
					$_data['logs_telegram_type'] = $type;
					break;
				}
			}
		}

		if(!isset($_data['logs_telegram_type']) || !isset($_data["logs_telegram_api"])
		   || !isset($_data["logs_telegram_channel"])) unset($_data["logs_telegram"]);


			$_data = json_encode($_data, JSON_UNESCAPED_UNICODE);
		file_put_contents($file, $_data);
		clear_cache();

		echo 'ok';

		break;


}
