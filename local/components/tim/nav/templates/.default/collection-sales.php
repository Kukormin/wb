<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [
	'UF_COLLECTION' => $collection['ID'],
];

$showPrices = false;
include('_sales.php');