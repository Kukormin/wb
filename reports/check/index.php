<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проверка на корректность");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'check']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>