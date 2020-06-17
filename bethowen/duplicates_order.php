<?

/* cron script for duplicates from 1C */

$_SERVER['DOCUMENT_ROOT'] = '/var/www/bethowen.ru/';
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/highloadblock.php');

include_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/helpers/CheckDublicates.php');

define('TIME_SELECT_FROM', strval(intval(time()) - (60*60*1)));

$path = $_SERVER['DOCUMENT_ROOT'] . '/test/log_duplicate_' . date('ymd', time()) . '.log';
$duplicates = new \Bethowen\Helpers\CheckDublicates(true, true, $path);

$duplicates->detectedDublicate();