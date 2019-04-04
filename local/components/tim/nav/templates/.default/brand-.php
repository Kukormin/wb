<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$collections = \Local\Main\Collections::getAll();

$from = new \Bitrix\Main\Type\DateTime();
$from->add('-1 months');
$sales = \Local\Main\Sales::getSummarySalesByCollection($from);

$week = new \Bitrix\Main\Type\DateTime();
$week = $week->add('-2 week');
$order = \Local\Main\Sales::getSummarySalesByCollection($week);

?>
	<table class="fix">
		<colgroup width="350">
		<colgroup width="160">
		<colgroup width="160">
		<thead>
		<tr>
			<th>Название</th>
			<th>Продажи за месяц</th>
			<th>Заказы за 2 недели</th>
		</tr>
		</thead>

		<tbody><?

		foreach ($collections['ITEMS'] as $collection)
		{
			if ($collection['BRAND'] != $brandId)
				continue;

			?><tr>
				<td class="tal"><a href="/brands/<?= $brand['ID'] ?>/<?= $collection['ID'] ?>/"><?= $collection['NAME'] ?></a></td>
				<td><?= $sales[$collection['ID']]['UF_SALES'] ?></td>
				<td><?= $order[$collection['ID']]['UF_ORDER'] ?></td>
			</tr><?
		}

		?>
		</tbody>
	</table>
