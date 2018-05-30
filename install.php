<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Origin: //static.maxim-harder.de/");

$codename = "telegram";
$helplink = "http://help.maxim-harder.de/forum/31-telegram-posting/";
$sitelink = "https://maxim-harder.de/dle/71-telegram-posting.html";

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');
define('INC_DIR', ENGINE_DIR.'/inc');

require_once INC_DIR.'/include/functions.inc.php';
include ENGINE_DIR.'/data/config.php';
if(!file_exists(ENGINE_DIR . '/inc/maharder/assets/functions.php')) {
    die("Неустановлен модуль MaHarder Assets. Последняя версия: <a href=\"https://github.com/Gokujo/myAssetsDLE\">https://github.com/Gokujo/myAssetsDLE</a>");
} else require_once (ENGINE_DIR . '/inc/maharder/assets/functions.php');
require_once (ENGINE_DIR . '/inc/maharder/'.$codename.'/version.php');

if($config['version_id'] < 13) die('Версия DLE ниже 13. Данная версия предназначена для версий 13 и выше.');

$html = <<<HTML
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta http-equiv="Access-Control-Allow-Credentials" content="True">
    <link href="/engine/skins/maharder/css/frame.css" rel="stylesheet">
    <link href="//static.maxim-harder.de/semantic/css/prettify.css" rel="stylesheet" integrity="sha384-tGeLopS7aWCwgeqg+ah7c+iI19JU3teQPshyaQVybvOMRravAVLscwoeT4HPsfoW" crossorigin="anonymous">
    <link rel="stylesheet" href="//static.maxim-harder.de/semantic/css/installpage.css" integrity="sha384-52WMk/u9qMdHs6Q2JvnYGca3v19hLxXAJvHYPRX5lTxss9fu3g2HS/mF4RZOiZNW" crossorigin="anonymous">
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
                        <a class="item" data-tab="update13">Обновление до 1.3</a>
                        <a class="item" data-tab="update12">Обновление до 1.2</a>
                        <a class="item" data-tab="update11">Обновление до 1.1</a>
                        <a class="item" data-tab="update101">Обновление до 1.01</a>
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
                            <li>Для установки достаточно закинуть в корень сайта все файлы</li>
                            <li>Установите <b>install_telegramposting.xml</b> в админпанеле <a href="{$config['http_home_url']}{$config['admin_path']}?mod=plugins" target="_blank">через систему плагинов  <i class="fas fa-external-link-alt"></i></a>.</li>
                            <li>Открываем <b>/cron.php</b> и ищем <pre class="prettyprint linenums">\$allow_cron = 0;</pre> и меняем значение на <pre class="prettyprint linenums">\$allow_cron = 1;</pre></li>
							<li>Ищем в <b>/cron.php</b> <pre class="prettyprint linenums">} elseif(\$cronmode == "antivirus") {</pre> и ставим выше <pre class="prettyprint linenums">} elseif(\$cronmode == "telegram") {
            include_once (DLEPlugins::Check(ENGINE_DIR . "/ajax/maharder/telegram/cronadd.php"));
            die ("done");
</pre></li>
							<li>Удаляем install.php с корня сайта</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="update13">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Обновление
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Замените просто все файлы с заменой</li>
                            <li>Открываем <b>/cron.php</b> и ищем <pre class="prettyprint linenums">\$allow_cron = 0;</pre> и меняем значение на <pre class="prettyprint linenums">\$allow_cron = 1;</pre></li>
							<li>Ищем в <b>/cron.php</b> <pre class="prettyprint linenums">} elseif(\$cronmode == "antivirus") {</pre> и ставим выше <pre class="prettyprint linenums">} elseif(\$cronmode == "telegram") {
            include(ENGINE_DIR . "/ajax/maharder/telegram/cronadd.php");
            die ("done");
</pre></li>
                            <li>Запустить <a href="{$_SERVER['PHP_SELF']}?action=update" target="_blank">этот скрипт  <i class="fas fa-external-link-alt"></i></a></li>
                            <li>Удалить файл install.php с сервера</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="update12">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Обновление
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Замените просто все файлы с заменой</li>
                            <li>Открываем <b>/cron.php</b> и ищем <pre class="prettyprint linenums">\$allow_cron = 0;</pre> и меняем значение на <pre class="prettyprint linenums">\$allow_cron = 1;</pre></li>
							<li>Ищем в <b>/cron.php</b> <pre class="prettyprint linenums">} elseif(\$cronmode == "antivirus") {</pre> и ставим выше <pre class="prettyprint linenums">} elseif(\$cronmode == "telegram") {
            include(ENGINE_DIR . "/ajax/maharder/telegram/cronadd.php");
            die ("done");
</pre></li>
                            <li>Запустить <a href="{$_SERVER['PHP_SELF']}?action=update" target="_blank">этот скрипт  <i class="fas fa-external-link-alt"></i></a></li>
                            <li>Удалить файл install.php с сервера</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="update11">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Обновление
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Замените просто все файлы с заменой</li>
                            <li>Запустить <a href="{$_SERVER['PHP_SELF']}?action=update" target="_blank">этот скрипт  <i class="fas fa-external-link-alt"></i></a></li>
                            <li>Удалить файл install.php с сервера</li>
                        </ol>
                    </div>
                    <div class="ui segment tab" data-tab="update101">
                        <h2 class="ui header">
                            <i class="fas fa-list-ol"></i>
                            <div class="content">
                                Обновление
                                <div class="sub header">Обновляемая документация всегда <a href="{$helplink}" target="_blank">здесь  <i class="fas fa-external-link-alt"></i></a></div>
                            </div>
                        </h2>
                        <ol>
                            <li>Замените просто все файлы с заменой</li>
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
    <script src="//static.maxim-harder.de/semantic/js/jquery.js" integrity="sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT" crossorigin="anonymous"></script>
    <script src="/engine/skins/maharder/js/frame.js"></script>
    <script src="/engine/skins/maharder/js/icons.js"></script>
    <script src="//static.maxim-harder.de/semantic/js/prettify.js" integrity="sha384-9luOpjvvELVvMT+TKP6AFimTg5Q6kp0QVdKdK1pN+7OaQnnYe5sDKnb7gctU1v6n" crossorigin="anonymous"></script>
    <script src="//static.maxim-harder.de/semantic/js/run_prettify.js" integrity="sha384-nbv7QmJPtJfSM8Zj+wAaWoGGWcH/SfmcIkvRlkaNEr0dVOQKgFkP8uls2uiVkjy3" crossorigin="anonymous"></script>
    <script src="//static.maxim-harder.de/semantic/js/installpage.js" integrity="sha384-+aPCYH6BOkjVB47rr71yrgeDG1acw++qivrQXhEoonmNvx+ybK1jj71aqrQApBY9" crossorigin="anonymous"></script>
</body>

</html>
HTML;

echo $html;
