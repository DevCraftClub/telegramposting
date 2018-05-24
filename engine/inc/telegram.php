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

$codename = "telegram";

@include (ENGINE_DIR . '/data/'.$codename.'.php');
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/functions.php');

impFiles('css', $cssfiles);

$adminlink = "?mod=".$codename;

switch ($_GET['do']) {

    case 'save':
        include (ENGINE_DIR . '/inc/maharder/'.$codename.'/save.php');
        break;

    case 'crontab':
        include (ENGINE_DIR . '/inc/maharder/'.$codename.'/cron.php');
        break;

	case 'chat_id':
        include (ENGINE_DIR . '/inc/maharder/'.$codename.'/getChat.php');
		break;

	case 'sendMessage':
        include (ENGINE_DIR . '/inc/maharder/'.$codename.'/sendMessage.php');
		break;

	default:
        include (ENGINE_DIR . '/inc/maharder/'.$codename.'/default.php');
		break;
}

impFiles('js', $jsfiles);
echofooter();