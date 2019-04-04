<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$stocks = \Local\Main\Reports::getStocksByCollections();
$uln = \Local\Main\Reports::getUlnByCollections();
$sales = \Local\Main\Sales::getSummarySalesByCollection();

$type = 'collections';
include('_fin.php');