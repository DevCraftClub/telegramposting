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
    'cron' => [
        'name' => "Настройки крона",
        'icon' => "clock outline icon",
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
    segRow("Метод подключения", "Для file_get_contents - нужна поддержка <b>allow_url_fopen</b><br>Для cUrl нужна поддержка <b>curl</b>", addSelect('method', array(1 => "file_get_contents", 2 => "cUrl"), 'Метод подключение', $telebot['method']), 'method'),
    segRow("Включить прокси?", "Включает и выключает прокси", addCheckbox('proxy', ($telebot['proxy'] == 1) ? true : false), 'proxy'),
    segRow("Укажите IP-Адрес", "Укажите IP-адрес прокси сервера", addInput('proxyip', $telebot['proxyip'], "Укажите IP-адрес"), 'proxyip'),
    segRow("Укажите IP-порт", "Укажите IP-порт прокси сервера", addInput('proxyport', $telebot['proxyport'], "Укажите IP-порт"), 'proxyport'),
    segRow("Нужна ли авторизация?", "Если для проски нужны данные авторизации - включаем ", addCheckbox('proxyauth', ($telebot['proxyauth'] == 1) ? true : false), 'proxyauth'),
    segRow("Укажите пользователя", "Укажите пользователя для прокси сервера", addInput('proxyuser', $telebot['proxyuser'], "Пользователь прокси"), 'proxyuser'),
    segRow("Укажите пароль", "Укажите пароль для прокси сервера", addInput('proxypass', $telebot['proxypass'], "Пароль пользователя"), 'proxypass'),

];
if($config['only_ssl'] && !empty($telebot['token']) && !empty($telebot['chat']) && !$telebot['webhook']) $blockstart[] = segRow("Активируем WebHook", "Для более стабильной работы нужно активировать вебхук для бота. Для этого обязателен SSL сертификат. Сертификаты от Wildcard могут не работать", "<a href=\"{$adminlink}&do=chat_id&action=webhook\">Активировать</a>", '');
$blockcron = [
    segRow('Включить отправку по крону?', "Если выключено, то сообщение отправляется сразу в чат.", addCheckbox('cron', ($telebot['cron'] == 1) ? true : false), 'cron' ),
    segRow('Сколько новостей за раз добавлять?', "Данная функция позволит за раз отправить несколько новостей в чат. Если 0, то будет добавлять все новости.", addInput('cron_news', $telebot['cron_news'], 'N новостей за раз'), 'cron_news'),
    segRow('Крон по времени?', "Данная функция позволит за раз отправить несколько новостей в чат после определённого времени. Сравнение будет идти по дате добавления новости. Добавляем значение в <b>минутах</b>.<br>Т.е., если время новости+эти минуты уже прошло, то новость отправится в телеграм.", addInput('cron_time', $telebot['cron_time'], 'Указываем в минутах'), 'cron_time'),
    segRow('Настройка крона', "Если вы не сильны в crontab и не знаете как правильно настроить всё - данная функция для вас", "Если у вас нет доступа к крону или SSH, то выполняем этот скрипт: <a href=\"{$config['http_home_url']}cron.php?cronmode=telegram\" target=\"_blank\">cron.php?cronmode=telegram</a><br>Если же вы всётаки его имеете, то выполните этот запрос <kbd>crontab -e</kbd> и в самый низ вставьте следующую строчку:<br><kbd>* * * * * /usr/bin/php -f {$_SERVER['DOCUMENT_ROOT']}/cron.php telegram</kbd><br>Простой генератор для крона можно увидеть тут: <a href='http://www.crontabgenerator.com' target='_blank'>http://www.crontabgenerator.com</a><br>Замените <kbd>/usr/bin/php</kbd> на путь вашего интерпретатора. Если не знаете где он лежит, то узнаете это с помощью <kbd>which php</kbd>", ''),
];
$blocktelegram = [
	segRow("Укажите токен вашего бота", "Не давайте доступа к настройкам никому. Как узнать токен бота - можно узнать <a href=\"http://help.maxim-harder.de/topic/34-kak-dobavit-bota-v-telegram/\" target=\"_blank\">тут</a>.", addInput('token', $telebot['token'], "Укажите токен вашего бота"), 'token'),
	segRow("Укажите ID канала", "Не давайте доступа к настройкам никому. Как узнать ID чата - можно узнать <a href=\"http://help.maxim-harder.de/topic/35-kak-uznat-id-chata/\" target=\"_blank\">тут</a>, либо узнать через скрипт <a href=\"{$adminlink}&do=chat_id\" target=\"_blank\">тут</a>.", addInput('chat', $telebot['chat'], "Укажите токен вашего бота"), 'chat'),
];
$blocktemplates = [
	segRow('Поддерживаемые теги', 'Следующие BB- & HTML-теги поддерживаются', '<b>&lt;b&gt;, &lt;strong&gt;, [b]</b> - жирный текст<br><b>&lt;i&gt;, &lt;em&gt;, [i]</b> - курсивный текст<br><b>&lt;a&gt;</b> - Ссылка<br><b>&lt;code&gt;, [code]</b> - фиксированный код<br><b>&lt;pre&gt;</b> - отформатировынй код<br><b>[url=Ссылка]Название Ссылки[/url]</b> - Форматирование ссылки с её названием<br><b>[url]Ссылка[/url]</b> - Форматирование ссылки<br><br><b>Следующие теги будут заменены на данные:</b><ul><li><b>%link%</b> - Ссылка на новость</li><li><b>%xf_field%</b> - выводит значение поля, вместо field название доп.поля</li><li><b>%autor%</b> - автор новости</li><li><b>%title%</b> - название новости</li><li><b>%short_descr%</b> - короткое описание новости</li><li><b>%full_descr%</b> - полное описание новости</li></ul>', ''),
	segRow('Шаблон сообщения при добавлении новости', '<b>Разрешены BB-Code и HTML-Code</b>', addTextarea('addnews', $telebot['addnews'], 'Шаблон сообщения при добавлении новости'), 'addnews'),
	segRow('Шаблон сообщения при редактировании новости', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%editreason%</b> - если было заполнено поле \"Причина редактирования\", то используйте этот тег</li></ul>', addTextarea('editnews', $telebot['editnews'], 'Шаблон сообщения при редактировании новости'), 'editnews'),
    segRow('Шаблон сообщения при добавлении новости по крону', '<b>Разрешены BB-Code и HTML-Code</b><br>Если поле будет пустым, то будет использоваться шаблон при добавлении новости', addTextarea('cron_addnews', $telebot['cron_addnews'], 'Шаблон сообщения при добавлении новости'), 'cron_addnews'),
    segRow('Шаблон сообщения при редактировании новости по крону', '<b>Разрешены BB-Code и HTML-Code</b><br>Следующие теги будут заменены на данные:<ul><li><b>%editreason%</b> - если было заполнено поле \"Причина редактирования\", то используйте этот тег</li></ul><br>Если поле будет пустым, то будет использоваться шаблон при редактированиие новости', addTextarea('cron_editnews', $telebot['cron_editnews'], 'Шаблон сообщения при редактировании новости'), 'cron_editnews'),
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
segment('cron', $blockcron);
segment('telegram', $blocktelegram);
segment('templates', $blocktemplates);
segment('message', $blockmessage);
segment('author', $blockauthor);
saveButton();
if($telebot['webhook']) echo "<input type=\"hidden\" id=\"webhook\" name=\"save[webhook]\" value=\"1\">";
echo "</form>";