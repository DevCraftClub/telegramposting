<?php

//	===============================
//	Настройки модуля | главная
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

switch ($_GET['action']) {
	case 'getID':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=chat_id' => "Модификация бота", '' => "В поисках ID чата") );
		$request = $_POST['save'];
		$telebot['token'] = $telebot['token'] ? $telebot['token'] : $request['token'];
		$answer = file_get_contents( "https://api.telegram.org/bot". $telebot['token'] ."/getUpdates" );
		$answer = json_decode( $answer, true);
		foreach ($answer['result'] as $id => $array) {
			foreach ($array as $key => $value) {
				if(count($value) > 1) {
					if($value['text'] == $request['message']) {
						$chatID = $value['chat']['id'];
						$chatName = $value['chat']['username'];
						$chatTitle = $value['chat']['title'];
						break;
					} else continue;
				} else continue;
			}
		}
		echo "<form class=\"ui form\" action=\"{$adminlink}&do=save\" method=\"post\">";
		foreach ($telebot as $key => $value) {
			if($key == 'chat') $telebot[$key] = $chatID;
			else {
				$telebot[$key] = $value;
				echo "<input type=\"hidden\" id=\"{$key}\" name=\"save[{$key}]\" value=\"{$value}\">";
			}
		}
		$blockform[] = segRow("ID Чата", "Скрипт нашёл этот ID. Вы оставили его в чате <b>{$chatTitle}</b> (@{$chatName}).<br>Если не верно - пробуем ещё раз.", addInput('chat', $chatID, $chatID), 'chat');
		segment('start', $blockform, true);
		saveButton();
		echo "</form>";
		break;

	case 'webhook':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=chat_id' => "Модификация бота", '' => "Создаём Webhook") );
		file_get_contents("https://api.telegram.org/bot". $telebot['token'] ."/setWebhook?url=".$config['http_home_url']);
		$handler = fopen(ENGINE_DIR . '/data/'.$codename.'.php', "w");
	
		fwrite($handler, "<?PHP\n//	===============================\n//	Конфигурация модуля\n//	===============================\n//	Автор: Maxim Harder\n//	Сайт: https://maxim-harder.de\n//	Телеграм: http://t.me/MaHarder\n//	===============================\n//	Меняем, если скрипт неверно сохраняет\n//	===============================\n\n\$telebot = array (");
		foreach ($telebot as $key => $value) {
			fwrite($handler, "\t'{$name}' => \"{$value}\",\n");
		}
		fwrite($handler, "\t'webhook' => \"1\",\n");
		fwrite($handler, ");\n?>");
		fclose($handler);
			
		clear_cache();

		messageOut("Настройки сохранены", "Чтобы проверить - сохранились ли они - перейдите на страницу с настройками.", array($adminlink => "Настройки"));
		break;
	
	default:
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => "Модификация бота") );
		echo "<form class=\"ui form\" action=\"{$adminlink}&do=chat_id&action=getID\" method=\"post\">";
		if(empty($telebot['token'])) $blockform[] = segRow("Укажите токен вашего бота", "Не давайте доступа к настройкам никому. Как узнать токен бота - можно узнать <a href=\"http://help.maxim-harder.de/topic/34-kak-dobavit-bota-v-telegram/\" target=\"_blank\">тут</a>.", addInput('token', $telebot['token'], "Укажите токен вашего бота"), 'token');
		$blockform[] = segRow("Укажите сообщение в чате", "Напишите в чате, где администрирует бот, сообщение в виде: Привет @my_bot. Это-же сообщение продублируйте сюда.", addInput('message', '', "Привет @my_bot"), 'message');
		segment('start', $blockform, true);
		saveButton("Найти ID");
		echo "</form>";
		break;
}

