<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [
	'UF_OFFER' => $offer['ID'],
];

$showPrices = true;
include('_sales.php');