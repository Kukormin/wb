<?
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../..');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);
define('BX_NO_ACCELERATOR_RESET', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

\Local\Import\Service::sales(true);