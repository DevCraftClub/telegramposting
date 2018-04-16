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
	
impFiles('css', $cssfiles);

$adminlink = "?mod=".$codename;

switch ($_GET['do']) {

	case 'save':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/save.php');
		break;


	case 'chat_id':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/getChat.php');
		break;

	case 'sendMessage':
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/sendMessage.php');
		break;

	default:
		require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/default.php');
		break;
}

impFiles('js', $jsfiles);
echofooter();