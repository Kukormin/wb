<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$items = \Local\Main\Realization::getAll();

for ($i = 0; $i < 2; $i++)
{
	$title = $i ? 'Еженедельные' : 'Ежемесячные';
	?>
	<h2 class="page-title"><?= $title ?></h2>
	<table>
	<thead>
	<tr>
		<th>Даты</th>
		<th>Продажи, руб</th>
		<th>Продажи себестоимость, руб</th>
		<th>Вознаграждение, руб</th>
		<th>Средний процент скидки</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($items['ITEMS'] as $item)
	{
		if (!$i && $item['WEEK'])
			continue;
		if ($i && !$item['WEEK'])
			continue;

		$salesF = number_format($item['SALES'], 2, ',', ' ');
		$costF = number_format($item['COST'], 2, ',', ' ');
		$feeF = number_format($item['FEE'], 2, ',', ' ');
		$discountF = number_format($item['DISCOUNT'], 2, ',', ' ');

		?>
		<tr>
		<td><a href="/reports/realization/<?= $item['XML_ID'] ?>/"><?= $item['NAME'] ?></a></td>
		<td><?= $salesF ?></td>
		<td><?= $costF ?></td>
		<td><?= $feeF ?></td>
		<td><?= $discountF ?></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?
}
