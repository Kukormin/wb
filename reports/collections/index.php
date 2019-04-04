<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Продажи по коллекциям");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'sales.bycollections']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>