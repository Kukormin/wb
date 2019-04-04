<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $offers */
/** @var array $filter */
/** @var bool $showProduct */
/** @var bool $showOffer */

$stores = \Local\Main\Stores::getAll();

$sumByStore = [];
$sumByOffer = [];
foreach ($offers['ITEMS'] as $offer)
{
    $sumByOffer[$offer['ID']] = [
        0,
        0,
    ];
}

// Продажи за месяц
$from = new \Bitrix\Main\Type\DateTime();
$from->add('-1 months');
$week = new \Bitrix\Main\Type\DateTime();
$weekTs = $week->add('-2 week')->getTimestamp();
$filter['>=UF_DATE'] = $from;
$sales = \Local\Main\Sales::getByFilter($filter);
// Группируем продажи по предложениям
$salesOfferData = [];
$isWeek = true;
foreach ($sales as $sale)
{
	if ($sale['UF_ORDER'] || $sale['UF_SALES'])
	{
		/** @var \Bitrix\Main\Type\DateTime $date */
		$date = $sale['UF_DATE'];
		$dt = $date->getTimestamp();
		if ($isWeek && $dt < $weekTs)
			$isWeek = false;

		if ($sale['UF_SALES'])
			$salesOfferData[$sale['UF_OFFER']]['M'] += $sale['UF_SALES'];
		if ($isWeek && $sale['UF_ORDER'])
			$salesOfferData[$sale['UF_OFFER']]['W'] += $sale['UF_ORDER'];
	}
}

    ?>
    <h2 class="page-title">Остатки</h2>

	<table class="fix table-edit"><?

	if ($showProduct)
	{
		?>
		<colgroup width="250"><?
	}

	if ($showOffer)
	{
		?>
		<colgroup width="250"><?
	}

	?>
    <colgroup width="50">
    <colgroup width="50">
        <col span="<?= count($stores['ITEMS']) * 3 ?>">
    </colgroup>
    <colgroup width="90">
        <col span="2">
    </colgroup>
    <colgroup width="70">
    <colgroup width="50">
    <colgroup width="50">
    <thead>
    <tr><?

		if ($showProduct)
		{
			?>
			<th rowspan="2">Товар</th><?
		}

		if ($showOffer)
		{
			?>
        	<th rowspan="2">Предложение</th><?
		}

		?>
        <th rowspan="2">Улн</th><?

		foreach ($stores['ITEMS'] as $store)
		{
			?>
			<th colspan="3"><?= $store['TITLE'] ?></th><?
		}

		?>
        <th colspan="2">В пути</th>
        <th rowspan="2">Остатки<br />+ в пути</th>
		<th rowspan="2">Прод.<br />мес</th>
		<th rowspan="2">Зак.<br />2 нед</th>
    </tr>
    <tr><?

        foreach ($stores['ITEMS'] as $store)
        {
            ?>
            <th>Скл.</th>
            <th>Деф.</th>
            <th>База</th><?
        }

        ?>
        <th>К клиенту</th>
        <th>От клиента</th>
    </tr>
    </thead>

    <tbody><?

    $from = 0;
    $to = 0;
	$uln = 0;
	$m = 0;
	$w = 0;
    foreach ($offers['ITEMS'] as $offer)
    {
        ?>
        <tr data-id="<?= $offer['ID'] ?>"><?

		$product = \Local\Main\Products::getById($offer['PRODUCT']);

		if ($showProduct)
		{
			$productA = \Local\Main\Products::getA($product, true);
			?>
			<td class="tal"><?= $productA ?></td><?
		}

		if ($showOffer)
		{
			$offerA = \Local\Main\Offers::getA($offer, true, $product);
			?>
			<td class="tal"><?= $offerA ?></td><?
		}

		?>
		<td class="uln"><?= $offer['STOCKS'] ?></td><?

        $total = 0;
        foreach ($stores['ITEMS'] as $store)
        {
            $stocks = \Local\Main\Stocks::getAmount($offer['ID'], $store['ID']);
			$deficit = Local\Main\Stocks::getDeficitAmount($offer['ID'], $store['ID']);
			$target = Local\Main\Stocks::getTargetAmount($offer['ID'], $store['ID']);
            ?>
            <td class="stc"><?= intval($stocks) ?></td>
            <td class="dfc"><?= intval($deficit) ?></td>
            <td class="e" data-id="<?= $store['ID'] ?>"><?= intval($target) ?></td><?

            $total += intval($stocks);
            $sumByStore[$store['ID']]['S'] += intval($stocks);
            $sumByStore[$store['ID']]['D'] += intval($deficit);
            $sumByStore[$store['ID']]['T'] += intval($target);
        }

        $shipping = \Local\Main\Shipping::getItem($offer['ID']);
		$total += $shipping['TO_CLIENT'] + $shipping['FROM_CLIENT'];

		?>
		<td><?= intval($shipping['TO_CLIENT']) ?></td>
		<td><?= intval($shipping['FROM_CLIENT']) ?></td>
		<td><?= $total ?></td>
		<td><?= $salesOfferData[$offer['ID']]['M'] ?></td>
		<td><?= $salesOfferData[$offer['ID']]['W'] ?></td><?

        $to += $shipping['TO_CLIENT'];
        $from += $shipping['FROM_CLIENT'];
		$uln += $offer['STOCKS'];
		$m += $salesOfferData[$offer['ID']]['M'];
		$w += $salesOfferData[$offer['ID']]['W'];

		?>
        </tr><?
    }

	if ($showOffer)
	{
		$colspan = $showProduct ? ' colspan="2"' : '';
		?>
		<tr class="summary">
			<td class="tar"<?= $colspan ?>>Итого:</td>
			<td><?= $uln ?></td><?

			$total = $from + $to;
			foreach ($stores['ITEMS'] as $store)
			{
				?>
				<td><?= $sumByStore[$store['ID']]['S'] ?></td>
				<td><?= $sumByStore[$store['ID']]['D'] ?></td>
				<td><?= $sumByStore[$store['ID']]['T'] ?></td><?

				$total += $sumByStore[$store['ID']]['S'];
			}

			?>
			<td><?= $to ?></td>
			<td><?= $from ?></td>
			<td><?= $total ?></td>
			<td><?= $m ?></td>
			<td><?= $w ?></td>
		</tr><?
	}

    ?>
    </tbody>
    </table><?
