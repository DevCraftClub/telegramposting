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
		$answer = sendMessage( "https://api.telegram.org/bot". $telebot['token'] ."/getUpdates" );
		$answer = json_decode( $answer, true);

		if ($answer['ok']) {

			$chatArr = [
				'id' => 0,
				'title' => '',
				'name' => ''
			];
			foreach ($answer['result'] as $id => $array) {
				if (in_array($request['channelName'], [ $array['channel_post']['chat']['title'], $array['message']['chat']['title'] ])) {
					$chName = 'message';
					if ($array['channel_post']['chat']['title'] != NULL) $chName = 'channel_post';
					$chatArr = [
						'id' => $array[$chName]['chat']['id'],
						'title' => $array[$chName]['chat']['title'],
						'name' => $array[$chName]['chat']['username']
					];
					break;
				}

			}
			if ($chatArr['id'] === 0) {
				messageOut("Произошла ошибка!", "Ваш канал не был найден! Вы уверены, что написали в \"{$request['channelName']}\" сообщение адресованное боту?", array($adminlink => "Настройки", $adminlink . '&do=chat_id' => 'Узнать ID чата'));
			} else {

				echo "<form class=\"ui form\" action=\"{$adminlink}&do=save\" method=\"post\">";
				foreach ( $telebot as $key => $value ) {
					if ( $key == 'chat' ) $telebot[$key] = $chatArr['id'];
					else {
						$telebot[$key] = $value;
						echo "<input type=\"hidden\" id=\"{$key}\" name=\"save[{$key}]\" value=\"{$value}\">";
					}
				}
				$blockform[] = segRow( "ID Чата", "Скрипт нашёл этот ID. Вы оставили его в чате <b>{$chatArr['title']}</b> (@{$chatArr['name']}).<br>Если не верно - пробуем ещё раз.", addInput( 'chat', $chatArr['id'], $chatArr['id'] ), 'chat' );
				segment( 'start', $blockform, true );
				saveButton();
				echo "</form>";
			}
		} else {
			messageOut("Произошла ошибка!", "<strong>Код ошибки</strong>: {$answer['error_code']}<br><strong>Описание ошибки</strong>: {$answer['description']}<br><br>Постарайтесь решить решить проблему описанную в описании", array($adminlink => "Настройки", $adminlink . '&do=chat_id&action=webhook&subaction=delete' => 'Удалить вебхук'));
		}
		break;

	case 'webhook':

		$head = 'Создаём Webhook';
		$msgHead = 'Настройки сохранены';
		$msgDescr = 'Чтобы проверить - сохранились ли они - перейдите на страницу с настройками.';
		$link = "https://api.telegram.org/bot". $telebot['token'] ."/setWebhook?url=".$config['http_home_url'];

		if ($_GET['subaction'] == 'delete') {
			$head = 'Удаляем Webhook';
			$msgHead = 'Вебхук удалён!';
			$msgDescr = 'Чтобы проверить - сохранились ли настройки они - перейдите на страницу с настройками.';
			$link = "https://api.telegram.org/bot". $telebot['token'] ."/deleteWebhook";
		}
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=chat_id' => "Модификация бота", '' => $head) );
		sendMessage($link);
		$handler = fopen(ENGINE_DIR . '/data/'.$codename.'.php', "w");
	
		fwrite($handler, "<?PHP\n//	===============================\n//	Конфигурация модуля\n//	===============================\n//	Автор: Maxim Harder\n//	Сайт: https://maxim-harder.de\n//	Телеграм: http://t.me/MaHarder\n//	===============================\n//	Меняем, если скрипт неверно сохраняет\n//	===============================\n\n\$telebot = array (");
		foreach ($telebot as $key => $value) {
			fwrite($handler, "\t'{$key}' => \"{$value}\",\n");
		}
		if ($_GET['subaction'] == 'delete')
			fwrite($handler, "\t'webhook' => \"0\",\n");
		else
			fwrite($handler, "\t'webhook' => \"1\",\n");
		fwrite($handler, ");\n?>");
		fclose($handler);
			
		clear_cache();

		messageOut($msgHead, $msgDescr, array($adminlink => "Настройки"));
		break;
	
	default:
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => "Модификация бота") );
		echo "<form class=\"ui form\" action=\"{$adminlink}&do=chat_id&action=getID\" method=\"post\">";
		if(empty($telebot['token'])) $blockform[] = segRow("Укажите токен вашего бота", "Не давайте доступа к настройкам никому. Как узнать токен бота - можно узнать <a href=\"http://help.maxim-harder.de/topic/34-kak-dobavit-bota-v-telegram/\" target=\"_blank\">тут</a>.", addInput('token', $telebot['token'], "Укажите токен вашего бота"), 'token');
		$blockform[] = segRow("Название канала или группы", "Затем напишите сообщение адресованное вашему боту в вашем канале или в вашей группе, пример: Привет @my_bot.<br><br>Имеются проблемы с закрытыми обществами!", addInput('channelName', '', "Название канала/группы"), 'message');
		segment('start', $blockform, true);
		saveButton("Найти ID");
		echo "</form>";
		break;
}

