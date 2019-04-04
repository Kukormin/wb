<?
/** @var string $reportFileName */

$content = file_get_contents($reportFileName);
$reportData = json_decode($content, true);

// Группируем данные из отчета по предложениям
$reportOffersData = [];
foreach ($reportData['data'] as $row)
{
	$bar = $row['Barcode'];
	if ($bar)
		$offer = \Local\Main\Offers::getByBar($bar);
	else
	{
		$xmlId = $row['Article'];
		$product = \Local\Main\Products::getByXmlId($xmlId);
		$offer = \Local\Main\Offers::getByProductSize($product['ID'], $row['Size']);
	}

	if ($offer)
		$reportOffersData[$offer['ID']][] = $row;
}

// Получаем все продажи за месяц из БД
$fromDT = \Bitrix\Main\Type\DateTime::createFromUserTime($realization['FROM']);
$toDT = \Bitrix\Main\Type\DateTime::createFromUserTime($realization['TO']);
$sales = \Local\Main\Sales::getByDates($fromDT, $toDT);

// Группируем продажи по предложениям
$salesOfferData = [];
foreach ($sales as $sale)
{
	if ($sale['UF_SALES'] || $sale['UF_RETURN'])
		$salesOfferData[$sale['UF_OFFER']][] = $sale;
}

//
// Формируем данные для вывода в таблицу
//
$result = [];
foreach ($reportOffersData as $offerId => $offerData)
{

	$offer = \Local\Main\Offers::getById($offerId);
	$product = \Local\Main\Products::getById($offer['PRODUCT']);

	$report = [];
	$salesDB = 0;
	$returnDB = 0;
	$salesR = 0;
	$returnR = 0;

	foreach ($salesOfferData[$offerId] as $sale)
	{
		$salesDB += $sale['UF_SALES'];
		$returnDB += $sale['UF_RETURN'];

		if ($sale['UF_SALES'] || $sale['UF_RETURN'])
		{
			/** @var \Bitrix\Main\Type\Date $date */
			$date = $sale['UF_DATE'];
			$key = $date->format('Y-m-d');

			if (!isset($report[$key]))
				$report[$key] = [
					'DATE' => $date->toString(),
					'S' => 0,
					'R' => 0,
				];

			$report[$key]['DB']['S'] += $sale['UF_SALES'];
			$report[$key]['DB']['R'] += $sale['UF_RETURN'];
		}
	}

	foreach ($offerData as $row)
	{
		$s = 0;
		$r = 0;
		if ($row['DocumentType'] == 'Продажа')
			$s = $row['Quantity'];
		elseif ($row['DocumentType'] == 'Возврат')
			$r = $row['Quantity'];

		$salesR += $s;
		$returnR += $r;

		if ($s || $r)
		{
			$dateF = $row['OrderPkDate'];
			$key = ConvertDateTime($dateF, 'YYYY-MM-DD');

			if (!isset($report[$key]))
				$report[$key] = [
					'DATE' => $dateF,
				];

			$report[$key]['REP']['S'] += $s;
			$report[$key]['REP']['R'] += $r;
		}
	}

	ksort($report);

	$salesCh = $salesR - $salesDB;
	$returnCh = $returnR - $returnDB;

	$result[$offerId] = [
		'ID' => $offerId,
		'PRODUCT' => $product['ID'],
		'NAME' => $offer['NAME'],
		'XML_ID' => $product['XML_ID'],
		'CODE' => $product['CODE'],
		'S1' => $salesR,
		'S2' => $salesDB,
		'S3' => $salesCh,
		'R1' => $returnR,
		'R2' => $returnDB,
		'R3' => $returnCh,
		'report' => $report,
	];
}

$order = $_GET['order'] == 'asc' ? 'desc' : 'asc';
$cur = $_GET['order'] == 'asc' ? ' s-asc' : ' s-desc';
if ($_GET['sort'] == 'name')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if ($a['NAME'] < $b['NAME'])
				return -1;
			elseif ($a['NAME'] > $b['NAME'])
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if ($a['NAME'] > $b['NAME'])
				return -1;
			elseif ($a['NAME'] < $b['NAME'])
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 's1')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if ($a['S1'] < $b['S1'])
				return -1;
			elseif ($a['S1'] > $b['S1'])
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if ($a['S1'] > $b['S1'])
				return -1;
			elseif ($a['S1'] < $b['S1'])
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 's2')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if ($a['S2'] < $b['S2'])
				return -1;
			elseif ($a['S2'] > $b['S2'])
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if ($a['S2'] > $b['S2'])
				return -1;
			elseif ($a['S2'] < $b['S2'])
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 's3')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if (abs($a['S3']) < abs($b['S3']))
				return -1;
			elseif (abs($a['S3']) > abs($b['S3']))
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if (abs($a['S3']) > abs($b['S3']))
				return -1;
			elseif (abs($a['S3']) < abs($b['S3']))
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 'r1')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if ($a['R1'] < $b['R1'])
				return -1;
			elseif ($a['R1'] > $b['R1'])
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if ($a['R1'] > $b['R1'])
				return -1;
			elseif ($a['R1'] < $b['R1'])
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 'r2')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if ($a['R2'] < $b['R2'])
				return -1;
			elseif ($a['R2'] > $b['R2'])
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if ($a['R2'] > $b['R2'])
				return -1;
			elseif ($a['R2'] < $b['R2'])
				return 1;
			else
				return 0;
		});
elseif ($_GET['sort'] == 'r3')
	if ($_GET['order'] == 'asc')
		usort($result, function($a, $b) {
			if (abs($a['R3']) < abs($b['R3']))
				return -1;
			elseif (abs($a['R3']) > abs($b['R3']))
				return 1;
			else
				return 0;
		});
	else
		usort($result, function($a, $b) {
			if (abs($a['R3']) > abs($b['R3']))
				return -1;
			elseif (abs($a['R3']) < abs($b['R3']))
				return 1;
			else
				return 0;
		});

?>
<div class="container">
	<div>
		<h3>Фильтры</h3>
		<label><input type="checkbox" id="hideOk" checked> Показать только отличающиеся предложения</label><br />
	</div>
</div>
<table class="realization fix hideOk">
	<colgroup width="300">
	<colgroup width="100">
	<colgroup width="100">
	<colgroup width="100">
		<col span="3">
	</colgroup>
	<thead>
	<tr><?

		$sort = $APPLICATION->GetCurPageParam('sort=name&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 'name' ? $cur : '';
		?><th rowspan="2" class="sort<?= $cl ?>" data-sort="<?= $sort ?>">Предложение</th>
		<th rowspan="2">Арт. WB</th>
		<th rowspan="2">Артикул</th>
		<th colspan="3">Кол-во продаж</th>
	</tr>
	<tr><?

		$sort = $APPLICATION->GetCurPageParam('sort=s1&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 's1' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">В отчете</th><?
		$sort = $APPLICATION->GetCurPageParam('sort=s2&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 's2' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">В БД</th><?
		$sort = $APPLICATION->GetCurPageParam('sort=s3&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 's3' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">Отличие</th><?
		/*$sort = $APPLICATION->GetCurPageParam('sort=r1&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 'r1' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">В отчете</th><?
		$sort = $APPLICATION->GetCurPageParam('sort=r2&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 'r2' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">В БД</th><?
		$sort = $APPLICATION->GetCurPageParam('sort=r3&order=' . $order, ['sort', 'order', 'id']);
		$cl = $_GET['sort'] == 'r3' ? $cur : '';
		?><th class="sort<?= $cl ?>" data-sort="<?= $sort ?>">Отличие</th><?*/
	?>
	</tr>
	</thead>

	<tbody><?

	foreach ($result as $offerId => $item)
	{

		$chClass = '';
		if ($item['S3'] != 0)
			$chClass = ' ch';
		else
			$chClass = ' ok';

		if ($item['S3'])
			$item['S3'] = '<b class="warning">' . $item['S3'] . '</b>';
		if ($item['R3'])
			$item['R3'] = '<b class="warning">' . $item['R3'] . '</b>';

		$offerA = \Local\Main\Offers::getA($item, true);

		?><tr class="offer<?= $chClass ?>" data-id="<?= $offerId ?>">
			<td><?= $offerA ?></td>
			<td><?= $item['XML_ID'] ?></td>
			<td><?= $item['CODE'] ?></td>
			<td><?= $item['S1'] ?></td>
			<td><?= $item['S2'] ?></td>
			<td><?= $item['S3'] ?></td>
		</tr><?

		foreach ($item['report'] as $day)
		{
			?><tr class="report hidden" data-bar="<?= $offerId ?>">
				<td colspan="3"><?= $day['DATE'] ?></a></td>
				<td><?= $day['REP']['S'] ? $day['REP']['S'] : '' ?></td>
				<td><?= $day['DB']['S'] ? $day['DB']['S'] : '' ?></td>
				<td><?= '' ?></td>
			</tr><?
		}
	}

	?>
	</tbody>
</table>
