<?
define("PULL_AJAX_INIT", true);
define('PUBLIC_AJAX_MODE', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC","Y");
define('BX_SECURITY_SESSION_READONLY', true);
define('BX_SECURITY_SESSION_VIRTUAL', true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/helpers/url_to_buy.php');

global $USER;

$buy_url = new \Bethowen\Helpers\BuyByUrl(false);

if(isset($_GET['items'])) {
    $buy_url->addToBusketUrl('items');
}
if(isset($_GET['codes'])) {
    $buy_url->addToBusketUrl('codes');
}

if(CUser::IsAuthorized() == false) {
    $key = 'data/auth_flag/' . sha1($_SERVER['REMOTE_ADDR']);

    $cache = new \Bethowen\Helpers\FasterClaster('bbu');
    $cache->setDataTimout($key, true, 3600);
    $cache->close();

    LocalRedirect('/');
}
