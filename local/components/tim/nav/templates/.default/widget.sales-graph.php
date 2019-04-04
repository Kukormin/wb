<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$filter = [];

$hideStores = true;
$height = 500;

$date = new \Bitrix\Main\Type\DateTime();
$date->add('-1 month');
$dateFromF = $date->format('c');

include ('_graphs.php');