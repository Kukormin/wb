<?
//
// В этой папке настроен параметр
// php_admin_value mbstring.func_overload 0
// для корректной работы phpoffice
//

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

@set_time_limit(0);
ini_set('memory_limit', '2048M');

echo '<pre>';

$agent = $_GET['agent'] == 'Y';

// Импорт дефицита
\Local\Import\Service::deficit($agent);
//$fn = $_SERVER['DOCUMENT_ROOT'] . '/_import/deficit/2018_10_08.xls';
//\Local\Import\Parser::deficit($fn, $log);

echo '</pre>';