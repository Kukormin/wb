<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ini_set('memory_limit', '2048M');

/** @var array $stocks */
/** @var array $uln */
/** @var array $sales */

$sum = [
	'UF_ADMISSION' => 0,
	'UF_ADMISSION_PRICE' => 0,
	'UF_ORDER' => 0,
	'UF_ORDER_PRICE' => 0,
	'UF_RETURN' => 0,
	'UF_RETURN_PRICE' => 0,
	'UF_SALES' => 0,
	'UF_SALES_PRICE' => 0,
	'UF_REMISSION' => 0,
	'UF_REMISSION_PRICE' => 0,
	'ULN' => 0,
	'ULN_PRICE' => 0,
	'STOCKS' => 0,
	'STOCKS_PRICE' => 0,
];
foreach ($sales as $item)
{
	$sum['UF_ADMISSION'] += $item['UF_ADMISSION'];
	$sum['UF_ADMISSION_PRICE'] += $item['UF_ADMISSION_PRICE'];
	$sum['UF_ORDER'] += $item['UF_ORDER'];
	$sum['UF_ORDER_PRICE'] += $item['UF_ORDER_PRICE'];
	$sum['UF_RETURN'] += $item['UF_RETURN'];
	$sum['UF_RETURN_PRICE'] += $item['UF_RETURN_PRICE'];
	$sum['UF_SALES'] += $item['UF_SALES'];
	$sum['UF_SALES_PRICE'] += $item['UF_SALES_PRICE'];
	$sum['UF_REMISSION'] += $item['UF_REMISSION'];
	$sum['UF_REMISSION_PRICE'] += $item['UF_REMISSION_PRICE'];

	if ($type == 'collections')
		$key = $item['COLLECTION'];
	elseif ($type == 'products')
		$key = $item['PRODUCT'];
	elseif ($type == 'offers')
		$key = $item['OFFER'];

	$sum['ULN'] += $uln[$key]['CNT'];
	$sum['ULN_COST'] += $uln[$key]['COST'];
	$sum['ULN_PRICE'] += $uln[$key]['PRICE'] - $uln[$key]['WB'];
	$sum['STOCKS'] += $stocks[$key]['CNT'];
	$sum['STOCKS_COST'] += $stocks[$key]['COST'];
	$sum['STOCKS_PRICE'] += $stocks[$key]['PRICE'] - $stocks[$key]['WB'];
}

$params = $APPLICATION->GetCurParam();
if ($params)
	$params = '?' . $params;

?>
	<table class="fix sales-table"><?

	if ($type == 'collections')
	{
		?>
		<colgroup width="100">
		<colgroup width="300"><?
	}
	elseif ($type == 'products')
	{
		?>
		<colgroup width="300">
		<colgroup width="100"><?
	}
	elseif ($type == 'offers')
	{
		?>
		<colgroup width="300">
		<colgroup width="100"><?
	}

	?>
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="80">
	<colgroup width="70">
	<colgroup width="80">
	<colgroup width="80">
	<thead>
	<tr><?

		if ($type == 'collections')
		{
			?>
			<th rowspan="2">Бренд</th>
			<th rowspan="2">Коллекция</th><?
		}
		elseif ($type == 'products')
		{
			?>
			<th rowspan="2">Товар</th>
			<th rowspan="2">Артикул</th><?
		}
		elseif ($type == 'offers')
		{
			?>
			<th rowspan="2">Предложение</th>
			<th rowspan="2">Размер</th><?
		}

		?>
		<th colspan="2">Поступления</th>
		<th colspan="2">Заказано</th>
		<th colspan="2">Возвраты<br />до оплаты</th>
		<th colspan="2">Продажи<br />по оплатам</th>
		<th colspan="2">Возвраты cо склада<br />поставщику</th>
		<th colspan="3">Остатки<br />Улн</th>
		<th colspan="3">Остатки<br />на складах WB</th>
	</tr>
	<tr>
		<th>шт.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>Себест.</th>
		<th>руб.</th>
		<th>шт.</th>
		<th>Себест.</th>
		<th>руб.</th>
	</tr>
	</thead>

	<tbody><?

	$cnt1 = $sum['UF_ADMISSION'] ? $sum['UF_ADMISSION'] : '';
	$cnt2 = $sum['UF_ORDER'] ? $sum['UF_ORDER'] : '';
	$cnt3 = $sum['UF_RETURN'] ? $sum['UF_RETURN'] : '';
	$cnt4 = $sum['UF_SALES'] ? $sum['UF_SALES'] : '';
	$cnt5 = $sum['UF_REMISSION'] ? $sum['UF_REMISSION'] : '';
	$cnt6 = $sum['ULN'] ? $sum['ULN'] : '';
	$cnt7 = $sum['STOCKS'] ? $sum['STOCKS'] : '';

	$price1 = $sum['UF_ADMISSION_PRICE'] ? number_format($sum['UF_ADMISSION_PRICE'], 0, ',', ' ') : '';
	$price2 = $sum['UF_ORDER_PRICE'] ? number_format($sum['UF_ORDER_PRICE'], 0, ',', ' ') : '';
	$price3 = $sum['UF_RETURN_PRICE'] ? number_format($sum['UF_RETURN_PRICE'], 0, ',', ' ') : '';
	$price4 = $sum['UF_SALES_PRICE'] ? number_format($sum['UF_SALES_PRICE'], 0, ',', ' ') : '';
	$price5 = $sum['UF_REMISSION_PRICE'] ? number_format($sum['UF_REMISSION_PRICE'], 0, ',', ' ') : '';
	$price6 = $sum['ULN_PRICE'] ? number_format($sum['ULN_COST'], 0, ',', ' ') : '';
	$price6a = $sum['ULN_PRICE'] ? number_format($sum['ULN_PRICE'], 0, ',', ' ') : '';
	$price7 = $sum['STOCKS_PRICE'] ? number_format($sum['STOCKS_COST'], 0, ',', ' ') : '';
	$price7a = $sum['STOCKS_PRICE'] ? number_format($sum['STOCKS_PRICE'], 0, ',', ' ') : '';
	?>
	<tr class="summary">
		<td colspan="2" class="tar">Итого:</td>
		<td><?= $cnt1 ?></td>
		<td class="tar"><?= $price1 ?></td>
		<td><?= $cnt2 ?></td>
		<td class="tar"><?= $price2 ?></td>
		<td><?= $cnt3 ?></td>
		<td class="tar"><?= $price3 ?></td>
		<td><?= $cnt4 ?></td>
		<td class="tar"><?= $price4 ?></td>
		<td><?= $cnt5 ?></td>
		<td class="tar"><?= $price5 ?></td>
		<td><?= $cnt6 ?></td>
		<td class="tar"><?= $price6 ?></td>
		<td class="tar"><?= $price6a ?></td>
		<td><?= $cnt7 ?></td>
		<td class="tar"><?= $price7 ?></td>
		<td class="tar"><?= $price7a ?></td>
	</tr><?

	foreach ($sales as $item)
	{
		$cnt1 = $item['UF_ADMISSION'] ? $item['UF_ADMISSION'] : '';
		$cnt2 = $item['UF_ORDER'] ? $item['UF_ORDER'] : '';
		$cnt3 = $item['UF_RETURN'] ? $item['UF_RETURN'] : '';
		$cnt4 = $item['UF_SALES'] ? $item['UF_SALES'] : '';
		$cnt5 = $item['UF_REMISSION'] ? $item['UF_REMISSION'] : '';

		$price1 = $item['UF_ADMISSION_PRICE'] ? number_format($item['UF_ADMISSION_PRICE'], 0, ',', ' ') : '';
		$price2 = $item['UF_ORDER_PRICE'] ? number_format($item['UF_ORDER_PRICE'], 0, ',', ' ') : '';
		$price3 = $item['UF_RETURN_PRICE'] ? number_format($item['UF_RETURN_PRICE'], 0, ',', ' ') : '';
		$price4 = $item['UF_SALES_PRICE'] ? number_format($item['UF_SALES_PRICE'], 0, ',', ' ') : '';
		$price5 = $item['UF_REMISSION_PRICE'] ? number_format($item['UF_REMISSION_PRICE'], 0, ',', ' ') : '';

		if ($type == 'collections')
		{
			$key = $item['COLLECTION'];
			$collection = \Local\Main\Collections::getById($item['COLLECTION']);
			$brand = \Local\Main\Brands::getById($collection['BRAND']);
			?>
			<tr>
			<td class="tal"><a href="/brands/<?= $brand['ID'] ?>/"><?= $brand['NAME'] ?></a></td>
			<td class="tal"><a href="/fin/<?= $collection['ID'] ?>/"><?= $collection['NAME'] ?></a></td><?
		}
		elseif ($type == 'products')
		{
			$key = $item['PRODUCT'];
			$product = \Local\Main\Products::getById($item['PRODUCT']);
			$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
			?>
			<tr<?= $trClass ?>>
			<td class="tal"><a href="/fin/<?= $collection['ID'] ?>/<?= $product['ID'] ?>/"><?= $product['NAME'] ?></a></td>
			<td><?= $product['CODE'] ?></td><?
		}
		elseif ($type == 'offers')
		{
			$key = $item['OFFER'];
			$offer = \Local\Main\Offers::getById($item['OFFER']);
			$product = \Local\Main\Products::getById($offer['PRODUCT']);

			$offerA = \Local\Main\Offers::getA($offer, false, $product);
			?>
			<tr>
			<td class="tal"><?= $offerA ?></td>
			<td><?= $offer['SIZE'] ?></td><?
		}

		$cnt6 = $uln[$key]['CNT'] ? $uln[$key]['CNT'] : '';
		$price6 = $uln[$key]['COST'];
		$price6 = $price6 ? number_format($price6, 0, ',', ' ') : '';
		$price6a = $uln[$key]['PRICE'] - $uln[$key]['WB'];
		$price6a = $price6a ? number_format($price6a, 0, ',', ' ') : '';
		$cl6 = $uln[$key]['WARNINGS'] ? ' bgr' : '';

		$cnt7 = $stocks[$key]['CNT'] ? $stocks[$key]['CNT'] : '';
		$price7 = $stocks[$key]['COST'];
		$price7 = $price7 ? number_format($price7, 0, ',', ' ') : '';
		$price7a = $stocks[$key]['PRICE'] - $stocks[$key]['WB'];
		$price7a = $price7a ? number_format($price7a, 0, ',', ' ') : '';
		$cl7 = $stocks[$key]['WARNINGS'] ? ' bgr' : '';

		?>
		<td><?= $cnt1 ?></td>
		<td class="tar"><?= $price1 ?></td>
		<td><?= $cnt2 ?></td>
		<td class="tar"><?= $price2 ?></td>
		<td><?= $cnt3 ?></td>
		<td class="tar"><?= $price3 ?></td>
		<td><?= $cnt4 ?></td>
		<td class="tar"><?= $price4 ?></td>
		<td><?= $cnt5 ?></td>
		<td class="tar"><?= $price5 ?></td>
		<td><?= $cnt6 ?></td>
		<td class="tar<?= $cl6 ?>"><?= $price6 ?></td>
		<td class="tar"><?= $price6a ?></td>
		<td><?= $cnt7 ?></td>
		<td class="tar<?= $cl7 ?>"><?= $price7 ?></td>
		<td class="tar"><?= $price7a ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?