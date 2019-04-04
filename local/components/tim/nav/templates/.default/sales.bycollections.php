<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ini_set('memory_limit', '2048M');

$stores = \Local\Main\Stores::getAll();

$selectedStore = $_GET['store'];
if ($selectedStore && !\Local\Main\Stores::getById($selectedStore))
	$selectedStore = 0;

if ($_REQUEST['from'])
	$from = new \Bitrix\Main\Type\DateTime($_REQUEST['from']);
if ($_REQUEST['to'])
	$to = new \Bitrix\Main\Type\DateTime($_REQUEST['to']);

if ($selectedStore || $_REQUEST['from'] || $_REQUEST['to'])
	$showReset = $APPLICATION->GetCurDir();

?>
	<div class="container">
		<form method="get" action="">

			<div class="dates">
				<h3>Диапазон дат</h3>
				<p>
					<label for="from">От:</label>
					<input type="text" id="from" name="from" value="<?= $_REQUEST['from'] ?>"/>
				</p>
				<p>
					<label for="to">До:</label>
					<input type="text" id="to" name="to" value="<?= $_REQUEST['to'] ?>"/>
				</p>
			</div>
			<div>
				<h3>Склады</h3>
				<label><input name="store" type="radio" value="0"<?= !$selectedStore ? ' checked' : '' ?>> Сумма</label><br /><?

				foreach ($stores['ITEMS'] as $st)
				{
					$checked = ($selectedStore == $st['ID']) ? ' checked' : '';
					?><label><input name="store" type="radio" value="<?= $st['ID'] ?>"<?= $checked ?>> <?= $st['NAME'] ?></label><br /><?
				}

				?>
			</div>
			<div>
				<p>
					<input type="submit" value="Показать"/><?

					if ($showReset)
					{
						?><a class="button reset" href="<?= $showReset ?>">Сбросить фильтр</a><?
					}

					?>
				</p>
			</div>
		</form>
	</div>
<?

$sales = \Local\Main\Sales::getSummarySalesByCollection($from, $to, $selectedStore);

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
}

$params = $APPLICATION->GetCurParam();
if ($params)
	$params = '?' . $params;

    ?>
    <table class="fix sales-table">
    <colgroup width="100">
    <colgroup width="300">
    <colgroup width="70">
    <colgroup width="90">
    <colgroup width="70">
    <colgroup width="90">
    <colgroup width="70">
    <colgroup width="90">
	<colgroup width="70">
	<colgroup width="90">
    <colgroup width="70">
    <colgroup width="90">
    <thead>
    <tr>
        <th rowspan="2">Бренд</th>
        <th rowspan="2">Коллекция</th>
        <th colspan="2">Поступления</th>
        <th colspan="2">Заказано</th>
        <th colspan="2">Возвраты<br />до оплаты</th>
        <th colspan="2">Продажи<br />по оплатам</th>
        <th colspan="2">Возвраты cо склада<br />поставщику</th>
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
    </tr>
    </thead>

	<tbody><?

	$cnt1 = $sum['UF_ADMISSION'] ? $sum['UF_ADMISSION'] : '';
	$cnt2 = $sum['UF_ORDER'] ? $sum['UF_ORDER'] : '';
	$cnt3 = $sum['UF_RETURN'] ? $sum['UF_RETURN'] : '';
	$cnt4 = $sum['UF_SALES'] ? $sum['UF_SALES'] : '';
	$cnt5 = $sum['UF_REMISSION'] ? $sum['UF_REMISSION'] : '';

	$price1 = $sum['UF_ADMISSION_PRICE'] ? number_format($sum['UF_ADMISSION_PRICE'], 2, ',', ' ') : '';
	$price2 = $sum['UF_ORDER_PRICE'] ? number_format($sum['UF_ORDER_PRICE'], 2, ',', ' ') : '';
	$price3 = $sum['UF_RETURN_PRICE'] ? number_format($sum['UF_RETURN_PRICE'], 2, ',', ' ') : '';
	$price4 = $sum['UF_SALES_PRICE'] ? number_format($sum['UF_SALES_PRICE'], 2, ',', ' ') : '';
	$price5 = $sum['UF_REMISSION_PRICE'] ? number_format($sum['UF_REMISSION_PRICE'], 2, ',', ' ') : '';
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
	</tr><?

	foreach ($sales as $item)
	{
		$cnt1 = $item['UF_ADMISSION'] ? $item['UF_ADMISSION'] : '';
		$cnt2 = $item['UF_ORDER'] ? $item['UF_ORDER'] : '';
		$cnt3 = $item['UF_RETURN'] ? $item['UF_RETURN'] : '';
		$cnt4 = $item['UF_SALES'] ? $item['UF_SALES'] : '';
		$cnt5 = $item['UF_REMISSION'] ? $item['UF_REMISSION'] : '';

		$price1 = $item['UF_ADMISSION_PRICE'] ? number_format($item['UF_ADMISSION_PRICE'], 2, ',', ' ') : '';
		$price2 = $item['UF_ORDER_PRICE'] ? number_format($item['UF_ORDER_PRICE'], 2, ',', ' ') : '';
		$price3 = $item['UF_RETURN_PRICE'] ? number_format($item['UF_RETURN_PRICE'], 2, ',', ' ') : '';
		$price4 = $item['UF_SALES_PRICE'] ? number_format($item['UF_SALES_PRICE'], 2, ',', ' ') : '';
		$price5 = $item['UF_REMISSION_PRICE'] ? number_format($item['UF_REMISSION_PRICE'], 2, ',', ' ') : '';

		$collection = \Local\Main\Collections::getById($item['COLLECTION']);
		$brand = \Local\Main\Brands::getById($collection['BRAND']);

		?>
		<tr>
			<td class="tal"><a href="/brands/<?= $brand['ID'] ?>/"><?= $brand['NAME'] ?></a></td>
			<td class="tal"><a href="/reports/collections/<?= $collection['ID'] ?>/<?= $params ?>"><?= $collection['NAME'] ?></a></td>
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
		</tr><?
	}

	?>
	</tbody>
    </table><?