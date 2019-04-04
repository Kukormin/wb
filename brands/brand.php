<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бренды");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'brand']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>