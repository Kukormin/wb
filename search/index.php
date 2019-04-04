<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'search']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");