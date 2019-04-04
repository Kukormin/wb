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

$productHref = \Local\Main\Products::getHref($product, $collection);

$baseTitle = \Local\Main\Products::getName($product);
$APPLICATION->SetTitle($baseTitle);
$APPLICATION->AddChainItem($brand['NAME'], '/brands/' . $brand['ID'] . '/');
$APPLICATION->AddChainItem($collection['NAME'], '/brands/' . $brand['ID'] . '/' . $collection['ID'] . '/');
$APPLICATION->AddChainItem($baseTitle, $productHref);

/** @var string $page */
if (!$page)
{
    //
    // Картинка
    //
    $xmlIdPath = floor($product['XML_ID'] / 10000) * 10000;
    ?><img class="fr" src="//img2.wbstatic.net/large/new/<?= $xmlIdPath ?>/<?= $product['XML_ID'] ?>-1.jpg" /><?
}

$pages = [
    '' => 'Характеристики',
	'stocks' => 'Остатки',
    'sales' => 'Продажи',
    'graphs' => 'Графики',
];
include('_pages_menu.php');