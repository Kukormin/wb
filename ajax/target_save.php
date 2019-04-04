<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!\Local\System\User::isLogged()) die();

$clearCache = false;
foreach ($_REQUEST['data'] as $offerId => $ar)
{
	foreach ($ar as $storeId => $value)
	{
		$item = \Local\Main\Stocks::getTargetItem($offerId, $storeId);
		if ($item['ID'])
			\Local\Main\Stocks::update($item['ID'], $value);
		else
			\Local\Main\Stocks::add($offerId, $storeId, $value, 1);

		$clearCache = true;
	}
}

if ($clearCache)
	\Local\Main\Stocks::getAllTarget(true);