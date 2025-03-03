<?php

//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// Mod: Telegram Posting
// File: main.php
// Path: /engine/inc/telegram.php
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Author: Maxim Harder <dev@devcraft.club> (c) 2022-2025
// Website: https://devcraft.club
// Telegram: http://t.me/MaHarder
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
// Do not change anything!
//===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

global $mh, $modVars, $mh_template, $htmlTemplate, $config, $dle_login_hash;

use Symfony\Bridge\Twig\Extension\TranslationExtension;

$modInfo = [
	'module_name'        => 'Telegram Posting',
	'module_version'     => '173.8.0',
	'module_id'          => 11,
	'module_description' => 'Отправка сообщений в телеграм канал или группу',
	'module_code'        => 'telegram',
	'module_icon'        => 'fa-brands fa-telegram',
	'site_link'          => 'https://devcraft.club/downloads/telegram-posting.11/',
	'docs_link'          => 'https://readme.devcraft.club/latest/dev/telegramposting/install/',
	'dle_config'         => $config,
	'dle_login_hash'     => $dle_login_hash,
	'crowdin_name'       => 'telegram-posting',
	'crowdin_stat_id'    => '16830581-762905',
];

require_once DLEPlugins::Check(__DIR__ . '/maharder/admin/index.php');

$mh->setLink(
	new AdminLink('cron', __('Отложенный постинг'), THIS_SELF . '?mod=' . $modInfo['module_code'] . '&sites=cron'),
	'cron'
);

switch ($_GET['sites']) {
	default:
		require_once DLEPlugins::Check(MH_MODULES . "/{$modInfo['module_code']}/module/main.php");
		break;
	case 'cron':
		require_once DLEPlugins::Check(MH_MODULES . "/{$modInfo['module_code']}/module/cron_data.php");
		break;
	case 'changelog':
		require_once DLEPlugins::Check(MH_MODULES . "/{$modInfo['module_code']}/module/changelog.php");
		break;
}

$xtraVariable = [
	'breadcrumbs' => $mh->getBreadcrumb(),
	'settings'    => DataManager::getConfig($modInfo['module_code']),
	'links'       => $mh->getVariables('menu')
];

$mh->setVars($modInfo);
$mh->setVars($xtraVariable);
$mh->setVars($modVars);

$mh_template->addExtension(new TranslationExtension(MhTranslation::getTranslator()));

$template = $mh_template->load($htmlTemplate);

echo $template->render($mh->getVariables());
