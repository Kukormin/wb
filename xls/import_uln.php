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

// Импорт остатков в Ульяновске
\Local\Import\Service::uln($agent);

echo '</pre>';
