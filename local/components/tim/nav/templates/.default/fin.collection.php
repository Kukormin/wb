<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$collectionId = $_REQUEST['id'];
$collection = \Local\Main\Collections::getById($collectionId);
if (!$collection)
	return;

$APPLICATION->SetTitle($collection['NAME']);
$APPLICATION->AddChainItem($collection['NAME'], '/fin/' . $collectionId . '/');

$productIds = [];
$products = \Local\Main\Products::getByCollection($collection['ID']);
foreach ($products['ITEMS'] as $product)
{
	if ($product['DISABLE'])
		continue;

	$productIds[] = $product['ID'];
}

$stocks = \Local\Main\Reports::getStocksByProducts($productIds);
$uln = \Local\Main\Reports::getUlnByProducts($productIds);
$sales = \Local\Main\Sales::getSummarySalesByProducts($productIds);

$type = 'products';
include('_fin.php');