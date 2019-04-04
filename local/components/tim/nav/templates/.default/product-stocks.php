<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [
	'UF_PRODUCT' => $product['ID'],
];

$offers = \Local\Main\Offers::getByProduct($product['ID']);

$showProduct = false;
$showOffer = true;
include('_stocks.php');
