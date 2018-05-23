<?php

//	===============================
//	Настройки модуля | Отправляем сообщение в группу
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if(!$telebot['token'] && !$telebot['chat']) messageOut("Нехватает настроек", "Убедитесь, что вы сохранили токен бота и ID чата.", array($adminlink => "Настройки"));
require_once (ENGINE_DIR . '/inc/maharder/telegram/functions.php');

switch ($_GET['action']) {
	case 'send':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink."&do=sendMessage" => "Отправка сообщения", '' => "Отправка") );
		$req = $_REQUEST['save'];
		$temes = $req['sendMessage'];
		$temes = str_replace('[b]', '<b>', $temes);
		$temes = str_replace('[/b]', '</b>', $temes);
		$temes = str_replace('[i]', '<i>', $temes);
		$temes = str_replace('[/i]', '</i>', $temes);
		$temes = str_replace('[code]', '<code>', $temes);
		$temes = str_replace('[/code]', '</code>', $temes);
		$temes = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $temes);
		$temes = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $temes);
		$turl = "https://api.telegram.org/bot". $telebot['token'] ."/sendMessage?chat_id=". $telebot['chat'] ."&text=" . urlencode ( $temes ) . "&parse_mode=HTML";
        sendMessage($turl);
		messageOut("Сообщение отправлено", "Дообщение было отправлено в чат.", array($adminlink => "Настройки", $adminlink."&do=sendMessage" => "Отправить ещё одно сообщение"));
		break;

	default:
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => "Отправка сообщения") );
		echo "<form class=\"ui form\" method=\"POST\" action=\"".$adminlink."&do=sendMessage&action=send\">";
		$outblock = [
			segRow('Поддерживаемые теги', 'Следующие BB- & HTML-теги поддерживаются', '<b>&lt;b&gt;, &lt;strong&gt;, [b]</b> - жирный текст<br><b>&lt;i&gt;, &lt;em&gt;, [i]</b> - курсивный текст<br><b>&lt;a&gt;</b> - Ссылка<br><b>&lt;code&gt;, [code]</b> - фиксированный код<br><b>&lt;pre&gt;</b> - отформатировынй код<br><b>[url=Ссылка]Название Ссылки[/url]</b> - Форматирование ссылки с её названием<br><b>[url]Ссылка[/url]</b> - Форматирование ссылки', ''),
			segRow('Ваше сообщение народу', 'Впишите любой текст.', addTextarea('sendMessage', '', 'Сообщение народу'), 'sendMessage'),
		];
		segment('Messega', $outblock, true);
		saveButton("Отправить");
		echo "</form>";
		break;
}

