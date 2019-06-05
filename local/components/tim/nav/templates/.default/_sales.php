<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ini_set('memory_limit', '2048M');

/** @var array $product */
/** @var array $filter */
/** @var bool $showPrices */
/** @var bool $needDateInterval */

$priceHistory = null;
$sppHistory = null;
$startPrice = null;
if ($showPrices)
{
	$priceHistory = \Local\Main\PriceHistory::getByProduct($product['ID']);
	$sppHistory = \Local\Main\Spp::getAll();
	$startPrice = $product['START_PRICE'];
}

$stores = \Local\Main\Stores::getAll();

$selectedStore = $_GET['store'];
if ($selectedStore && !\Local\Main\Stores::getById($selectedStore))
	$selectedStore = 0;

$groups = [
	'n' => 'не группировать (по дням)',
	'w' => 'по неделям',
	'm' => 'по месяцам',
];
$selectedGroup = $_GET['g'];
if (!isset($groups[$selectedGroup]))
	$selectedGroup = 'n';

$required = $needDateInterval ? ' required' : '';

if ($_REQUEST['from'])
	$filter['>=UF_DATE'] = \Bitrix\Main\Type\Date::createFromText($_REQUEST['from']);
if ($_REQUEST['to'])
	$filter['<=UF_DATE'] = \Bitrix\Main\Type\Date::createFromText($_REQUEST['to'] . ' 23:59:59');
if ($selectedStore)
	$filter['=UF_STORE'] = $selectedStore;

if ($selectedStore || $selectedGroup != 'n' || $_REQUEST['from'] || $_REQUEST['to'])
	$showReset = $APPLICATION->GetCurDir() . '?p=sales';

?>
	<div class="container">
		<form method="get" action="">

			<div class="dates">
				<h3>Диапазон дат</h3>
				<input type="hidden" name="p" value="sales"/>
				<p>
					<label for="from">От:</label>
					<input type="text" id="from" name="from" value="<?= $_REQUEST['from'] ?>"<?= $required ?>/>
				</p>
				<p>
					<label for="to">До:</label>
					<input type="text" id="to" name="to" value="<?= $_REQUEST['to'] ?>"<?= $required ?>/>
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
				<h3>Группировка</h3><?

				foreach ($groups as $gr => $text)
				{
					$checked = ($selectedGroup == $gr) ? ' checked' : '';
					?><label><input name="g" type="radio" value="<?= $gr ?>"<?= $checked ?>> <?= $text ?></label><br /><?
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

if ($needDateInterval && !($_REQUEST['from'] && $_REQUEST['to']))
	return;

$sales = \Local\Main\Sales::getByFilter($filter);
$stocks = \Local\Main\StocksHistory::getByFilter($filter);
$stocksByDate = [];
foreach ($stocks as $item)
{
	/** @var \Bitrix\Main\Type\DateTime $date */
	$date = $item['UF_DATE'];
	$date->setTime(0, 0, 0);
	$ts = $date->getTimestamp();

	if (!isset($stocksByDate[$ts][$item['UF_OFFER']][$item['UF_STORE']]))
		$stocksByDate[$ts][$item['UF_OFFER']][$item['UF_STORE']] = $item['UF_AMOUNT'];
}

$storeIds = [0];
if (!$selectedStore)
	foreach ($stores['ITEMS'] as $st)
		$storeIds[] = $st['ID'];

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

    ?>
    <table class="fix sales-table">
    <colgroup width="100">
    <colgroup width="120">
    <colgroup width="80">
    <colgroup width="70">
    <colgroup width="90">
    <colgroup width="70">
    <colgroup width="90">
    <colgroup width="70">
    <colgroup width="90">
	<colgroup width="70">
	<colgroup width="90">
    <colgroup width="70">
    <colgroup width="90"><?

		if ($showPrices && $selectedGroup == 'n')
		{
			?>
			<colgroup width="70">
			<colgroup width="50">
			<colgroup width="50">
			<colgroup width="50">
			<colgroup width="70">
			<colgroup width="70"><?
		}

		?>
    <thead>
    <tr>
        <th rowspan="2">Дата</th>
        <th rowspan="2">Склад</th>
        <th rowspan="2">Остатки</th>
        <th colspan="2">Поступления</th>
        <th colspan="2">Заказано</th>
        <th colspan="2">Возвраты<br />до оплаты</th>
        <th colspan="2">Продажи<br />по оплатам</th>
        <th colspan="2">Возвраты cо склада<br />поставщику</th><?

		if ($showPrices && $selectedGroup == 'n')
		{
			?>
			<th colspan="6">Цены в этот день</th><?
		}

		?>
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
        <th>руб.</th><?

		if ($showPrices && $selectedGroup == 'n')
		{
			?>
			<th>Цена</th>
			<th>Ск.</th>
			<th>Пр.</th>
			<th>СПП</th>
			<th>Рез.</th>
			<th>Итог WB</th><?
		}

	?>
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
		<td colspan="3" class="tar">Итого:</td>
		<td><?= $cnt1 ?></td>
		<td class="tar"><?= $price1 ?></td>
		<td><?= $cnt2 ?></td>
		<td class="tar"><?= $price2 ?></td>
		<td><?= $cnt3 ?></td>
		<td class="tar"><?= $price3 ?></td>
		<td><?= $cnt4 ?></td>
		<td class="tar"><?= $price4 ?></td>
		<td><?= $cnt5 ?></td>
		<td class="tar"><?= $price5 ?></td><?

		if ($showPrices && $selectedGroup == 'n')
		{
			?>
			<td colspan="6"></td><?
		}

		?>
	</tr><?


	/**
	 * Выводит строку
	 * @param $sums
	 * @param $ts
	 * @param $selectedStore
	 * @param $selectedGroup
	 * @param null $priceHistory
	 * @param null $sppHistory
	 * @param null $startPrice
	 * @param null $product
	 */
	function printRow($sums, $ts, $selectedStore, $selectedGroup, $priceHistory = null, $sppHistory = null, $startPrice = null, $product = null)
	{
		foreach ($sums as $storeId => $sum)
		{
			if (!$sum['ITEMS'])
				continue;

			$stocks = $sum['STOCKS'] && $selectedGroup == 'n' ? $sum['STOCKS'] : '';

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

			$orderClassPrice = '';
			$returnClassPrice = '';
			$saleClassPrice = '';

			$dayPrice = [];
			if ($priceHistory && $selectedGroup == 'n')
			{
				$dateTime = \Bitrix\Main\Type\DateTime::createFromTimestamp($ts);
				$dayPrice = \Local\System\Utils::getProductPriceByDay($dateTime, $priceHistory, $startPrice, $sppHistory, $product['BRAND']);

				if ($sum['UF_ORDER'])
				{
					$order = $sum['UF_ORDER_PRICE'] / $sum['UF_ORDER'];
					if ($order < $dayPrice['WIN_FROM'] || $order > $dayPrice['WIN_TO'])
					{
						$orderClassPrice = ' bgr';
					}
				}


				if ($sum['UF_RETURN'])
				{
					$order = $sum['UF_RETURN_PRICE'] / $sum['UF_RETURN'];
					if ($order < $dayPrice['WIN_FROM'] || $order > $dayPrice['WIN_TO'])
					{
						$returnClassPrice = ' bgr';
					}
				}


				if ($sum['UF_SALES'])
				{
					$order = $sum['UF_SALES_PRICE'] / $sum['UF_SALES'];
					if ($order < $dayPrice['WIN_FROM'] || $order > $dayPrice['WIN_TO'])
					{
						$saleClassPrice = ' bgr';
					}
				}
			}

			$date = date('d.m.Y', $ts);
			if (!$selectedStore)
			{
				$trClass = $storeId ? 'str d-' . $ts : 'sumr';
				$dataId = $storeId ? '' : ' data-id="' . $ts . '"';
				if ($storeId)
				{
					$store = \Local\Main\Stores::getById($storeId);
					$name = $store['NAME'];
					$date = '';
				}
				else
					$name = '';
			}
			else
			{
				$store = \Local\Main\Stores::getById($selectedStore);
				$name = $store['NAME'];
				$trClass = '';
				$dataId = '';
			}

			?>
			<tr class="<?= $trClass ?>"<?= $dataId ?>>
				<td><?= $date ?></td>
				<td><?= $name ?></td>
				<td><?= $stocks ?></td>
				<td><?= $cnt1 ?></td>
				<td class="tar"><?= $price1 ?></td>
				<td><?= $cnt2 ?></td>
				<td class="tar<?= $orderClassPrice ?>"><?= $price2 ?></td>
				<td><?= $cnt3 ?></td>
				<td class="tar<?= $returnClassPrice ?>"><?= $price3 ?></td>
				<td><?= $cnt4 ?></td>
				<td class="tar<?= $saleClassPrice ?>"><?= $price4 ?></td>
				<td><?= $cnt5 ?></td>
				<td class="tar"><?= $price5 ?></td><?

				if ($priceHistory && $selectedGroup == 'n')
				{
					?>
					<td class="tar"><?= $dayPrice['PRICE'] ?></td>
					<td class="tar"><?= $dayPrice['DISCOUNT'] ?></td>
					<td class="tar"><?= $dayPrice['PROMO'] ?></td>
					<td class="tar"><?= $dayPrice['SPP'] ?></td>
					<td class="tar"><?= $dayPrice['RES'] ?></td>
					<td class="tar"><?= $dayPrice['WIN'] ?></td><?
				}

				?>
			</tr><?
		}
	}

	/**
	 * Обнуляет суммы
	 * @param $sums
	 * @param $storeIds
	 */
	function clearSums(&$sums, $storeIds)
	{
		$sums = [];
		foreach ($storeIds as $storeId)
			$sums[$storeId] = [
				'ITEMS' => 0,
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
				'STOCKS' => 0,
			];
	}

	/**
	 * =====================================================
	 * Вывод таблицы
	 */

	$pred = false;
	$sums = [];
	clearSums($sums, $storeIds);

	foreach ($sales as $item)
	{
		/** @var \Bitrix\Main\Type\DateTime $date */
		$date = $item['UF_DATE'];
		$ts = $date->getTimestamp();

		if ($selectedGroup == 'n')
			$key = $ts;
		elseif ($selectedGroup == 'w')
			$key = floor(($ts - 302400) / 604800) * 604800 + 345600;
		elseif ($selectedGroup == 'm')
			$key = strtotime(date('01.m.Y', $ts));

		if ($pred === false)
			$pred = $key;

		if ($pred != $key)
		{
			printRow($sums, $pred, $selectedStore, $selectedGroup, $priceHistory, $sppHistory, $startPrice, $product);
			clearSums($sums, $storeIds);
		}

		foreach ($storeIds as $storeId)
		{
			if ($storeId == 0 || $storeId == $item['UF_STORE'])
			{
				$stocks = $stocksByDate[$ts][$item['UF_OFFER']][$item['UF_STORE']];

				$sums[$storeId]['ITEMS']++;
				$sums[$storeId]['UF_ADMISSION'] += $item['UF_ADMISSION'];
				$sums[$storeId]['UF_ADMISSION_PRICE'] += $item['UF_ADMISSION_PRICE'];
				$sums[$storeId]['UF_ORDER'] += $item['UF_ORDER'];
				$sums[$storeId]['UF_ORDER_PRICE'] += $item['UF_ORDER_PRICE'];
				$sums[$storeId]['UF_RETURN'] += $item['UF_RETURN'];
				$sums[$storeId]['UF_RETURN_PRICE'] += $item['UF_RETURN_PRICE'];
				$sums[$storeId]['UF_SALES'] += $item['UF_SALES'];
				$sums[$storeId]['UF_SALES_PRICE'] += $item['UF_SALES_PRICE'];
				$sums[$storeId]['UF_REMISSION'] += $item['UF_REMISSION'];
				$sums[$storeId]['UF_REMISSION_PRICE'] += $item['UF_REMISSION_PRICE'];
				$sums[$storeId]['STOCKS'] += $stocks;
			}
		}

		$pred = $key;
	}

	if ($pred)
		printRow($sums, $pred, $selectedStore, $selectedGroup, $priceHistory, $sppHistory, $startPrice, $product);

	?>
	</tbody>
    </table><?