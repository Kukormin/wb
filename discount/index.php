<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Скидки");?>

<?
if (\Local\System\User::isAdmin())
	$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'discount']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>