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
		'value' => __('-- выбираем, не стесняемся --')
	],
	[
		'source' => 'post',
		'name' => 'autor',
		'value' => __('Автор')
	],
	[
		'source' => 'post',
		'name' => 'title',
		'value' => __('Заголовок'),
	],
	[
		'source' => 'post',
		'name' => 'date',
		'value' => __('Дата'),
	],
	[
		'source' => 'post',
		'name' => 'short_story',
		'value' => __('Короткое содержание'),
	],
	[
		'source' => 'post',
		'name' => 'full_story',
		'value' => __('Полное содержание'),
	],
	[
		'source' => 'post',
		'name' => 'descr',
		'value' => __('Описание'),
	],
	[
		'source' => 'post',
		'name' => 'alt_name',
		'value' => __('ЧПУ Имя'),
	],
	[
		'source' => 'post',
		'name' => 'comm_num',
		'value' => __('Кол-во комментариев'),
	],
	[
		'source' => 'post',
		'name' => 'allow_comm',
		'value' => __('Разрешеить комментарии'),
	],
	[
		'source' => 'post',
		'name' => 'allow_main',
		'value' => __('Вывод на главной'),
	],
	[
		'source' => 'post',
		'name' => 'approve',
		'value' => __('Проверено'),
	],
	[
		'source' => 'post',
		'name' => 'fixed',
		'value' => __('Фиксированная новость'),
	],
	[
		'source' => 'post',
		'name' => 'allow_br',
		'value' => __('Разрешить перенос строк'),
	],
	[
		'source' => 'post',
		'name' => 'symbol',
		'value' => __('Символ'),
	],
	[
		'source' => 'post',
		'name' => 'tags',
		'value' => __('Теги'),
	],
	[
		'source' => 'post',
		'name' => 'metatitle',
		'value' => __('Метазаголовок'),
	],
	[
		'source' => 'post_extras',
		'name' => 'news_read',
		'value' => __('Кол-во прочтений'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rate',
		'value' => __('Разрешить рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'rating',
		'value' => __('Рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'vote_num',
		'value' => __('ID Опроса'),
	],
	[
		'source' => 'post_extras',
		'name' => 'votes',
		'value' => __('Кол-во голосов'),
	],
	[
		'source' => 'post_extras',
		'name' => 'view_edit',
		'value' => __('view_edit'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_index',
		'value' => __('Запретить индексировние'),
	],
	[
		'source' => 'post_extras',
		'name' => 'related_ids',
		'value' => __('Похожие новости'),
	],
	[
		'source' => 'post_extras',
		'name' => 'access',
		'value' => __('Доступ'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editdate',
		'value' => __('Время редактирования'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editor',
		'value' => __('Редактор'),
	],
	[
		'source' => 'post_extras',
		'name' => 'reason',
		'value' => __('Причина'),
	],
	[
		'source' => 'post_extras',
		'name' => 'user_id',
		'value' => __('ID автора'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_search',
		'value' => __('Исключить из поиска'),
	],
	[
		'source' => 'post_extras',
		'name' => 'need_pass',
		'value' => __('Нужен пароль'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss',
		'value' => __('Разрешить вывод в RSS-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_turbo',
		'value' => __('Разрешить вывод в Турбо-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_dzen',
		'value' => __('Разрешить Дзен'),
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
	'value' => __('Прочее'),
];

$modVars = [
	'title' => __('Настройки модуля'),
	'dependencies' => json_encode($dependencies, JSON_UNESCAPED_UNICODE)
];

$htmlTemplate = 'telegram/main.html';