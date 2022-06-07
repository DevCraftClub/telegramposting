<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: /engine/inc/maharder/admin/modules/telegram/main.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

global $mh, $config;

$settings = $mh->getConfig('telegram', ENGINE_DIR . '/inc/maharder/_config', 'telebot');
$xfields = $mh->loadXfields();
$categories = $mh->load_data("category", [
	'selects' => [
		'id',
		'name',
		'alt_name'
	],
	'order'   => ['name' => 'ASC']
]);

$dependencies = [
	[
		'source' => '',
		'name' => '',
		'value' => _('-- выбираем, не стесняемся --')
	],
	[
		'source' => 'post',
		'name' => 'autor',
		'value' => _('Автор')
	],
	[
		'source' => 'post',
		'name' => 'title',
		'value' => _('Заголовок'),
	],
	[
		'source' => 'post',
		'name' => 'date',
		'value' => _('Дата'),
	],
	[
		'source' => 'post',
		'name' => 'short_story',
		'value' => _('Короткое содержание'),
	],
	[
		'source' => 'post',
		'name' => 'full_story',
		'value' => _('Полное содержание'),
	],
	[
		'source' => 'post',
		'name' => 'descr',
		'value' => _('Описание'),
	],
	[
		'source' => 'post',
		'name' => 'alt_name',
		'value' => _('ЧПУ Имя'),
	],
	[
		'source' => 'post',
		'name' => 'comm_num',
		'value' => _('Кол-во комментариев'),
	],
	[
		'source' => 'post',
		'name' => 'allow_comm',
		'value' => _('Разрешеить комментарии'),
	],
	[
		'source' => 'post',
		'name' => 'allow_main',
		'value' => _('Вывод на главной'),
	],
	[
		'source' => 'post',
		'name' => 'approve',
		'value' => _('Проверено'),
	],
	[
		'source' => 'post',
		'name' => 'fixed',
		'value' => _('Фиксированная новость'),
	],
	[
		'source' => 'post',
		'name' => 'allow_br',
		'value' => _('Разрешить перенос строк'),
	],
	[
		'source' => 'post',
		'name' => 'symbol',
		'value' => _('Символ'),
	],
	[
		'source' => 'post',
		'name' => 'tags',
		'value' => _('Теги'),
	],
	[
		'source' => 'post',
		'name' => 'metatitle',
		'value' => _('Метазаголовок'),
	],
	[
		'source' => 'post_extras',
		'name' => 'news_read',
		'value' => _('Кол-во прочтений'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rate',
		'value' => _('Разрешить рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'rating',
		'value' => _('Рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'vote_num',
		'value' => _('ID Опроса'),
	],
	[
		'source' => 'post_extras',
		'name' => 'votes',
		'value' => _('Кол-во голосов'),
	],
	[
		'source' => 'post_extras',
		'name' => 'view_edit',
		'value' => _('view_edit'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_index',
		'value' => _('Запретить индексировние'),
	],
	[
		'source' => 'post_extras',
		'name' => 'related_ids',
		'value' => _('Похожие новости'),
	],
	[
		'source' => 'post_extras',
		'name' => 'access',
		'value' => _('Доступ'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editdate',
		'value' => _('Время редактирования'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editor',
		'value' => _('Редактор'),
	],
	[
		'source' => 'post_extras',
		'name' => 'reason',
		'value' => _('Причина'),
	],
	[
		'source' => 'post_extras',
		'name' => 'user_id',
		'value' => _('ID автора'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_search',
		'value' => _('Исключить из поиска'),
	],
	[
		'source' => 'post_extras',
		'name' => 'need_pass',
		'value' => _('Нужен пароль'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss',
		'value' => _('Разрешить вывод в RSS-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_turbo',
		'value' => _('Разрешить вывод в Турбо-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_dzen',
		'value' => _('Разрешить Дзен'),
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
	'value' => _('Прочее'),
];

$modVars = [
	'title' => 'Настройки модуля',
	'settings' => $settings,
	'dependencies' => json_encode($dependencies, JSON_UNESCAPED_UNICODE)
];

$htmlTemplate = 'modules/telegram/main.html';