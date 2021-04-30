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

$xfields = loadXfields();
$categories = load_categories();

$dependencies = [
	[
		'source' => '',
		'name' => '',
		'value' => '-- выбираем, не стесняемся --'
	],
	[
		'source' => 'post',
		'name' => 'autor',
		'value' => 'Автор'
	],
	[
		'source' => 'post',
		'name' => 'title',
		'value' => 'Заголовок',
	],
	[
		'source' => 'post',
		'name' => 'date',
		'value' => 'Дата',
	],
	[
		'source' => 'post',
		'name' => 'short_story',
		'value' => 'Короткое содержание',
	],
	[
		'source' => 'post',
		'name' => 'full_story',
		'value' => 'Полное содержание',
	],
	[
		'source' => 'post',
		'name' => 'descr',
		'value' => 'Описание',
	],
	[
		'source' => 'post',
		'name' => 'alt_name',
		'value' => 'ЧПУ Имя',
	],
	[
		'source' => 'post',
		'name' => 'comm_num',
		'value' => 'Кол-во комментариев',
	],
	[
		'source' => 'post',
		'name' => 'allow_comm',
		'value' => 'Разрешеить комментарии',
	],
	[
		'source' => 'post',
		'name' => 'allow_main',
		'value' => 'Вывод на главной',
	],
	[
		'source' => 'post',
		'name' => 'approve',
		'value' => 'Проверено',
	],
	[
		'source' => 'post',
		'name' => 'fixed',
		'value' => 'Фиксированная новость',
	],
	[
		'source' => 'post',
		'name' => 'allow_br',
		'value' => 'Разрешить перенос строк',
	],
	[
		'source' => 'post',
		'name' => 'symbol',
		'value' => 'Символ',
	],
	[
		'source' => 'post',
		'name' => 'tags',
		'value' => 'Теги',
	],
	[
		'source' => 'post',
		'name' => 'metatitle',
		'value' => 'Метазаголовок',
	],
	[
		'source' => 'post_extras',
		'name' => 'news_read',
		'value' => 'Кол-во прочтений',
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rate',
		'value' => 'Разрешить рейтинг',
	],
	[
		'source' => 'post_extras',
		'name' => 'rating',
		'value' => 'Рейтинг',
	],
	[
		'source' => 'post_extras',
		'name' => 'vote_num',
		'value' => 'ID Опроса',
	],
	[
		'source' => 'post_extras',
		'name' => 'votes',
		'value' => 'Кол-во голосов',
	],
	[
		'source' => 'post_extras',
		'name' => 'view_edit',
		'value' => 'view_edit',
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_index',
		'value' => 'Запретить индексировние',
	],
	[
		'source' => 'post_extras',
		'name' => 'related_ids',
		'value' => 'Похожие новости',
	],
	[
		'source' => 'post_extras',
		'name' => 'access',
		'value' => 'Доступ',
	],
	[
		'source' => 'post_extras',
		'name' => 'editdate',
		'value' => 'Время редактирования',
	],
	[
		'source' => 'post_extras',
		'name' => 'editor',
		'value' => 'Редактор',
	],
	[
		'source' => 'post_extras',
		'name' => 'reason',
		'value' => 'Причина',
	],
	[
		'source' => 'post_extras',
		'name' => 'user_id',
		'value' => 'ID автора',
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_search',
		'value' => 'Исключить из поиска',
	],
	[
		'source' => 'post_extras',
		'name' => 'need_pass',
		'value' => 'Нужен пароль',
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss',
		'value' => 'Резрешить вывод в RSS-ленту',
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_turbo',
		'value' => 'Резрешить вывод в Турбо-ленту',
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_dzen',
		'value' => 'Резрешить Дзен',
	],
];

foreach ($categories as $id => $cat) {
	$dependencies[] = [
		'source' => 'category',
		'name' => $cat['id'],
		'value' => $cat['name'],
	];
}

foreach ($xfields as $id => $xf) {
	$dependencies[] = [
		'source' => 'xfields',
		'name' => $id,
		'value' => $xf,
	];
}

$dependencies[] = [
	'source' => 'other',
	'name' => '',
	'value' => 'Прочее',
];

$blockstart = [
	segRow("Включить модуль?", "Включает и выключает модуль", addCheckbox('onof', ($telebot['onof'] == 1) ? true : false), 'onof'),
	segRow("Укажите зависимость", "Выберите поля зависимости, а так-же укажите значения, по которым будет вестись проверка данных перед отправкой в телеграм.<br><br>При выборе 'Прочее' указываем из какой таблицы (post, post_extras), из какого поля и какое значение должно быть в следующей формате - таблица:поле|значение. Пример: post:allow_main|1.<br><br>Если поле не связано с новостями, то значение игнорируется.<br><br>Зависимости типа 'Категория' отмечаются любым значением, желательно единицей (1).", addInput('field',  $telebot['field'], "Укажите зависимость"), 'field'),
	segRow("Миниатюра", "Эта заглушка будет использоваться, чтобы заполнить создать превью для сообщения", addInput('thumb_placeholder',  $telebot['thumb_placeholder'], "Укажите ссылку на миниатюру"), 'thumb_placeholder'),
	segRow("Отношение зависимостей", "Выбираем отношение между зависимостями, по которым будет вестись фильтрация.<br><b>И</b>: Пока все зависимости не будут соответствовать заданным параметрам, отправки в телеграм не будет<br><b>ИЛИ</b>: Пока одна из зависимистей не будет соответствовать заданным параметрам, то отправки в телеграм не будет.", addSelect('field_relation', array('or' =>	"ИЛИ", 'and'	=> "И"),	'Отношение зависимостей', $telebot['field_relation']), 'field_relation'),
	segRow("Вывод сообщений", "Выбираем вывод сообщений в телеграм.<br>
			<b>Текстовой вывод</b> - Обыкновенный вывод сообщений.<br>
			<b>Галлерийный вывод</b> - Вывод сообщений с поддержкой медиавставок (до 10-ти штук)<br>
			<b>Сообщение с постером</b> - Вывод сообщения с основным постером, в качестве постера берётся первое указанное изображение, другие игнорируются<br>
			<b>Сообщение с аудио</b> - Вывод сообщения с аудио, как основа, в качестве аудио берётся первое указанное аудио, другие игнорируются<br>
			<b>Сообщение с видео</b> - Вывод сообщения с видео, как основа, в качестве видео берётся первое указанное видео, другие игнорируются", addSelect
	('message_type', array('text' => "Текстовой", 'media'	=> "Галлерийный", 'photo'	=> "С постером", 'audio' => "С аудио", 'video' => "С видео"), 'Вывод сообщений', $telebot['message_type']), 'message_type'),
	segRow("Включить прокси?", "Включает и выключает прокси", addCheckbox('proxy', ($telebot['proxy'] == 1) ? true : false), 'proxy'),
    segRow("Тип прокси", "Позволяет выбрать тип прокси для подключения. SOCKS5 проски работают ТОЛЬКО с cUrl!", addSelect('proxytype', array('http' => "http(s)", 'socks' => "socks5"), 'Тип прокси', $telebot['proxytype']), 'proxytype'),
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
	segRow('Поддерживаемые теги', 'Следующие BB- & HTML-теги поддерживаются', '<b>&lt;b&gt;, &lt;strong&gt;, [b]</b> - жирный текст<br>
			<b>&lt;i&gt;, &lt;em&gt;, [i]</b> - курсивный текст<br>
			<b>&lt;a&gt;</b> - Ссылка<br>
			<b>&lt;code&gt;, [code]</b> - фиксированный код
			<br><b>&lt;pre&gt;</b> - отформатировынй код
			<br><b>[url=Ссылка]Название Ссылки[/url]</b> - Форматирование ссылки с её названием
			<br><b>[url]Ссылка[/url]</b> - Форматирование ссылки<br><br>
			
			<b>Следующие теги будут заменены на данные:</b><ul>
				<li><b>Стандартные теги полной новости</b> - Ссылка на документацию DLE: <a href="https://dle-news.ru/extras/online/all17.html" target="_blank">/extras/online/all17</a>. За исплючением ссылок на печатные и постраничные страницы. Все функциональные ссылки, типа поднятия рейтинга, были удалены.</li>
				<li><b>Дополнительная поддержка</b> - Модуль Xf Select: <a href="https://devcraft.club/threads/xf-select-vyvod-pravilnogo-znachenija.82/" target="_blank">на сайт плагина</a>.</li>
				<li><b>Следуищие теги теряют свою силу и будут заменены на пустышки:</b> [edit], [/edit], {favorites}, [add-favorites], [/add-favorites], [del-favorites], [/del-favorites], [complaint], [/complaint], {poll}, баннеры, {comments}, {addcomments}, {navigation}, [hide], {pages}, {PAGEBREAK}, [comments-subscribe]</li>
				<li><b>Следующие теги были изменены для модуля:</b> [xfvalue_thumb_url_XXX], [xfvalue_image_url_XXX] - Содержимое ссылок на увелечение было заменено на описание изображения</li>
				<li><b>{tags_no_link}</b> - Вывод тегов без ссылок</li><li><b>{hashtags}</b> - Вывод тегов в виде хештегов (тег без ссылки, но добавляется # к тегу)</li>
				<li><b>[xfvalue_XXX_text]</b> - Если доп. поле является перекрёсной ссылкой, то выводиться будет как простой текст</li>
				<li><b>[xfvalue_XXX_hashtag]</b> - Если доп. поле является перекрёсной ссылкой, то выводиться будет как хештег</li>
				<li><b>[telegram_media_xfield_XXX file=Y max=Z]</b> - Добавляет значение в массив медиа для сообщения.<br>
					XXX - название доп. поля<br>Y - Номер медиафайла, если в перечени несколько файлов (file= не обязателен)<br>
					Z - максимальное кол-во добовляемых файлов, если в перечени несколько файлов, иначе добавятся все (max= не обязателен). Не совместим с опцией file.</li>
				<li><b>[telegram_media_video video=X max=Z]</b> - Добавление видео в массив медиа.<br>
					X - номер видео<br>
					Y - Номер медиафайла, если в перечени несколько файлов (video= не обязателен)<br>
					Z - максимальное кол-во добовляемых файлов, если в перечени несколько файлов, иначе добавятся все (max= не обязателен). Не совместим с опцией video.</li>
				<li><b>[telegram_media_audio audio=X max=Z]</b> - Добавление аудио в массив медиа.<br>
					Y - Номер медиафайла, если в перечени несколько файлов (audio= не обязателен). Не совместим с опцией audio.<br>
					Z - максимальное кол-во добовляемых файлов, если в перечени несколько файлов, иначе добавятся все (max= не обязателен)</li>
				<li><b>[telegram_media_image image=X max=Z]</b> - Добавление изображений в массив медиа.<br>
					Y - Номер медиафайла, если в перечени несколько файлов (image= не обязателен). Не совместим с опцией image.<br>
					Z - максимальное кол-во добовляемых файлов, если в перечени несколько файлов, иначе добавятся все (max= не обязателен). Не совместим с опцией image.</li>
				<li><b>[telegram_media_allimages image=X max=Z]</b> - Учитываются все изображения добавленные в краткую и полную новость, а так-же из доп. полей.<br>
					Y - Номер медиафайла, если в перечени несколько файлов (image= не обязателен)<br>
					Z - максимальное кол-во добовляемых файлов, если в перечени несколько файлов, иначе добавятся все (max= не обязателен). Не совместим с опцией image.</li>
				<li><b>[telegram_thumb]XXX[/telegram_thumb]</b> - Миниатюра или же превьюшка. Вместо XXX заполняем теги, иначе будет браться первое изображение из массива изображений.<br>
				Максимальный размер изображения - 200 kb<br>
				Максимальная высота и ширина - 320 px<br>
				Указывать только онду ссылку на миниатюру, иначе будет браться первое изображение из массива изображений</li>
				<li><b>[telegram_title]XXX[/telegram_title]</b> - Заголовок для сообщения в телеграме. Вмсесто XXX выши теги, иначе будет браться заголовок новости.</li>
				<li><b>[button=X]Y[/button]</b> - Добавление кнопки под сообщением.<br>
						X - ссылка<br>
						Y - описание ссылки</li>
			</ul><br><br>
			Теги <b>[telegram_media_</b> будут обработаны в том случае, если выбран медийный шаблон. Иначе - станут пустышкой. Если загруженный файл не будет являться разрешённым форматом, то он будет загружен как документ. Максимальное кол-во медиа файлов: 10.', ''),
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
	segRow("Документация", "Инструкции по установке, использованию, обновлению...", "<a href=\"{$doc_link}\">{$doc_link}</a>"),
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

$dependencies = json_encode($dependencies);

include_once (DLEPlugins::Check(__DIR__ . '/assets/script.php'));

