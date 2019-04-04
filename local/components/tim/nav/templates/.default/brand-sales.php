<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$collectionIds = [];

$collections = \Local\Main\Collections::getAll();
foreach ($collections['ITEMS'] as $collection)
{
	if ($collection['BRAND'] != $brandId)
		continue;

	$collectionIds[] = $collection['ID'];
}

$filter = [
	'UF_COLLECTION' => $collectionIds,
];

$needDateInterval = true;
$showPrices = false;
include('_sales.php');