<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Продажи по реализации");?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'realization.index']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>