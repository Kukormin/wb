<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Топ");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'top']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>