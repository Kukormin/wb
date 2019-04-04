<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$discounts = \Local\Main\Discount::getAll();

?>
<div class="container">
	<a class="button" href="/discount/new/">Добавить</a>
</div>
<table class="fix">
	<colgroup width="300">
	<colgroup width="120">
	<colgroup width="120">
	<colgroup width="120">
	<thead>
	<tr>
		<th>Название</th>
		<th>От</th>
		<th>До</th>
		<th>Значение, %</th>
	</tr>
	</thead>

	<tbody><?

	foreach ($discounts as $item)
	{
		?><tr>
			<td class="tal"><a href="/discount/<?= $item['ID'] ?>/"><?= $item['NAME'] ?></td>
			<td><?= $item['FROM'] ?></td>
			<td><?= $item['TO'] ?></td>
			<td><?= $item['CODE'] ?></td>
		</tr><?
	}

	?>
	</tbody>
</table>


