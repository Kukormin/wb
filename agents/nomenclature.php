<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!\Local\System\User::isLogged()) die();

@set_time_limit(0);
ini_set('memory_limit', '2048M');

echo '<pre>';

\Local\Import\Service::nomenclature();

echo '</pre>';