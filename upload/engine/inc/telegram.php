<?php

//	===============================
//	Настройки модуля
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );


if(!file_exists(ENGINE_DIR . '/inc/maharder/assets/functions.php')) {
    die("Неустановлен модуль MaHarder Assets. Последняя версия: <a href=\"https://github.com/Gokujo/myAssetsDLE\">https://github.com/Gokujo/myAssetsDLE</a>");
}

$codename = "telegram";

@include (DLEPlugins::Check(ENGINE_DIR . '/data/'.$codename.'.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/assets/functions.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/functions.php'));

impFiles('css', $cssfiles);

$adminlink = "?mod=".$codename;

switch ($_GET['do']) {

    case 'save':
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/save.php'));
        break;

    case 'crontab':
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/cron.php'));
        break;

	case 'chat_id':
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/getChat.php'));
		break;

    case 'sendMessage':
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/sendMessage.php'));
        break;

    case 'documentation':
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/docs.php'));
        break;

	default:
        include (DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/'.$codename.'/default.php'));
		break;
}

impFiles('js', $jsfiles);
echofooter();