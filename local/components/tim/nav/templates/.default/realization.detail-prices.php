<?
/** @var string $reportFileName */

$content = file_get_contents($reportFileName);
$reportData = json_decode($content, true);

$productsData = [];
foreach ($reportData['data'] as $row)
{
	if ($row['DocumentType'] != 'Продажа')
		continue;

	$bar = $row['Barcode'];
	$xmlId = $row['Article'];
	$product = \Local\Main\Products::getByXmlId($xmlId);
	$productId = $product['ID'];
	$productsData[$productId][] = $row;
}

?>
<div class="container">
	<div>
		<h3>Фильтры</h3>
		<label><input type="checkbox" id="hideOk" checked> Показать только сомнительные</label><br />
		<label><input type="checkbox" id="hidePr"> Показать только минусовой профит</label><br />
	</div>
</div>
<table class="prices fix hideOk">
	<colgroup width="200">
	<colgroup width="120">
	<colgroup width="200">
	<colgroup width="80">
	<colgroup width="80">
	<colgroup width="90">
	<colgroup width="80">

	<colgroup width="120">
	<colgroup width="100">
	<colgroup width="100">
	<colgroup width="100">
	<colgroup width="100">

	<colgroup width="70">
	<colgroup width="70">
	<colgroup width="70">

	<colgroup width="80">
	<colgroup width="80">
	<colgroup width="80">

	<thead>
	<tr>
		<th rowspan="2">Предложение</th>
		<th rowspan="2">Штрихкод</th>
		<th rowspan="2">Товар</th>
		<th rowspan="2">Арт. WB</th>
		<th rowspan="2">Артикул</th>
		<th rowspan="2">Дата</th>
		<th rowspan="2">Кол-во</th>
		<th colspan="5">Из отчета</th>
		<th colspan="3">Из БД</th>
		<th colspan="3">Расчет</th>
	</tr>
	<tr>
		<th>Себестоимость</th>
		<th>Розничная</th>
		<th>Скидка, %</th>
		<th>Со скидкой</th>
		<th>К переч.</th>
		<th>Себест.</th>
		<th>Цена</th>
		<th>Скидка</th>
		<th>К переч.</th>
		<th>Профит1</th>
		<th>Профит2</th>
	</tr>
	</thead>

	<tbody><?

	$priceHistoryAll = \Local\Main\PriceHistory::getAll();
	foreach ($productsData as $productId => $rows)
	{
		$product = \Local\Main\Products::getById($productId);
		$priceHistory = $priceHistoryAll[$productId];

		foreach ($rows as $row)
		{
			$bar = $row['Barcode'];
			if ($bar)
				$offer = \Local\Main\Offers::getByBar($bar);
			else
				$offer = \Local\Main\Offers::getByProductSize($productId, $row['Size']);

			$CostAmount = $row['CostAmount'] / $row['Quantity']; // Себестоимость
			$RetailPrice = $row['RetailPrice']; // Цена розничная
			$RetailAmount = $row['RetailAmount'] / $row['Quantity']; // Сумма продаж
			$RetailCommission = $row['RetailCommission'] / $row['Quantity']; // Сумма комиссии продаж
			$SalePercent = $row['SalePercent']; // Скидка

			$WIN = $row['ForPay'] / $row['Quantity']; // К перечислению за товар
			//$WIN = $RetailAmount - $RetailCommission;
			$PROFIT = $WIN - $CostAmount;
			$PROFIT2 = 0;
			if ($offer['COST'])
				$PROFIT2 = $WIN - $offer['COST'];

			$day = $row['OrderPkDate'];
			$dayPrice = \Local\System\Utils::getProductPriceByDay($day, $priceHistory, $product['START_PRICE']);

			$errors = false;
			$priceClass = '';
			if ($RetailPrice < $dayPrice['PRICE_FROM'] || $RetailPrice > $dayPrice['PRICE_TO'])
			{
				$priceClass = ' class="bgr"';
				$errors = true;
			}
			$discountClass = '';
			if ($SalePercent < $dayPrice['DISCOUNT_FROM'] || $SalePercent > $dayPrice['DISCOUNT_TO'])
			{
				$discountClass = ' class="bgr"';
				$errors = true;
			}

			$comissClass = '';
			$reportPrice = $RetailPrice;
			if ($SalePercent)
				$reportPrice = $reportPrice * (1 - $SalePercent / 100);
			$comiss = round($reportPrice * 38) / 100;
			if ($comiss < 100)
				$comiss = 100;
			$_WIN = $reportPrice - $comiss;
			if (abs($WIN - $_WIN) > 0.01)
			{
				$comissClass = ' class="bgr"';
				$errors = true;
			}

			$trClass = '';
			if (!$errors)
				$trClass .= 'ok ';
			if ($PROFIT >= 0)
				$trClass .= 'pr ';

			$profitClass = '';
			if ($PROFIT > 0)
				$profitClass = 'p2';
			elseif ($PROFIT < 0)
				$profitClass = 'p1';

			$profit2Class = '';
			if ($PROFIT2 > 0)
				$profit2Class = 'p2';
			elseif ($PROFIT2 < 0)
				$profit2Class = 'p1';

			$productA = \Local\Main\Products::getA($product, true);
			$offerA = \Local\Main\Offers::getA($offer, true, $product);

			?>
			<tr class="<?= $trClass ?>">
				<td class="tal"><?= $offerA ?></td>
				<td><?= $row['Barcode'] ?></td>
				<td class="tal"><?= $productA ?></td>
				<td><?= $product['XML_ID'] ?></td>
				<td><?= $product['CODE'] ?></td>
				<td><?= $day ?></td>
				<td><?= $row['Quantity'] ?></td>

				<td><?= $CostAmount ?></td>
				<td<?= $priceClass ?>><?= $RetailPrice ?></td>
				<td<?= $discountClass ?>><?= $SalePercent ?></td>
				<td><?= $RetailAmount ?></td>
				<td<?= $comissClass ?>><?= $WIN ?></td>

				<td><?= $offer['COST'] ?></td>
				<td<?= $priceClass ?>><?= $dayPrice['PRICE'] ?></td>
				<td<?= $discountClass ?>><?= $dayPrice['DISCOUNT'] ?></td>

				<td<?= $comissClass ?>><?= $_WIN ?></td>
				<td class="<?= $profitClass ?>"><?= $PROFIT ?></td>
				<td class="<?= $profit2Class ?>"><?= $PROFIT2 ?></td>

			</tr><?

		}
	}

	?>
	</tbody>
</table>
