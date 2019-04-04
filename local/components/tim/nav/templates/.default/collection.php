<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$brandId = $_REQUEST['id'];
$brand = \Local\Main\Brands::getById($brandId);
if (!$brand)
	return;

$collectionId = $_REQUEST['col'];
$collection = \Local\Main\Collections::getById($collectionId);
if (!$collection)
    return;

$baseTitle = $collection['NAME'];
$APPLICATION->SetTitle($baseTitle);
$APPLICATION->AddChainItem($brand['NAME'], '/brands/' . $brand['ID'] . '/');
$APPLICATION->AddChainItem($baseTitle, '/brands/' . $brand['ID'] . '/' . $collection['ID'] . '/');

$pages = [
    '' => 'Список товаров',
	'stocks' => 'Остатки',
	'sales' => 'Продажи',
	'graphs' => 'Графики',
];
include('_pages_menu.php');
