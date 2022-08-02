<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: D:\OpenServer\domains\dle150.local/engine/ajax/maharder/telegram/master.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

if(!defined('DATALIFEENGINE')) {
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../../../../');
	exit('Hacking attempt!');
}

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

		$telebot = $mh_admin->getConfig('telegram', ENGINE_DIR . '/inc/maharder/_config', 'telebot');

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

include_once DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/models/Cron.php');

switch($method) {
	case 'send_cron_data':

		include_once DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/classes/telegram.class.php');

		$telegram = new Telegram($_data['news_id'], $_data['type']);
		$message = json_decode($telegram->sendMessage(), true);
		$cron = new Cron();

		if($message['ok']) {
			echo json_encode($cron->delete($_data['cron_id']), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} else {
			echo json_encode(['success' => false, 'message' => $message['message']]);
		}

		$mh_admin->clear_cache($cron->getTableName());

		break;

	case 'delete_cron_data':

		$cron = new Cron();

		$mh_admin->clear_cache();

		echo json_encode($cron->delete($_data['cron_id']), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

		break;

	case 'save_cron_data':

		$cron = new Cron();
		$update_cron = $cron->update($_data['cron_id'], [
			'news_id' => $_data['news_id'], 'time' => $_data['time'], 'type' => $_data['type'],
		]);
		$post = $mh_admin->load_data('Post', [
			'table' => 'post', 'selects' => ['id', 'title'], 'where' => [
				'id' => $_data['news_id'],
			]
		])[0];

		$update_cron['data'] = array_merge($update_cron['data'], $post);

		$mh_admin->clear_cache($cron->getTableName());

		echo json_encode($update_cron, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

		break;

	case 'cron_new_entry':

		$cron = new Cron();
		$new_cron = $cron->create([
			                          'news_id' => $_data['news_id'], 'time' => $_data['time'],
			                          'type'    => $_data['type'],
		                          ]);
		$post = $mh_admin->load_data('Post', [
			'table' => 'post', 'selects' => ['id', 'title'], 'where' => [
				'id' => $_data['news_id'],
			]
		])[0];

		$new_cron = array_merge($new_cron, $post);

		$mh_admin->clear_cache($cron->getTableName());

		echo json_encode($new_cron, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

		break;

	case 'get_news':

		try {
			echo json_encode([
				                 'success' => true, 'news' => $mh_admin->load_data('Post', [
					'table' => 'post'
				])
			                 ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch(JsonException $e) {
			echo json_encode([
				                 'success' => false, 'news' => []
			                 ]);
		}

		break;

	case 'get_chat_id':
		$answer = sendMessage("https://api.telegram.org/bot" . $_data['bot'] . "/getUpdates");
		$answer = json_decode($answer, true);

		echo json_encode($answer, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

		echo json_encode($antwort, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		break;

	case 'settings':
		if(!mkdir($concurrentDirectory = ENGINE_DIR . '/inc/maharder/_config', 0777, true)
		   && !is_dir(
				$concurrentDirectory
			)) {
			LogGenerator::generate_log(
				'telegram', 'settings[Сохранение настроек]', sprintf('Папка "%s" не была создана', $concurrentDirectory)
			);
		}
		$file = $concurrentDirectory . '/' . $_POST['module'] . '.json';


		if(empty($_data['list_count']) || !isset($_data['list_count'])) {
			$_data['list_count'] = $config['news_number'];
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


			$_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		file_put_contents($file, $_data);
		clear_cache();

		echo 'ok';

		break;


}
