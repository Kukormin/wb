<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$products = \Local\Main\Products::getAll(true);
$offers = \Local\Main\Offers::getAll(true);

$noPrice = [];
$noCollection = [];
$noActive = [];
$noNewBar = [];
$noProduct = [];
$noOffers = [];
$noCost = [];
$disable = [];

foreach ($offers['ITEMS'] as $offer)
{
	$bars = explode(',', $offer['BAR']);
	$ex = false;
	foreach ($bars as $bar)
		if (substr($bar, 0, 1) == '4')
		{
			$ex = true;
			break;
		}
	if (!$ex)
		$noNewBar[] = $offer;

	if (!$offer['PRODUCT'])
		$noProduct[] = $offer;
	else
		$products['ITEMS'][$offer['PRODUCT']]['OFFERS_EX'] = true;

	if (!$offer['COST'] || !$offer['PRICE'])
		$noCost[] = $offer;
}

foreach ($products['ITEMS'] as $product)
{
	if ($product['DISABLE'])
	{
		$disable[] = $product;
		continue;
	}

	if ($product['START_PRICE'] <= 0 || $product['PRICE'] <= 0)
		$noPrice[] = $product;

	if (!$product['COLLECTION'])
		$noCollection[] = $product;

	if (!$product['ACTIVE'])
		$noActive[] = $product;

	if (!$product['OFFERS_EX'])
		$noOffers[] = $product;
}

if ($disable)
{
	?>
	<h2 class="page-title js-show-table">Левые товары (<?= count($disable) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($disable as $product)
	{
		$brand = \Local\Main\Brands::getById($product['BRAND']);

		$trClass = ' class="disable"';
		$productA = \Local\Main\Products::getA($product, true);

		?>
		<tr<?= $trClass ?>>
		<td><?= $brand['NAME'] ?></td>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td><?= $product['XML_ID'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}

if ($noPrice)
{
	?>
	<h2 class="page-title js-show-table">Товары у которых не задана начальная цена (<?= count($noPrice) ?>)</h2>
	<table class="fix hidden">
		<colgroup width="120">
		<colgroup width="300">
		<colgroup width="120">
		<colgroup width="120">
		<colgroup width="80">
		<colgroup width="80">
		<thead>
		<tr>
			<th>Бренд</th>
			<th>Товар</th>
			<th>Артикул</th>
			<th>Номенклатура</th>
			<th>Цена</th>
			<th>Начальная цена</th>
		</tr>
		</thead>

		<tbody><?

		foreach ($noPrice as $product)
		{
			$brand = \Local\Main\Brands::getById($product['BRAND']);
			$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
			$productA = \Local\Main\Products::getA($product, true);

			?>
			<tr<?= $trClass ?>>
			<td><?= $brand['NAME'] ?></td>
			<td class="tal"><?= $productA ?></td>
			<td><?= $product['CODE'] ?></td>
			<td><?= $product['XML_ID'] ?></td>
			<td class="tar"><?= $product['PRICE'] ?></td>
			<td class="tar"><?= $product['START_PRICE'] ?></td>
			</tr><?
		}
		?>
		</tbody>
	</table><?
}

if ($noCollection)
{
	?>
	<h2 class="page-title js-show-table">Товары без принадлежности к коллекции (<?= count($noCollection) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($noCollection as $product)
	{
		$brand = \Local\Main\Brands::getById($product['BRAND']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);

		?>
		<tr<?= $trClass ?>>
		<td><?= $brand['NAME'] ?></td>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td><?= $product['XML_ID'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}

if ($noOffers)
{
	?>
	<h2 class="page-title js-show-table">Товары, у которых нет ни одного предложения (<?= count($noOffers) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($noOffers as $product)
	{
		$brand = \Local\Main\Brands::getById($product['BRAND']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);

		?>
	<tr<?= $trClass ?>>
		<td><?= $brand['NAME'] ?></td>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td><?= $product['XML_ID'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}

if ($noActive)
{
	?>
	<h2 class="page-title js-show-table">Деактивированные товары (<?= count($noActive) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($noActive as $product)
	{
		$brand = \Local\Main\Brands::getById($product['BRAND']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);

		?>
	<tr<?= $trClass ?>>
		<td><?= $brand['NAME'] ?></td>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td><?= $product['XML_ID'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}

if ($noProduct)
{
	?>
	<h2 class="page-title js-show-table">Предложения без привязки к товару (<?= count($noProduct) ?>)</h2>
		<table class="fix hidden">
		<colgroup width="50">
		<colgroup width="300">
		<colgroup width="150">
		<thead>
		<tr>
			<th>ID</th>
			<th>Предложение</th>
			<th>ШК</th>
		</tr>
		</thead>

		<tbody><?

		foreach ($noProduct as $offer)
		{
			$href = \Local\Main\Offers::getAdminHref($offer)
			?>
			<tr>
				<td><?= $offer['ID'] ?></td>
				<td class="tal"><a target="_blank" href="<?= $href ?>"><?= $offer['NAME'] ?></a></td>
				<td><?= $offer['BAR'] ?></td>
			</tr><?
		}
		?>
		</tbody>
	</table><?
}

if ($noCost)
{
	?>
	<h2 class="page-title js-show-table">Предложения, у которых не задана себестоимость (<?= count($noCost) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="150">
	<colgroup width="150">
	<colgroup width="100">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
		<th>Предложение</th>
		<th>ШК</th>
		<th>Себестоимость</th>
		<th>Цена</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($noCost as $offer)
	{
		$product = \Local\Main\Products::getById($offer['PRODUCT']);
		$brand = \Local\Main\Brands::getById($product['BRAND']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);
		$offerA = \Local\Main\Offers::getA($offer, true, $product);

		?>
		<tr<?= $trClass ?>>
			<td><?= $brand['NAME'] ?></td>
			<td class="tal"><?= $productA ?></td>
			<td><?= $product['CODE'] ?></td>
			<td><?= $product['XML_ID'] ?></td>
			<td class="tal"><?= $offerA ?></td>
			<td><?= $offer['BAR'] ?></td>
			<td><?= $offer['COST'] ?></td>
			<td><?= $offer['PRICE'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}

if ($noNewBar)
{
	?>
	<h2 class="page-title js-show-table">Предложения, у которых не обновился штрихкод (<?= count($noNewBar) ?>)</h2>
	<table class="fix hidden">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="300">
	<colgroup width="150">
	<thead>
	<tr>
		<th>Бренд</th>
		<th>Товар</th>
		<th>Артикул</th>
		<th>Номенклатура</th>
		<th>Предложение</th>
		<th>ШК</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($noNewBar as $offer)
	{
		$product = \Local\Main\Products::getById($offer['PRODUCT']);
		$brand = \Local\Main\Brands::getById($product['BRAND']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);
		$offerA = \Local\Main\Offers::getA($offer, true, $product);

		?>
	<tr<?= $trClass ?>>
		<td><?= $brand['NAME'] ?></td>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td><?= $product['XML_ID'] ?></td>
		<td class="tal"><?= $offerA ?></td>
		<td><?= $offer['BAR'] ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?
}


