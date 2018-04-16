<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Origin: https://ui.sakuranight.net");

$codename = "telegram";
$helplink = "http://help.maxim-harder.de/forum/31-telegram-posting/";
$sitelink = "https://maxim-harder.de/dle/71-telegram-posting.html";

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');
define('INC_DIR', ENGINE_DIR.'/inc');

require_once ENGINE_DIR.'/classes/mysql.php';
require_once INC_DIR.'/include/functions.inc.php';
include ENGINE_DIR.'/data/dbconfig.php';
include ENGINE_DIR.'/data/config.php';
require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');

$check_db = new db;
$check_db->connect(DBUSER, DBPASS, DBNAME, DBHOST, false);
if( version_compare($check_db->mysql_version, '5.6.4', '<') ) {
    $storage_engine = "MyISAM";
} else $storage_engine = "InnoDB";
unset($check_db);

switch ($_GET['action']) {
    case 'install':
        try {
            $tableSchema = array();
            $tableSchema[] = "INSERT INTO " . PREFIX . "_admin_sections (name, title, descr, icon, allow_groups) VALUES ('{$codename}', '{$name} v{$version}', '{$descr}', '{$codename}.png', '1')";
            foreach ($tableSchema as $table) {
                $db->query($table);
            }
            $html = "Успешно установлено";
        } catch (Exception $e) {
            $fail = $e->getMessage();
            $html = "Произошла ошибка: {$fail}";
        }
        break;

    default:
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta http-equiv="Access-Control-Allow-Credentials" content="True">
    <link href="https://ui.sakuranight.net/css/frame.css" rel="stylesheet">
    <link href="https://ui.sakuranight.net/css/prettify.css" rel="stylesheet">
    <link href="https://ui.sakuranight.net/css/installpage.css" rel="stylesheet">
    <title>{$name} v{$version}</title>
</head>

<body>
    <div class="ui container">
        <div class="ui equal width divided grid">
            <div class="stretched row">
                <div class="three wide column sticky">
                    <div class="ui vertical fluid tabular menu">
                        <a class="active item" data-tab="descr">Описание</a>
                        <a class="item" data-tab="install">Установка</a>
                        <a class="item" data-tab="help">Поддержка</a>
                    </div>
                </div>
                <div class="column content">
                    <div class="ui segment active tab" data-tab="descr">
                        <h2 class="ui header">
                            <i class="fab fa-cloudversify"></i>
                            <div class="content">
								{$name}, Версия {$version}
                                <div class="sub header">Отправка сообщений в телеграм</div>
                            </div>
                        </h2>
                        <p>
                            Модуль будет отправлять сообщения в выбранный чат при помощи бота. Гибкие настройки позволят модулю отправлять лишь то, что вы хотите.<br>Функционал молодой и разрабатывается. Пожелания учитываются.
                        </p>
                    </div>
                    <div class="ui segment tab" data-tab="install">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Установка
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Для установки достаточно закинуть в корень сайта все файлы и запустить <a href="{$_SERVER['PHP_SELF']}?action=install" target="_blank">этот скрипт  <i class="fas fa-external-link-alt"></i></a> (раз вы это читаете, значит вы молодец).</li>
							<li>В настройках модуля укажите токен бота и ID чата, иначе работать не будет.</li>
							<li>Открываем <b>engine/inc/addnews.php</b> и ищем <pre class="prettyprint linenums">clear_cache( array('news_', 'tagscloud_', 'archives_', 'calendar_', 'topnews_', 'rss', 'stats') );</pre> и ставим выше <pre class="prettyprint linenums">include_once (ENGINE_DIR . "/inc/maharder/telegram/addnews.php");</pre></li>
							<li>Открываем <b>engine/inc/editnews.php</b> и ищем <pre class="prettyprint linenums">clear_cache( array('news_', 'full_'.$item_db[0], 'comm_'.$item_db[0], 'tagscloud_', 'archives_', 'calendar_', 'rss', 'stats') );</pre> и ставим выше <pre class="prettyprint linenums">include_once (ENGINE_DIR . "/inc/maharder/telegram/editnews.php");</pre></li>
							<li>Удаляем install.php с корня сайта</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="help">
                        <h2 class="ui header">
                            <i class="fas fa-user-circle"></i>
                            <div class="content">
                                Поддержка
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                         <p>Поддержка скрипта проводится в <strong>свободное</strong> от работы <strong>время</strong> и делается на <strong>бесплатной основе</strong> в кодировке <strong>UTF-8</strong>. Автор не несёт ответственности за ваши модификации и с ними связанные ошибки
                            при установке.</p>
                        <p><strong>Вы имеете право</strong>:</p>
                        <ul>
                            <li>Запросить о помощи в ветке на <a href="{$helplink}" target="_blank" rel="noopener">форуме</a>, на <a href="{$sitelink}" target="_blank" rel="noopener">сайте</a> или в <a href="https://t.me/MaHarder" target="_blank" rel="noopener">телеграме</a> автора</li>
                            <li>Адаптировать функционал под себя</li>
                            <li>Адаптировать дизайн под себя</li>
                            <li>Предлагать новый функционал через те-же ветки, что описаны выше</li>
                            <li>Публиковать модуль в публичном доступе</li>
                        </ul>
                        <p><strong>Вы не имеете право:</strong></p>
                        <ul>
                            <li>Присваивать авторство себе</li>
                            <li>Требовать невозможного</li>
                            <li>Публиковать адаптации без согласия автора в сети</li>
                            <li>Распространять модуль без указания автора</li>
                            <li>Удалять копирайты</li>
                        </ul>
                        <p><strong>Авторство</strong>:</p>
                        <ul>
                            <li><strong>Автор</strong>: Maxim Harder</li>
                            <li><strong>Телеграм</strong>: <a href="https://t.me/MaHarder" target="_blank" rel="noopener">MaHarder</a></li>
                        </ul>
                        <p><strong>Финансовая поддержка (доброволная)</strong>:</p>
                        <ul>
                            <li><strong>Webmoney (RU)</strong>:&nbsp;R127552376453</li>
                            <li><strong>Webmoney (USD)</strong>:&nbsp;Z139685140004</li>
                            <li><strong>Webmoney (EU)</strong>:&nbsp;E275336355586</li>
                            <li><strong>PayPal</strong>:&nbsp;<a href="https://paypal.me/MaximH" target="_blank" rel="noopener">paypal.me/MaximH</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ui.sakuranight.net/js/jquery.js"></script>
    <script src="https://ui.sakuranight.net/js/frame.js"></script>
    <script src="https://ui.sakuranight.net/js/icons.js"></script>
    <script src="https://ui.sakuranight.net/js/prettify.js"></script>
    <script src="https://ui.sakuranight.net/js/run_prettify.js"></script>
    <script src="https://ui.sakuranight.net/js/installpage.js"></script>
</body>

</html>
HTML;

        break;

}

echo $html;