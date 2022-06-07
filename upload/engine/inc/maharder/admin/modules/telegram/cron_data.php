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

global $mh;

include_once DLEPlugins::Check(MH_ROOT . '/telegram/models/Cron.php');

$mh_config = $mh->getConfig('maharder');

$cron_post = new Cron();

$cur_page = $modInfo['_get']['page'] ?? 1;
$total_pages = @ceil($cron_post->count() / $mh_config['list_count']);
$start = isset($modInfo['_get']['page']) ? (($modInfo['_get']['page'] - 1) * $mh_config['list_count']) : 0;
$end = isset($modInfo['_get']['page']) ? ($modInfo['_get']['page'] * $mh_config['list_count']) : $mh_config['list_count'];

$cron_data = $cron_post->getAll(['limit' => "{$start},{$end}", 'order' => ['main' => ['time' => 'ASC']]]);

$modVars = [
	'title' => _('Отложенный постинг'),
	'cron_data' => $cron_data,
	'page' => $cur_page,
	'total_pages' => $total_pages,
	'all_count' => $cron_post->count()
];

$breadcrumbs[] = [
	'name' => $modVars['title'] . " ({$cron_post->count()} Новостей)",
	'url' => $links['cron']['href'],
];

$htmlTemplate = 'modules/telegram/cron.html';