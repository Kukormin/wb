<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Категории");
?>

<?$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'cat.index']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>