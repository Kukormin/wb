<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table>
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Коллекция</th>
		<th>Продажи, ₽</th>
		<th>Количество</th>
		<th>Профит1, ₽</th>
		<th>Профит2, ₽</th>
	</tr>
	</thead>

	<tbody><?

	$sales = \Local\Main\Sales::getTopProducts('SALES', 'SUM', true, '-1 month');
	foreach ($sales as $item)
	{
		$collection = \Local\Main\Collections::getById($item['COLLECTION']);
		?>
		<tr>
		<td class="tal"><a
					href="/brands/<?= $collection['BRAND'] ?>/<?= $collection['ID'] ?>/"><?= $collection['NAME'] ?></a>
		</td>
		<td class="tar"><?= number_format($item['SUM'], 2, ',', ' ') ?></td>
		<td><?= $item['CNT'] ?></td>
		<td class="tar"><?= number_format($item['PRE_MARGIN'], 2, ',', ' ') ?></td>
		<td class="tar"><?= number_format($item['MARGIN'], 2, ',', ' ') ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?