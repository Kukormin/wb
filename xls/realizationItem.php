<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!\Local\System\User::isLogged()) die();

@set_time_limit(0);
ini_set('memory_limit', '2048M');

$item = \Local\Main\Realization::getByXmlId($_REQUEST['id']);
if (!$item)
	return;

echo '<pre>';

\Local\Import\Service::realizationItem($item);

echo '</pre>';