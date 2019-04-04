<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бренды");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'brands.index']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>