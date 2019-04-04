<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$collectionId = $_REQUEST['id'];
$collection = \Local\Main\Collections::getById($collectionId);
if (!$collection)
	return;

$productId = $_REQUEST['pid'];
$product = \Local\Main\Products::getById($productId);
if (!$product)
	return;

$APPLICATION->SetTitle($product['NAME']);
$APPLICATION->AddChainItem($collection['NAME'], '/fin/' . $collectionId . '/');
$APPLICATION->AddChainItem($product['NAME']);

$offerIds = [];
$offers = \Local\Main\Offers::getByProduct($product['ID']);
foreach ($offers['ITEMS'] as $offer)
	$offerIds[] = $offer['ID'];

$stocks = \Local\Main\Reports::getStocksByOffers($offerIds);
$uln = \Local\Main\Reports::getUlnByOffers($offerIds);
$sales = \Local\Main\Sales::getSummarySalesByOffers($offerIds);

$type = 'offers';
include('_fin.php');