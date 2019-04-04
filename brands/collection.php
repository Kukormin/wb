<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Коллекции");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'collection']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>