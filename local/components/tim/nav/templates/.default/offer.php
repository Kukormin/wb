<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$brandId = $_REQUEST['id'];
$brand = \Local\Main\Brands::getById($brandId);
if (!$brand)
	return;

$collectionId = $_REQUEST['col'];
$collection = \Local\Main\Collections::getById($collectionId);
if (!$collection)
	return;

$productId = $_REQUEST['pid'];
$product = \Local\Main\Products::getById($productId);
if (!$product)
	return;

$offerId = $_REQUEST['oid'];
$offer = \Local\Main\Offers::getById($offerId);
if (!$offer)
    return;

$productHref = \Local\Main\Products::getHref($product, $collection);
$offerHref = \Local\Main\Offers::getHref($offer, $product, $collection);

$productName = \Local\Main\Products::getName($product);
$baseTitle = $offer['NAME'];
$APPLICATION->SetTitle($offer['NAME']);
$APPLICATION->AddChainItem($brand['NAME'], '/brands/' . $brand['ID'] . '/');
$APPLICATION->AddChainItem($collection['NAME'], '/brands/' . $brand['ID'] . '/' . $collection['ID'] . '/');
$APPLICATION->AddChainItem($productName, $productHref);
$APPLICATION->AddChainItem($baseTitle, $offerHref);

$pages = [
    '' => 'Характеристики',
	'stocks' => 'Остатки',
    'sales' => 'Продажи',
	'graphs' => 'Графики',
];
include('_pages_menu.php');