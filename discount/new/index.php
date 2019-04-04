<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новая скидка");?>

<?
if (\Local\System\User::isAdmin())
	$APPLICATION->IncludeComponent('tim:nav', '', ['PAGE' => 'discount.new']);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>