<?php

//	===============================
//	Версия модуля и изменения
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

$name = "telegram Posting";
$author = [
	'name' => "Maxim Harder",
	'site' => "https://devcraft.club",
	'social' => [
		'telegram' => "https://t.me/MaHarder",
		'E-Mail' => "dev@devcraft.club",
	],
];
<<<<<<< Updated upstream
$version = "1.5";
=======
$version = "1.6";
>>>>>>> Stashed changes
$descr = "Позволяет уведомлять пользователей в телеграме о новых добавлениях на сайте";
$doc_link = "https://devcraft.club/articles/telegram-posting.7/";
$changes = [
	'1.6' => [
		'Добавлена поддержка медиа контента. Можно выбирать одно из двух.',
		'Добавлена поддержка парсинга всех стандртных тегов DLE',
		'Исправлена проблема с отрпавкой данных из HTML редактора'
	],
	'1.5.1' => [
		'Небольшой фикс касательно поиска названия группы / канала',
		'Созданы значения по умолчанию для крона, теперь, даже если он и не настроен, телеграм будет получать сообщения по крону'
	],
	'1.5' => [
		'Обновлено для версии DLE 14.x',
		'Добавлена возможность выводить лимитированные описания',
		'Исправлен поиск чата',
		'Исправлены мелкие баги и недочёты'
	],
	'1.4' => [
		'Добавлена поддержка SOCKS5 прокси.'
	],
	'1.3.3' => [
		'Небольшой фикс со стилями.'
	],
	'1.3.2' => [
		'Небольшой фикс по отправке сообщений.'
	],
	'1.3.1' => [
		'Исправлена ошибка созданная гитом'
	],
	'1.3' => [
		'Добавлена возможность отправлять в телеграм при добавлении с сайта',
		'Добавлены новые теги для шаблонов: %categories% (выводит все категории через разделитель указанный в настройках движка) и %category_links% (выводит так же категории, только ссылками)',
		'Добавлены новые теги для шаблонов: [xfgiven_XXX]XYZ[/xfgiven_XXX] (аналогичен тегам для новостей, если доп. поле заполнено, то выведет информацию заключённую в теги)',
		'Добавлены новые теги для шаблонов: [xfnotgiven_XXX]XYZ[/xfnotgiven_XXX] (аналогичен тегам для новостей, если доп. поле не заполнено, то выведет информацию заключённую в теги)',
		'Исправлено пару багов',
		'Облегчённая версия'
	],
	'1.2.1' => [
		'Версия для DLE 13 и выше'
	],
	'1.2' => [
		'Мелкие правки',
		'Добавлена возможность использовать прокси (актуально для сайтов размещённых в РФ)',
		'Добавлена возможность отправки сообщений по крону',
		'Последняя версия для DLE 12.x (Добавлена конвертация текстав нужную кодировку)'
	],
	'1.1' => [
		'Исправлена ошибка со считыванием данных',
		'Добавлены новые теги для шаблонов: %full_descr% и %short_descr%',
		'Тег %descr% будет заменён на %short_descr%'
	],
	'1.01' => [
		'Исправлена ошибка с доп. полями'
	],
	'1.0' => [
		'Базовая версия',
		'Отправка сообщений при добавлении новости',
		'Отправка сообщений при редактировании новости',
		'Отправка при зависимости от полей',
		'Отправка обычного сообщения на канал',
		'Поиск нужного чата',
		'Настройка шаблонов для отправки сообщений',
		'Если сайт работает через SSL, то будет возможность приобразовать бота в Webhook'
	]
];