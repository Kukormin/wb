<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$q = htmlspecialchars($_GET['q']);

?>
<div class="container">
	<p>
	<form action="/search/" method="get">
		<input class="search" type="text" name="q" value="<?= $q ?>"/>
		<input type="submit" value="Поиск" />
	</form>
	</p>
</div><?

$l = strlen($q);
if ($l <= 0)
{
	return;
}

if ($l < 2)
{
	?><h3 class="page-title">Короткий запрос</h3><?

	return;
}

$products = \Local\Main\Products::getAll();
$fProducts = [];
foreach ($products['ITEMS'] as $product)
{
	if ($product['DISABLE'])
		continue;

	if ($product['CODE'] === $q || $product['XML_ID'] === $q)
	{
		$href = \Local\Main\Products::getHref($product);
		LocalRedirect($href);
	}

	if (strpos($product['CODE'], $q) !== false ||
		strpos($product['XML_ID'], $q) !== false ||
		strpos($product['ARTICLE_IMT'], $q) !== false ||
		strpos($product['ARTICLE_COLOR'], $q) !== false)
	{
		$fProducts[] = $product;
	}
}

$offer = \Local\Main\Offers::getByBar($q);
if ($offer)
{
	$href = \Local\Main\Offers::getHref($offer);
	LocalRedirect($href);
}

$offers = \Local\Main\Offers::getAll();
$fOffers = [];
foreach ($offers['ITEMS'] as $offer)
{
	if ($offer['CODE'] === $q)
	{
		$href = \Local\Main\Offers::getHref($offer);
		LocalRedirect($href);
	}

	if (strpos($offer['CODE'], $q) !== false ||
		strpos($offer['BAR'], $q) !== false ||
		strpos($offer['SIZE'], $q) !== false)
	{
		$fOffers[] = $offer;
	}
}

if (!$fProducts && !$fOffers)
{
	?><h3 class="page-title">Ничего не найдено</h3><?

	return;
}

if ($fProducts)
{
	?>
	<h3 class="page-title">Товары</h3>
	<table class="fix search-table">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Название</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
		<th>Артикул ИМТ</th>
		<th>Артикул цвета</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($fProducts as $product)
	{
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';

		$code = \Local\System\Utils::hl($product['CODE'], $q);
		$xmlId = \Local\System\Utils::hl($product['XML_ID'], $q);
		$imt = \Local\System\Utils::hl($product['ARTICLE_IMT'], $q);
		$color = \Local\System\Utils::hl($product['ARTICLE_COLOR'], $q);

		$productA = \Local\Main\Products::getA($product);

		?>
		<tr<?= $trClass ?>>
			<td class="tal"><?= $productA ?></td>
			<td><?= $code ?></td>
			<td><?= $xmlId ?></td>
			<td><?= $imt ?></td>
			<td><?= $color ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?
}

if ($fOffers)
{
	?>
	<h3 class="page-title">Предложения</h3>
	<table class="fix search-table">
	<colgroup width="300">
	<colgroup width="80">
	<colgroup width="120">
	<colgroup width="220">
	<thead>
	<tr>
		<th>Название</th>
		<th>Размер</th>
		<th>Артикул</th>
		<th>Штрихкод</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($fOffers as $offer)
	{
		$code = \Local\System\Utils::hl($offer['CODE'], $q);
		$bar = \Local\System\Utils::hl($offer['BAR'], $q);
		$size = \Local\System\Utils::hl($offer['SIZE'], $q);
		$offerA = \Local\Main\Offers::getA($offer);

		?>
		<tr>
			<td class="tal"><?= $offerA ?></td>
			<td><?= $size ?></td>
			<td><?= $code ?></td>
			<td><?= $bar ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?
}
