<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$offers = [
	'ITEMS' => [],
];
$products = \Local\Main\Products::getByCollection($collection['ID']);
foreach ($products['ITEMS'] as $product)
{
	if ($product['DISABLE'])
		continue;

	$productIds[] = $product['ID'];
	$items = \Local\Main\Offers::getByProduct($product['ID']);
	foreach ($items['ITEMS'] as $id => $offer)
	{
		$offers['ITEMS'][$id] = $offer;
	}
}

$filter = [
	'UF_COLLECTION' => $collection['ID'],
];

$showProduct = true;
$showOffer = true;
include('_stocks.php');