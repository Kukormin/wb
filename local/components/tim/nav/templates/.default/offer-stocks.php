<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [
	'UF_OFFER' => $offer['ID'],
];

$offers = [
	'ITEMS' => [
		$offer['ID'] => $offer,
	],
];

$showProduct = false;
$showOffer = false;
include('_stocks.php');
