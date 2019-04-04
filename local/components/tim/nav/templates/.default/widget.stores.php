<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$report = \Local\Main\Reports::getStocksByStores();

?>
	<table>
		<thead>
		<tr>
			<th>Склад</th>
			<th>Кол-во</th>
			<th>Сумма, ₽</th>
			<th>С учетом комиссии WB, ₽</th>
		</tr>
		</thead>

		<tbody><?

		$cnt = 0;
		$price = 0;
		$wb = 0;

		foreach ($report as $storeId => $item)
		{
			$store = \Local\Main\Stores::getById($storeId);
			?>
			<tr>
				<td class="tal"><?= $store['NAME'] ?></td>
				<td><?= $item['CNT'] ?></td>
				<td class="tar"><?= number_format($item['PRICE'], 0, ',', ' ') ?></td>
				<td class="tar"><?= number_format($item['PRICE'] - $item['WB'], 0, ',', ' ') ?></td>
			</tr><?

			$cnt += $item['CNT'];
			$price += $item['PRICE'];
			$wb += $item['WB'];
		}
		?>
			<tr class="summary">
				<td class="tar">Итого:</td>
				<td><?= $cnt ?></td>
				<td class="tar"><?= number_format($price, 0, ',', ' ') ?></td>
				<td class="tar"><?= number_format($price - $wb, 0, ',', ' ') ?></td>
			</tr>
		</tbody>
	</table><?