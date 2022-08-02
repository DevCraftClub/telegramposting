<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: D:\OpenServer\domains\dle150.local/engine/inc/telegram.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

$modInfo = [
	'module_name' => 'Telegram Posting',
	'module_version' => '1.7.32',
	'module_description' => 'Отправка сообщений в телеграм канал или группу',
	'module_code' => 'telegram',
	'module_icon' => 'fa-brands fa-telegram',
	'site_link' => 'https://devcraft.club/downloads/telegram-posting.11/',
	'docs_link' => 'https://readme.devcraft.club/latest/dev/telegramposting/install/',
	'dle_config' => $config,
	'dle_login_hash' => $dle_login_hash,
	'_get' => filter_input_array(INPUT_GET),
    '_post' => filter_input_array(INPUT_POST)
];

require_once DLEPlugins::Check(__DIR__.'/maharder/admin/index.php');

$links['cron'] = [
	'name' => _('Отложенный постинг'),
	'href' => THIS_SELF.'?mod='.$modInfo['module_code'].'&sites=cron',
	'type' => 'link',
	'children' => [],
];

$mh = new Admin();

switch ($_GET['sites']) {
	default:
		require_once DLEPlugins::Check(MH_ADMIN."/modules/{$modInfo['module_code']}/main.php");
		break;
	case 'cron':
		require_once DLEPlugins::Check(MH_ADMIN."/modules/{$modInfo['module_code']}/cron_data.php");
		break;
	case 'changelog':
		require_once DLEPlugins::Check(MH_ADMIN."/modules/{$modInfo['module_code']}/changelog.php");
		break;
}

$xtraVariable = [
	'links' => $links,
	'breadcrumbs' => $breadcrumbs,
	'settings' => $mh->getConfig($modInfo['module_code']),
];

$mh->setVars($modInfo);
$mh->setLinks($links);
$mh->setVars($xtraVariable);
$mh->setVars($modVars);

$template = $mh_template->load($htmlTemplate);

echo $template->render($mh->getVariables());
