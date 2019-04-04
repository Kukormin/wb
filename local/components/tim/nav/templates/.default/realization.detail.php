<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$realization = \Local\Main\Realization::getByXmlId($_REQUEST['id']);
if (!$realization)
	return;

$baseTitle = $realization['NAME'];
$APPLICATION->SetTitle($baseTitle);
$APPLICATION->AddChainItem($baseTitle, '/reports/realization/' . $realization['XML_ID'] . '/');

$fn = $realization['XML_ID'] . '.json';
$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/realization/' . $fn;

$pages = [
	'' => 'Информация',
	'counts' => 'Сравнение количества',
	'prices' => 'Сравнение цен',
];
include('_pages_menu.php');
