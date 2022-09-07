<?php

$logs = [
	'1.7.5' => [
		'[FIX] Исправлены ошибки подключений',
		'[FIX] Исправлен скрипт очистки временных файлов',
	],
	'1.7.4.1' => [
		'[FIX] Исправлен установщик',
	],
	'1.7.4' => [
		'[FIX] Исправлено под MH Admin 2.0.8',
	],
	'1.7.3.1' => [
		'[FIX] Исправлен установщик',
		'[FIX] Удалён мусор',
	],
	'1.7.3' => [
		'[UPDATE] Обновление кода до версии MHAdmin 2.0.7',
		'[NEW] Добавлена возможность назначать отдельно разделители тегов, хештегов и категорий',
		'[FIX] Исправлена совместимость с версией PHP 7.2',
		'[FIX] Исправлена совместимость с версией DLE до 1.5.0',
		'[FIX] Исправлена заявленная ошибка: https://devcraft.club/tickets/prevju.5/',
		'[FIX] Исправлена заявленная ошибка: https://devcraft.club/tickets/jazykovoj-kod-ru_ru-ne-najden-i-mysql-error.4/',
		'[FIX] Исправлена заявленная ошибка: обработка данных тега [telegram_media_',
	],
	'1.7.2.2' => [
		'[FIX] Тег {category} исправлен',
		'[FIX] Тег {link-category} исправлен',
	],
	'1.7.2.1' => [
		'[FIX] Поправлен вывод категорий в виде хештегов',
	],
	'1.7.2' => [
		'[NEW] Добавлен тег {category-hashtag} - отвечающий за вывод категории новости в виде хештегов.',
		'[NEW] Добавлен тег {link-category} - отвечающий за вывод категории новости в виде ссылок на вашем сайте.',
		'[NEW] Добавлен тег {category} - отвечающий за вывод категории новости в виде текста.',
		'[NEW] Добавлен тег {views} - отвечающий за кол-во просмотров новости.',
		'[NEW] Добавлен тег {comments-num} - отвечающий за кол-во комментариев в новости.',
		'[NEW] Добавлен тег {now=FORMAT} - отвечающий за текущее время в пользовательском формате.',
		'[NEW] Добавлен тег {now} - отвечающий за текущее время.',
		'[NEW] Добавлена функция проверки изображения на требуемые свойства для отправки',
		'[UPDATE] Поправлена функция конвертации WebP в JPG, если данная опция отключена в настройках DLE.',
		'[FIX] Исправлена заявленная ошибка на сайте: https://devcraft.club/tickets/modul-telegram-posting-rabotaet-s-oshibkami.2/',
		'[FIX] Исправлена ошибка отправки лишь одного шаблона в телеграм',
	],
	'1.7.1' => [
		'[FIX] Исправлен файл установки (install.xml), при обновлении выдавало ошибку, что значение TABLE лишнее'
	],
	'1.7.0' => [
		'[NEW] К редактору шаблонов была добавлена возможность копирования из других шаблонов',
		'[NEW] К редактору шаблонов была добавлена возможность поиска и вставки возможных тегов с их описанием (макс. 800 символов)',
		'[UPDATE] Редактор сообщений был упрощён',
		'[FIX] Исправлен парсинг содержимого, переставил местами обработчики'
	],
	'1.6.8' => [
		'[NEW]: Добавлена конвертация WebP в JPG/PNG',
		'[NEW]: Добавлена настройка задержки отправки новостей в телеграм при работе с кроном',
		'[NEW]: Добавлена настройка удаления неверно добавленных новостей в отложенные сообщения',
		'[UPDATE] Плагин обновлён до последней версии MHAdmin (2.0.5)',
		'[FIX] Устранена зависимость от класса миниатюр самой DLE, плагин будет работать на DLE 15.1',
		'[FIX] Устранена ошибка проверки зависимости при добавлении/редактировании новости',
		'[FIX] Устранена ошибка отправки новости по крону, функционал переработан',
		'[FIX] Исправлен установщик',
	],
	'1.6.7' => [
		'FIX: Теперь можно указывать прочие зависимости (забыл добавить их в массив)',
		'FIX: Обработка текста перенесена уже в новую функцию, поскольку урезались нужные теги для обработки данных',
		'FIX: Поправлен тег [xfvalue_XXX_hashtag]',
		'FIX: Исправлена работа с кроном. Временный штамп не отправлялся в базу данных',
		'NEW: Добавлен вывод списка с ожидаемыми новостями на отправку',
	],
	'1.6.6' => [
		'Логирование не работает на PHP 7.4, поэтому для таких случаев сделан вывод в браузер',
		'FIX: Убраны дубли',
		'FIX: Исправлена "копипаста"',
	],
	'1.6.5' => [
		'FIX: Для файлов в доп. полях которые сохраняются как [attachment...] была сделана обработка (упустил из виду)',
		'NEW: Максимальная длина сообщения отправляемого в телеграм была установлена, вшита в код. Это - 1024 символов, включая пробелы. Если длина сообщения равна или больше 1024 символов, то отправляются 1021 символа и троеточие в конце.',
		'FIX: Сбор изображений из базы данных был исправлен',
		'FIX: Миниатюры теперь генерируются из списка всех изображений',
	],
	'1.6.4' => [
		'FIX: Исправлена отправка данные, если указана внутренняя ссылка без домена, а-ля /uploads/...',
		'Добавлено кеширование данных на запросы в базу данных',
		'В общий массив медиа добавлены изображения и файлы из базы данных, если такие есть.',
		'FIX: Исправлен запрос в базу данных на новость (забыл закрывающую скобку поставить)',
		'Что-бы включить логирование отправки данных, достаточно в файле repost.class.php заменить $logs = 0 на $logs = 1',
	],
	'1.6.3' => [
		'FIX: Добавление аудио из доп. полей в общий массив',
		'FIX: Добавление видео из доп. полей в общий массив',
		'Добавлена заглушка по умолчанию из сервиса dummyimage.com',
		'FIX: Исправлена отправка файлов со сторонних источников',
		'FIX: Исправлена отправка текстовых сообщений',
	],
	'1.6.2' => [
		'FIX: Отправка сообщений из настроек',
		'FIX: Сохранение зависимостей (скрипт начинал работать после подключения второй зависимости, из-за чего вызывал ошибку 504)',
		'FIX: Если файл указан как ссылка (простое текстовое поле), то он пропускался. Теперь, если файл находится не на сервере, то пропускает проверку, а указывается как ссылка.',
		'FIX: Убраны дубли, из-за чего движок ругался',
	],
	'1.6.1' => [
		'Фикс файла version.php. При мёрдже файл был сохранён не верно'
	],
	'1.6.0' => [
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

$modVars = [
	'title' => 'История изменений',
	'module_icon' => 'fad fa-robot',
	'logs' => $logs,
];

$breadcrumbs[] = [
	'name' => $modVars['title'],
	'url' => $links['changelog']['href'],
];

$htmlTemplate = 'modules/admin/changelog.html';