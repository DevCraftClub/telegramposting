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

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array('' => $name) );

$boxList = [
	'start' => [
		'name' => "Основные функции",
		'icon' => "home icon",
	],
	'telegram' => [
		'name' => "Настройка бота",
		'icon' => "universal access icon",
	],
	'templates' => [
		'name' => "Шаблоны",
		'icon' => "pencil alternate icon",
	],
	'message' => [
		'name' => "Отправить сообщение",
		'icon' => "pen square icon",
	], 
	'author' => [
		'name' => "Автор",
		'icon' => "user circle icon",
	],
];

boxes($boxList);
echo "<form class=\"ui form\" method=\"POST\" action=\"".$adminlink."&do=save\">";

foreach ($telebot as $name => $value) {
	$telebot[$name] = htmlspecialchars_decode ( $value );
}

$blockstart = [
	segRow("Включить модуль?", "Включает и выключает модуль", addCheckbox('onof', ($telebot['onof'] == 1) ? true : false), 'onof'),
	segRow("Укажите зависимость", "Укажите поле, от чего будет зависеть отправка уведомления в телеграм.<br>Если это поле в dle_post или dle_post_extra, то впишите <b>post</b>:field|value.<br>Если это значение зависит от доп. поля, то укажите <b>xf</b>:field|value.<br>Как уже понятно, то вместо field вписываем название поля, а вместо value - значение, которое будет влиять на выборку<br>Пример:<br>- xf:telegraminform|1<br>- post:allow_main|1<br>Т.е., при этих значениях в канале телеграма появится информация", addInput('field', $telebot['field'], "Укажите зависимость"), 'field'),
];
if($config['only_ssl'] && !empty($telebot['token']) && !empty($telebot['chat']) && !$telebot['webhook']) $blockstart[] = segRow("Активируем WebHook", "Для более стабильной работы нужно активировать вебхук для бота. Для этого обязателен SSL сертификат. Сертификаты от Wildcard могут не работать", "<a href=\"{$adminlink}&do=chat_id&action=webhook\">Активировать</a>", '');

$blocktelegram = [
	segRow("Укажите токен вашего бота", "Не давайте доступа к настройкам никому. Как узнать токен бота - можно узнать <a href=\"http://help.maxim-harder.de/topic/34-kak-dobavit-bota-v-telegram/\" target=\"_blank\">тут</a>.", addInput('token', $telebot['token'], "Укажите токен вашего бота"), 'token'),
	segRow("Укажите ID канала", "Не давайте доступа к настройкам никому. Как узнать ID чата - можно узнать <a href=\"http://help.maxim-harder.de/topic/35-kak-uznat-id-chata/\" target=\"_blank\">тут</a>, либо узнать через скрипт <a href=\"{$adminlink}&do=chat_id\" target=\"_blank\">тут</a>.", addInput('chat', $telebot['chat'], "Укажите токен вашего бота"), 'chat'),
];
$blocktemplates = [
	segRow('Поддерживаемые теги', 'Следующие BB- & HTML-теги поддерживаются', '<b>&lt;b&gt;, &lt;strong&gt;, [b]</b> - жирный текст<br><b>&lt;i&gt;, &lt;em&gt;, [i]</b> - курсивный текст<br><b>&lt;a&gt;</b> - Ссылка<br><b>&lt;code&gt;, [code]</b> - фиксированный код<br><b>&lt;pre&gt;</b> - отформатировынй код<br><b>[url=Ссылка]Название Ссылки[/url]</b> - Форматирование ссылки с её названием<br><b>[url]Ссылка[/url]</b> - Форматирование ссылки<br><br><b>Следующие теги будут заменены на данные:</b><ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%xf_field%</b> - выводит значение поля, вместо field название доп.поля</li><li><b>%autor%</b> - автор новости</li><li><b>%title%</b> - название новости</li><li><b>%short_descr%</b> - короткое описание новости</li><li><b>%full_descr%</b> - полное описание новости</li></ul>', ''),
	segRow('Шаблон сообщения при добавлении новости', '<b>Разрешены BB-Code и HTML-Code</b>', addTextarea('addnews', $telebot['addnews'], 'Шаблон сообщения при добавлении новости'), 'addnews'),
	segRow('Шаблон сообщения при редактировании новости', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%editreason%</b> - если было заполнено поле \"Причина редактирования\", то используйте этот тег</li></ul>', addTextarea('editnews', $telebot['editnews'], 'Шаблон сообщения при редактировании новости'), 'editnews'),
];
$blockmessage = [
	segRow('Отправить сообщение в группу/канал', 'Для проверки функционала или для оповещения, которое никак не связанно с новостью', "<a class=\"ui button\" href=\"{$adminlink}&do=sendMessage\">Перейти</a>", ''),
];
$blockauthor = [
	segRow("Автор", "", author('name')),
	segRow("Связь", "", author('social')),
	segRow("Версия модуля", "", $version),
	segRow("Изменения", "", author('changes')),
];

segment('start', $blockstart, true);
segment('telegram', $blocktelegram);
segment('templates', $blocktemplates);
segment('message', $blockmessage);
segment('author', $blockauthor);
saveButton();
if($telebot['webhook']) echo "<input type=\"hidden\" id=\"webhook\" name=\"save[webhook]\" value=\"1\">";
echo "</form>";