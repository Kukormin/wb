<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$priceProductF = number_format($product['PRICE'], 2, ',', ' ');
$costF = '';
if ($offer['COST'])
	$costF = number_format($offer['COST'], 2, ',', ' ');
$priceF = '';
if ($offer['PRICE'])
	$priceF = number_format($offer['PRICE'], 2, ',', ' ');

$brand = \Local\Main\Brands::getById($product['BRAND']);

//
// Характеристики
//
?>
<div class="container">
    <h2>Характеристики</h2>
    <dl class="props props-offer">
		<dt>Бренд:</dt>
		<dd><?= $brand['NAME'] ?></dd>
        <dt>Размер:</dt>
        <dd><?= $offer['SIZE'] ?></dd>
		<dt>Артикул:</dt>
		<dd><?= $offer['ARTICLE'] ?></dd>
        <dt>Код размера:</dt>
        <dd><?= $offer['CODE'] ?></dd>
        <dt>Штрихкод:</dt>
        <dd><?= $offer['BAR'] ?></dd>
        <dt>Предложение в админке:</dt>
        <dd><a href="<?= \Local\Main\Offers::getAdminHref($offer) ?>"><?= $offer['ID'] ?></a></dd>
        <dt>Цена (товара):</dt>
        <dd><?= $priceProductF ?></dd>
        <dt>Скидка:</dt>
        <dd><?= $product['DISCOUNT'] ?></dd>
        <dt>Себестоимость:</dt>
        <dd><?= $costF ?></dd>
        <dt>Оптовая цена:</dt>
        <dd><?= $priceF ?></dd>
    </dl>
</div><?
