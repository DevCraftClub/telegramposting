<?php

use Twig\TwigFilter;
use jblond\TwigTrans\Translation;
use Twig\Extension\DebugExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twig\Environment;
use Twig\Extra\Cache\CacheExtension;
use Twig\Extra\Cache\CacheRuntime;
use Twig\Extra\CssInliner\CssInlinerExtension;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\Inky\InkyExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

if (!defined('DATALIFEENGINE')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../../../');
	die("Hacking attempt!");
}

global $lang;

require_once DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/_includes/extras/paths.php');
define('THIS_HOST', $_SERVER['HTTP_HOST']);
define('THIS_SELF', $_SERVER['PHP_SELF']);
define('URL', (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http') . '://' . THIS_HOST . '/engine/inc');

require_once DLEPlugins::Check(ENGINE_DIR . '/inc/include/functions.inc.php');
require_once DLEPlugins::Check(ENGINE_DIR . '/skins/default.skin.php');
require_once DLEPlugins::Check(ENGINE_DIR . '/data/config.php');
include_once DLEPlugins::Check(MH_ADMIN . '/modules/admin/links.php');

$loader = new FilesystemLoader(MH_ADMIN . '/templates');

$langCode = 'ru_RU';
putenv("LC_ALL=$langCode.UTF-8");
if (setlocale(LC_ALL, "$langCode.UTF-8", $langCode, 'ru') === false) {
	LogGenerator::generate_log('MHAdmin', 'index.php', sprintf('Языковой код %s не найден', $langCode));
}

$localDir = MH_ADMIN . '/_locales';
if (!mkdir($localDir . '/' . $langCode, 0777, true) && !is_dir($localDir)) {
	LogGenerator::generate_log('MHAdmin', 'index.php', sprintf('Папка "%s" не могла быть создана', $localDir));
}

bindtextdomain("MHAdmin", $localDir);
textdomain("MHAdmin");

$debug = true;

$twigConfigDebug = [
	'cache'       => false,
	'debug'       => true,
	'auto_reload' => true
];
$twigConfig = ['cache' => MH_ADMIN . '/_cache'];

if ($debug) $twigConfig = array_merge($twigConfig, $twigConfigDebug);

$mh_template = new Environment($loader, $twigConfig);

$filter = new TwigFilter('trans', function($context, $string) {
	return Translation::transGetText($string, $context);
},                       ['needs_context' => true]);
$mh_template->addFilter($filter);

$mh_template->addExtension(new MobileDetectExtension());
$mh_template->addExtension(new DeclineExtension());
$mh_template->addExtension(new AdminUrlExtension());
$mh_template->addExtension(new MarkdownExtension());
$mh_template->addExtension(new CacheExtension());
$mh_template->addExtension(new IntlExtension());
$mh_template->addExtension(new CssInlinerExtension());
$mh_template->addExtension(new StringExtension());
$mh_template->addExtension(new HtmlExtension());
$mh_template->addExtension(new InkyExtension());
$mh_template->addExtension(new Translation());
if ($debug) $mh_template->addExtension(new DebugExtension());
$mh_template->addRuntimeLoader(new class implements RuntimeLoaderInterface {
	public function load($class) {
		if (MarkdownRuntime::class === $class) {
			return new MarkdownRuntime(new DefaultMarkdown());
		}
	}
});
$mh_template->addRuntimeLoader(new class implements RuntimeLoaderInterface {
	public function load($class) {
		if (CacheRuntime::class === $class) {
			return new CacheRuntime(new TagAwareAdapter(new FilesystemAdapter()));
		}
	}
});


$breadcrumbs = [
	[
		'name' => $links['index']['name'],
		'url'  => $links['index']['href'],
	],
];

