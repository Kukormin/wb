<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Финансы");?>

<?
if (\Local\System\User::isAdmin())
	$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'fin.collection']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>