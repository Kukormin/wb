<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$productIds = [];

$collections = \Local\Main\Collections::getAll();
foreach ($collections['ITEMS'] as $collection)
{
	if ($collection['BRAND'] != $brandId)
		continue;

	$products = \Local\Main\Products::getByCollection($collection['ID']);
	foreach ($products['ITEMS'] as $product)
	{
		if ($product['DISABLE'])
			continue;

		$productIds[] = $product['ID'];
	}
}

$filter = [
	'UF_PRODUCT' => $productIds,
];

include('_graphs.php');