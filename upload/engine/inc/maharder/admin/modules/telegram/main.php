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
		'value' => __('telegram', '-- выбираем, не стесняемся --')
	],
	[
		'source' => 'post',
		'name' => 'autor',
		'value' => __('telegram', 'Автор')
	],
	[
		'source' => 'post',
		'name' => 'title',
		'value' => __('telegram', 'Заголовок'),
	],
	[
		'source' => 'post',
		'name' => 'date',
		'value' => __('telegram', 'Дата'),
	],
	[
		'source' => 'post',
		'name' => 'short_story',
		'value' => __('telegram', 'Короткое содержание'),
	],
	[
		'source' => 'post',
		'name' => 'full_story',
		'value' => __('telegram', 'Полное содержание'),
	],
	[
		'source' => 'post',
		'name' => 'descr',
		'value' => __('telegram', 'Описание'),
	],
	[
		'source' => 'post',
		'name' => 'alt_name',
		'value' => __('telegram', 'ЧПУ Имя'),
	],
	[
		'source' => 'post',
		'name' => 'comm_num',
		'value' => __('telegram', 'Кол-во комментариев'),
	],
	[
		'source' => 'post',
		'name' => 'allow_comm',
		'value' => __('telegram', 'Разрешить комментарии'),
	],
	[
		'source' => 'post',
		'name' => 'allow_main',
		'value' => __('telegram', 'Вывод на главной'),
	],
	[
		'source' => 'post',
		'name' => 'approve',
		'value' => __('telegram', 'Проверено'),
	],
	[
		'source' => 'post',
		'name' => 'fixed',
		'value' => __('telegram', 'Фиксированная новость'),
	],
	[
		'source' => 'post',
		'name' => 'allow_br',
		'value' => __('telegram', 'Разрешить перенос строк'),
	],
	[
		'source' => 'post',
		'name' => 'symbol',
		'value' => __('telegram', 'Символ'),
	],
	[
		'source' => 'post',
		'name' => 'tags',
		'value' => __('telegram', 'Теги'),
	],
	[
		'source' => 'post',
		'name' => 'metatitle',
		'value' => __('telegram', 'Метазаголовок'),
	],
	[
		'source' => 'post_extras',
		'name' => 'news_read',
		'value' => __('telegram', 'Кол-во прочтений'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rate',
		'value' => __('telegram', 'Разрешить рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'rating',
		'value' => __('telegram', 'Рейтинг'),
	],
	[
		'source' => 'post_extras',
		'name' => 'vote_num',
		'value' => __('telegram', 'ID Опроса'),
	],
	[
		'source' => 'post_extras',
		'name' => 'votes',
		'value' => __('telegram', 'Кол-во голосов'),
	],
	[
		'source' => 'post_extras',
		'name' => 'view_edit',
		'value' => __('telegram', 'view_edit'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_index',
		'value' => __('telegram', 'Запретить индексировние'),
	],
	[
		'source' => 'post_extras',
		'name' => 'related_ids',
		'value' => __('telegram', 'Похожие новости'),
	],
	[
		'source' => 'post_extras',
		'name' => 'access',
		'value' => __('telegram', 'Доступ'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editdate',
		'value' => __('telegram', 'Время редактирования'),
	],
	[
		'source' => 'post_extras',
		'name' => 'editor',
		'value' => __('telegram', 'Редактор'),
	],
	[
		'source' => 'post_extras',
		'name' => 'reason',
		'value' => __('telegram', 'Причина'),
	],
	[
		'source' => 'post_extras',
		'name' => 'user_id',
		'value' => __('telegram', 'ID автора'),
	],
	[
		'source' => 'post_extras',
		'name' => 'disable_search',
		'value' => __('telegram', 'Исключить из поиска'),
	],
	[
		'source' => 'post_extras',
		'name' => 'need_pass',
		'value' => __('telegram', 'Нужен пароль'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss',
		'value' => __('telegram', 'Разрешить вывод в RSS-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_turbo',
		'value' => __('telegram', 'Разрешить вывод в Турбо-ленту'),
	],
	[
		'source' => 'post_extras',
		'name' => 'allow_rss_dzen',
		'value' => __('telegram', 'Разрешить Дзен'),
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
	'value' => __('telegram', 'Прочее'),
];

$modVars = [
	'title' => 'Настройки модуля',
	'settings' => $settings,
	'dependencies' => json_encode($dependencies, JSON_UNESCAPED_UNICODE)
];

$htmlTemplate = 'modules/telegram/main.html';
