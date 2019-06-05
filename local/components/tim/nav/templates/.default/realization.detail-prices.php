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
	<colgroup width="100">
	<colgroup width="100">

	<colgroup width="70">
	<colgroup width="70">
	<colgroup width="70">

	<colgroup width="80">
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
		<th colspan="7">Из отчета</th>
		<th colspan="5">Из БД</th>
		<th colspan="4">Расчет</th>
	</tr>
	<tr>
		<th>Себестоимость</th>
		<th>Розничная</th>
		<th>Скидка</th>
		<th>Промо</th>
		<th>СПП</th>
		<th>Со скидкой</th>
		<th>К переч.</th>
		<th>Себест.</th>
		<th>Цена</th>
		<th>Скидка</th>
		<th>Промо</th>
		<th>СПП</th>
		<th>К переч.</th>
		<th>+/-</th>
		<th>Профит1</th>
		<th>Профит2</th>
	</tr>
	</thead>

	<tbody><?

	$grey = 0;
	$sppHistory = \Local\Main\Spp::getAll();
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
			$ProductDiscountForReport = $row['ProductDiscountForReport']; // Скидка
			$SupplierPromo = $row['SupplierPromo']; // Промо
			$SupplierSpp = $row['SupplierSpp']; // СПП

			$WIN = $row['ForPay'] / $row['Quantity']; // К перечислению за товар
			//$WIN = $RetailAmount - $RetailCommission;
			$PROFIT = $WIN - $CostAmount;
			$PROFIT2 = 0;
			if ($offer['COST'])
				$PROFIT2 = $WIN - $offer['COST'];

			$day = $row['OrderPkDate'];
			$dayPrice = \Local\System\Utils::getProductPriceByDay($day, $priceHistory, $product['START_PRICE'], $sppHistory, $product['BRAND']);

			$errors = false;
			$priceClass = '';
			$reportPrice = $RetailPrice;
			if ($RetailPrice < $dayPrice['PRICE_FROM'] || $RetailPrice > $dayPrice['PRICE_TO'])
			{
				$priceClass = ' class="bgr"';
				$errors = true;
				$reportPrice = $dayPrice['PRICE_FROM'];
			}
			$discountClass = '';
			$_ProductDiscountForReport = $ProductDiscountForReport;
			if ($ProductDiscountForReport < $dayPrice['DISCOUNT_FROM'] || $ProductDiscountForReport > $dayPrice['DISCOUNT_TO'])
			{
				$discountClass = ' class="bgr"';
				$errors = true;
				$_ProductDiscountForReport = $dayPrice['DISCOUNT_TO'];
			}
			$promoClass = '';
			$_SupplierPromo = $SupplierPromo;
			if ($SupplierPromo < $dayPrice['RES_PROMO_FROM'] || $SupplierPromo > $dayPrice['RES_PROMO_TO'])
			{
				$promoClass = ' class="bgr"';
				$errors = true;
				$_SupplierPromo = $dayPrice['RES_PROMO_TO'];
			}
			$sppClass = '';
			$_SupplierSpp = $SupplierSpp;
			if ($SupplierSpp > $dayPrice['SPP_TO'])
			{
				$sppClass = ' class="bgr"';
				$errors = true;
				$_SupplierSpp = $dayPrice['SPP_TO'];
			}

			if ($_ProductDiscountForReport || $_SupplierPromo || $_SupplierSpp)
				$reportPrice = $reportPrice * (1 - $_ProductDiscountForReport / 100) * (1 - $_SupplierPromo / 100) * (1 - $_SupplierSpp / 100);
			$comiss = round($reportPrice * 38) / 100;
			if ($comiss < 100)
				$comiss = 100;
			$_WIN = $reportPrice - $comiss;
			$comissClass = '';
			$g = $WIN - $_WIN;
			if (abs($g) > 0.01)
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
				<td<?= $discountClass ?>><?= $ProductDiscountForReport ?></td>
				<td<?= $promoClass ?>><?= $SupplierPromo ?></td>
				<td<?= $sppClass ?>><?= $SupplierSpp ?></td>
				<td><?= $RetailAmount ?></td>
				<td<?= $comissClass ?>><?= $WIN ?></td>

				<td><?= $offer['COST'] ?></td>
				<td<?= $priceClass ?>><?= $dayPrice['PRICE'] ?></td>
				<td<?= $discountClass ?>><?= $dayPrice['DISCOUNT'] ?></td>
				<td<?= $promoClass ?>><?= $dayPrice['PROMO'] ?></td>
				<td<?= $sppClass ?>><?= $dayPrice['SPP'] ?></td>

				<td<?= $comissClass ?>><?= number_format($_WIN, 2, '.', ' ') ?></td>
				<td<?= $comissClass ?>><?= number_format($g, 2, '.', ' ') ?></td>
				<td class="<?= $profitClass ?>"><?= $PROFIT ?></td>
				<td class="<?= $profit2Class ?>"><?= $PROFIT2 ?></td>

			</tr><?
			$grey += $g;

		}
	}

	?>
	</tbody>
</table>
<p><?= number_format($grey, 2, '.', ' ') ?></p>

