<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


$tops = [
	[
		'TITLE' => 'Топ товаров по профиту',
		'KEY' => 'SALES',
		'ORDER' => 'MARGIN',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по сумме продаж',
		'KEY' => 'SALES',
		'ORDER' => 'SUM',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по количеству продаж',
		'KEY' => 'SALES',
		'ORDER' => 'CNT',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по сумме заказов',
		'KEY' => 'ORDER',
		'ORDER' => 'SUM',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по количеству заказов',
		'KEY' => 'ORDER',
		'ORDER' => 'CNT',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по сумме возвратов',
		'KEY' => 'RETURN',
		'ORDER' => 'SUM',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ товаров по количеству возвратов',
		'KEY' => 'RETURN',
		'ORDER' => 'CNT',
		'COLLECTION' => false,
	],
	[
		'TITLE' => 'Топ коллекций по профиту',
		'KEY' => 'SALES',
		'ORDER' => 'MARGIN',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по сумме продаж',
		'KEY' => 'SALES',
		'ORDER' => 'SUM',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по количеству продаж',
		'KEY' => 'SALES',
		'ORDER' => 'CNT',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по сумме заказов',
		'KEY' => 'ORDER',
		'ORDER' => 'SUM',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по количеству заказов',
		'KEY' => 'ORDER',
		'ORDER' => 'CNT',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по сумме возвратов',
		'KEY' => 'RETURN',
		'ORDER' => 'SUM',
		'COLLECTION' => true,
	],
	[
		'TITLE' => 'Топ коллекций по количеству возвратов',
		'KEY' => 'RETURN',
		'ORDER' => 'CNT',
		'COLLECTION' => true,
	],
];

$intervals = [
	'1w' => [
		'ADD' => '-1 week',
		'TEXT' => 'неделя',
	],
	'2w' => [
		'ADD' => '-2 weeks',
		'TEXT' => '2 недели',
	],
	'1m' => [
		'ADD' => '-1 month',
		'TEXT' => 'месяц',
	],
	'2m' => [
		'ADD' => '-2 months',
		'TEXT' => '2 месяца',
	],
];
$interval = $_GET['i'];
if (!isset($intervals[$interval]))
	$interval = '1m';

?>
	<div class="container">
		<form method="get" action="">

			<div>
				<h3>Интервал</h3><?

				foreach ($intervals as $intCode => $int)
				{
					$checked = ($intCode == $interval) ? ' checked' : '';
					?><label><input name="i" type="radio" value="<?= $intCode ?>"<?= $checked ?>> <?= $int['TEXT'] ?></label><br /><?
				}

				?>
			</div>
			<div>
				<p>
					<input type="submit" value="Показать"/>
				</p>
			</div>
		</form>
	</div><?


foreach ($tops as $top)
{
	?>
	<h2 class="page-title"><?= $top['TITLE'] ?></h2>
	<table class="fix"><?

		if ($top['COLLECTION'])
		{
			?>
			<colgroup width="300"><?
		}
		else
		{
			?>
			<colgroup width="250">
			<colgroup width="100"><?
		}

		?>

		<colgroup width="120">
		<colgroup width="120"><?

		if ($top['KEY'] != 'ORDER' && $top['KEY'] != 'RETURN')
		{
			?>
			<colgroup width="120">
			<colgroup width="120"><?
		}

		?>
		<thead>
		<tr><?

			if ($top['COLLECTION'])
			{
				?>
				<th>Коллекция</th><?
			}
			else
			{
				?>
				<th>Название</th>
				<th>Артикул</th><?
			}

			if ($top['KEY'] == 'ORDER')
				$th = 'Заказы';
			elseif ($top['KEY'] == 'RETURN')
				$th = 'Возвраты';
			else
				$th = 'Продажи';

			?>
			<th><?= $th ?></th>
			<th>Количество</th><?

			if ($top['KEY'] != 'ORDER' && $top['KEY'] != 'RETURN')
			{
				?>
				<th>Профит1</th>
				<th>Профит2</th><?
			}

			?>
		</tr>
		</thead>

		<tbody><?

		$sales = \Local\Main\Sales::getTopProducts($top['KEY'], $top['ORDER'], $top['COLLECTION'], $intervals[$interval]['ADD']);
		foreach ($sales as $item)
		{
			if ($top['COLLECTION'])
			{
				$collection = \Local\Main\Collections::getById($item['COLLECTION']);
				?>
				<td class="tal"><a
						href="/brands/<?= $collection['BRAND'] ?>/<?= $collection['ID'] ?>/"><?= $collection['NAME'] ?></a>
				</td><?
			}
			else
			{
				$product = \Local\Main\Products::getById($item['UF_PRODUCT']);
				$trClass = $product['ACTIVE'] ? '' : ' class="deactiv"';
				$productA = \Local\Main\Products::getA($product);

				?>
				<tr<?= $trClass ?>>
				<td class="tal"><?= $productA ?></td>
				<td><?= $product['CODE'] ?></td><?
			}

			?>
			<td class="tar"><?= number_format($item['SUM'], 2, ',', ' ') ?></td>
			<td><?= $item['CNT'] ?></td><?

			if ($top['KEY'] != 'ORDER' && $top['KEY'] != 'RETURN')
			{
				?>
				<td class="tar"><?= number_format($item['PRE_MARGIN'], 2, ',', ' ') ?></td>
				<td class="tar"><?= number_format($item['MARGIN'], 2, ',', ' ') ?></td><?
			}

			?>
			</tr><?
		}

		?>
		</tbody>
	</table><?
}