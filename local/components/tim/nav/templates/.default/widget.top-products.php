<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table>
	<colgroup width="250">
	<colgroup width="100">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Название</th>
		<th>Артикул</th>
		<th>Продажи, ₽</th>
		<th>Количество</th>
		<th>Профит1, ₽</th>
		<th>Профит2, ₽</th>
	</tr>
	</thead>

	<tbody><?

	$sales = \Local\Main\Sales::getTopProducts('SALES', 'SUM', false, '-1 month');
	foreach ($sales as $item)
	{
		$product = \Local\Main\Products::getById($item['UF_PRODUCT']);
		$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
		$productA = \Local\Main\Products::getA($product, true);

		?>
		<tr<?= $trClass ?>>
		<td class="tal"><?= $productA ?></td>
		<td><?= $product['CODE'] ?></td>
		<td class="tar"><?= number_format($item['SUM'], 2, ',', ' ') ?></td>
		<td><?= $item['CNT'] ?></td>
		<td class="tar"><?= number_format($item['PRE_MARGIN'], 2, ',', ' ') ?></td>
		<td class="tar"><?= number_format($item['MARGIN'], 2, ',', ' ') ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?