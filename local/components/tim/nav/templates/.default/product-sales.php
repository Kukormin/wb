<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [
	'UF_PRODUCT' => $product['ID'],
];

$showPrices = true;
include('_sales.php');