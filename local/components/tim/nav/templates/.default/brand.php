<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$brandId = $_REQUEST['id'];
$brand = \Local\Main\Brands::getById($brandId);
if (!$brand)
	return;

$baseTitle = $brand['NAME'];
$APPLICATION->SetTitle($baseTitle);
$APPLICATION->AddChainItem($baseTitle, '/brands/' . $brand['ID'] . '/');

$pages = [
	'' => 'Коллекции',
	'sales' => 'Продажи',
	'graphs' => 'Графики',
];
include('_pages_menu.php');
