<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: /engine/inc/maharder/admin/modules/telegram/cron_data.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

global $mh, $MHDB, $modInfo;

use Spiral\Pagination\Paginator;
use Cycle\Database\Injection\Expression;

$filterKeys   = [
	'filter_type',
	'filter_news_id_from' => FILTER_VALIDATE_INT,
	'filter_news_id_to'   => FILTER_VALIDATE_INT,
	'filter_time_from',
	'filter_time_to',
];
$inputFilters = TwigFilter::getDefaultFilters($filterKeys);
$GET_DATA     = filter_input_array(INPUT_GET, $inputFilters);
$repo         = $MHDB->repository(TgCron::class);
$tgConfig            = DataManager::getConfig($modInfo['module_code']);
$mh_config    = DataManager::getConfig('maharder');
$twigFilter   = new TwigFilter($repo);

foreach ($filterKeys as $key => $filter) {
	$GET_DATA[$key] = isset($_GET[$key]) ? DataManager::sanitizeArrayInput(
		$_GET[$key],
		[FILTER_SANITIZE_FULL_SPECIAL_CHARS]
	) : null;
}

$whereClause = null;

$filters = [];
if ($GET_DATA['filter_type'] && $GET_DATA['filter_type']) $filters[] = [
	'type' => ['like' => $GET_DATA['filter_type']]
];
if ($GET_DATA['filter_news_id_from'] && $GET_DATA['filter_news_id_to']) $filters[] = [
	'news_id' => ['between' => [$GET_DATA['filter_news_id_from'], $GET_DATA['filter_news_id_to']]]
];
if ($GET_DATA['filter_time_from']) $filters[] = [
	'time' => [
		'<' => new Expression(
			"STR_TO_DATE('{$GET_DATA['filter_time_from']}', '%Y-%m-%d %H-%i-%s')"
		)
	]
];
if ($GET_DATA['filter_time_to']) $filters[] = [
	'time' => ['>' => new Expression("STR_TO_DATE('{$GET_DATA['filter_time_to']}', '%Y-%m-%d %H-%i-%s')")]
];

if (count($filters)) $whereClause['@and'] = $filters;
$telegram_cron = $repo->select()->where($whereClause);

$cur_page      = $GET_DATA['page'] ?? 1;
$total_pages   = (int)@ceil(count($telegram_cron) / $mh_config['list_count']);
$order         = $GET_DATA['order'] ?? 'id';
$sort          = TwigFilter::getSort($GET_DATA['sort'] ?? 'DESC');
$telegram_cron = $telegram_cron->orderBy($order, $sort);
$paginator     = new Paginator($mh_config['list_count']);
$paginator->withPage($cur_page)->paginate($telegram_cron);
$total_entries = $MHDB->count(TgCron::class);

$modVars = [
	'title'         => __('Список отложенной отправки'),
	'cron_data'     => $telegram_cron->fetchAll(),
	'total_entries' => $total_entries,
	'total_pages'   => $total_pages,
	'page'          => $cur_page,
	'order'         => $order,
	'sort'          => $sort,
	'filters'       => array_merge(
		$twigFilter->createRangeFilter('news_id', __('ID Новости')),
		$twigFilter->createFilter('type', 'select', __('Тип')),
		$twigFilter->createDateRangeFilter('time', __('Запланированная отправка')),
	),
];

$mh->setBreadcrumb(new BreadCrumb($modVars['title'], $mh->getLinkUrl('cron')));
if ($cur_page > 1) {
	$mh->setBreadcrumb(
		new BreadCrumb(__('Страница %page%', ['%page%' => $cur_page]),
					   THIS_SELF . '?' . http_build_query($GET_DATA))
	);
}

$htmlTemplate = 'telegram/cron.html';
