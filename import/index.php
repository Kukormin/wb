<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Импорты");?>

<?
if (\Local\System\User::isAdmin())
	$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'import']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>