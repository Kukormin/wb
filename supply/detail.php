<?
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поставка");

$storeId = $_REQUEST['store'];
$od = false;
if ($storeId == -1)
{
    $storeId = \Local\Main\Stores::PODOLSK_ID;
    $od = true;
}

$store = \Local\Main\Stores::getById($storeId);
if (!$store)
    return;

$kind = intval($_REQUEST['k']);
if (!$kind)
    return;

$ignoreDeficit = $_REQUEST['igd'] == 1;

$prefixByKind = [
    1 => 'Микс',
    2 => 'Моно',
    3 => 'Моно+микс',
];

$add = $od ? ' (общий дефицит)' : '';
$prefix = $prefixByKind[$kind];
$title = "$prefix поставка: " . $store['NAME'] . $add;
$APPLICATION->SetTitle($title);
$APPLICATION->AddChainItem($title);
$monoMin = 10;
$monoCorrect = 5;

$collections = \Local\Main\Collections::getAll();
$sections = \Local\Main\Sections::getAll();
$offers = \Local\Main\Offers::getAll();
$stores = \Local\Main\Stores::getAll();

$param = $APPLICATION->GetCurParam();

?>
<form class="supply-form" method="POST" target="_blank" action="/xls/supply1.php?<?= $param ?>">
    <div class="container">
        <p><input type="submit" value="Скачать xls"/></p>

        <div>
            <h3>Фильтры</h3>
            <label><input type="checkbox" id="hideZeroRes"> (R > 0) - Скрыть нулевой результат</label><br />
            <label><input type="checkbox" id="hideZeroUl"> (Улн > 0) - Скрыть нулевые остатки Улн</label><br />
            <label><input type="checkbox" id="hideZeroDef"> (Дефицит > 0) - Скрыть нулевой дефицит</label><br />
            <label><input type="checkbox" id="hideNotZeroTarget"> (База = 0) - Скрыть позиции, где заполнена "База"</label><br />
            <label><input type="checkbox" id="showDefUln"> (Дефицит = 0) и (Улн > 0)</label><br />
            <label><input type="checkbox" id="showAdd"> Больше рассчетного</label><br />
        </div>

		<div>
			<h3>Опции</h3>
			<label><input type="checkbox" id="ignoreDeficit" name="igd"<?= $_GET['igd'] == 1 ? ' checked' : '' ?> value="1"> Игнорировать дефицит</label><br />
		</div><?

        if (count($_REQUEST['section']) > 0)
        {
            ?>
            <div>
                <h3>Категории</h3><?

                foreach ($sections['ITEMS'] as $section)
                {
                    if (!in_array($section['ID'], $_REQUEST['section']))
                        continue;

                    ?>
                    <label><input type="checkbox" value="<?= $section['ID'] ?>" class="section-cb" checked /> <?= $section['NAME'] ?></label><br /><?
                }

                ?>
            </div><?
        }

        if (count($_REQUEST['collection']) > 0)
        {
            ?>
            <div>
            <h3>Коллекции</h3><?

            foreach ($collections['ITEMS'] as $collection)
            {
                if (!in_array($collection['ID'], $_REQUEST['collection']))
                    continue;

                ?>
                <label><input type="checkbox" value="<?= $collection['ID'] ?>" class="collection-cb" checked /> <?= $collection['NAME'] ?></label><br /><?
            }

            ?>
            </div><?
        }

        ?>
        <div>
            <h3>Дополнительно остатки на складах</h3><?

            foreach ($stores['ITEMS'] as $st)
            {
                if ($st['ID'] == $store['ID'])
                    continue;

                ?><label><input class="store-cb" type="checkbox" value="<?= $st['ID'] ?>"> <?= $st['NAME'] ?></label><br /><?
            }

            ?>
        </div>
        <div>
            <h3>Сортировка</h3>
            <a href="<?= $APPLICATION->GetCurPageParam('sort=deficit', ['sort']) ?>">По убыванию дефицита</a><br>
            <a href="<?= $APPLICATION->GetCurPageParam('sort=result', ['sort']) ?>">По убыванию результата</a>
        </div>
    </div><?

$result = [];
$summary = [];
$summaryChecked = [];
$jsRes = [];
foreach ($offers['ITEMS'] as $offer)
{
    $product = \Local\Main\Products::getById($offer['PRODUCT']);
    if (!$product['ACTIVE'])
    	continue;

    if ($kind == 2 && $product['PRICE'] >= 500)
        continue;
    if ($kind == 1 && $product['PRICE'] < 500)
        continue;

    if ($_REQUEST['section'] && $_REQUEST['collection'])
    {
	    $sectionOk = in_array($product['SECTION'], $_REQUEST['section']);
	    $coolectionOk = in_array($product['COLLECTION'], $_REQUEST['collection']);
	    if (!$sectionOk && !$coolectionOk)
		    continue;
    }
    elseif ($_REQUEST['section'])
    {
	    $sectionOk = in_array($product['SECTION'], $_REQUEST['section']);
	    if (!$sectionOk)
		    continue;
    }
	elseif ($_REQUEST['collection'])
	{
		$coolectionOk = in_array($product['COLLECTION'], $_REQUEST['collection']);
		if (!$coolectionOk)
			continue;
	}

    $uln = $offer['STOCKS'];
    $stocks = \Local\Main\Stocks::getAmount($offer['ID'], $store['ID']);
	$target = \Local\Main\Stocks::getTargetAmount($offer['ID'], $store['ID']);

    if ($od)
        $deficit = \Local\Main\Stocks::getDeficitAmount($offer['ID'], \Local\Main\Stores::COMMON_ID);
    else
        $deficit = \Local\Main\Stocks::getDeficitAmount($offer['ID'], $store['ID']);

    $textCode = 0;

    $R = 0;
	if (!$target)
	{
		$textCode = 1;
	}
	elseif ($target <= $stocks)
	{
		$textCode = 2;
	}
	elseif (!$uln)
	{
		$textCode = 3;
	}
	elseif (!$deficit && !$ignoreDeficit)
	{
		$textCode = 4;
	}
	else
	{
		$R = $target - $stocks;
		if ($uln < $R)
		{
			$R = $uln;
			$textCode = 11;
		}
		if (!$ignoreDeficit && $deficit < $R)
		{
			$R = $deficit;
			$textCode = 12;
		}
		if ($kind > 1 && $R < $monoMin)
		{
			$d = $monoMin - $R;
			if ($d < $monoCorrect && ($monoMin <= $deficit || $ignoreDeficit) && $monoMin <= $uln) {
				$R = $monoMin;
				$textCode = 41;
			}
			else
			{
				$R = 0;
				$textCode = 13;
			}
		}
	}

	if ($kind > 1 && $R)
	{
		$after = $uln - $R;
		if ($after > 0 && $after < $monoMin && ($R + $after <= $deficit || $ignoreDeficit))
		{
			$R += $after;
			$textCode = 42;
		}
	}

    $result[] = [
        'ID' => $offer['ID'],
        'PRODUCT' => $offer['PRODUCT'],
        'NAME' => $offer['NAME'],
        'STOCKS' => $stocks,
        'DEFICIT' => $deficit,
        'ULN' => $uln,
        'TARGET' => $target,
        'R' => $R,
        'TEXT' => $textCode,
    ];

    $summary['CNT']++;
    $summary['PRICE'] += $product['PRICE'];
    $summary['STOCKS'] += $stocks;
    $summary['DEFICIT'] += $deficit;
    $summary['ULN'] += $uln;
    $summary['TARGET'] += $target;
    $summary['R'] += $R;

    if ($R)
	{
		$summaryChecked['CNT']++;
		$summaryChecked['PRICE'] += $product['PRICE'];
		$summaryChecked['STOCKS'] += $stocks;
		$summaryChecked['DEFICIT'] += $deficit;
		$summaryChecked['ULN'] += $uln;
		$summaryChecked['TARGET'] += $target;
		$summaryChecked['R'] += $R;
	}

}

if ($_REQUEST['sort'] == 'deficit') {
    usort($result, function($a, $b) {
        if ($a['DEFICIT'] > $b['DEFICIT'])
            return -1;
        elseif ($a['DEFICIT'] < $b['DEFICIT'])
            return 1;
        else
            return 0;
    });
}
if ($_REQUEST['sort'] == 'result') {
    usort($result, function($a, $b) {
        if ($a['R'] > $b['R'])
            return -1;
        elseif ($a['R'] < $b['R'])
            return 1;
        else
            return 0;
    });
}

// Продажи за месяц
$to = new \Bitrix\Main\Type\DateTime();
$from = new \Bitrix\Main\Type\DateTime();
$from->add('-1 months');
$week = new \Bitrix\Main\Type\DateTime();
$weekTs = $week->add('-2 week')->getTimestamp();
$filterStore = $od ? 0 : $store['ID'];
$sales = \Local\Main\Sales::getByDates($from, $to, $filterStore);
// Группируем продажи по предложениям
$salesOfferData = [];
$isWeek = true;
foreach ($sales as $sale)
{
	if ($sale['UF_ORDER'] || $sale['UF_SALES'])
	{
		$dt = $sale['UF_DATE']->getTimestamp();
		if ($isWeek && $dt < $weekTs)
			$isWeek = false;

		if ($sale['UF_SALES'])
			$salesOfferData[$sale['UF_OFFER']]['M'] += $sale['UF_SALES'];
		if ($isWeek && $sale['UF_ORDER'])
			$salesOfferData[$sale['UF_OFFER']]['W'] += $sale['UF_ORDER'];
	}
}

$textByCode = [
    0 => '',
    1 => 'Не задана база',
    2 => 'Поставка не нужна',
    3 => 'Нет в наличии',
    4 => 'Нет дифицита',
    11 => 'Недостаточно на складе',
    12 => 'Дефицит не позволяет отправить больше',
    13 => 'Недостаточно для моно-поставки',
    41 => 'Больше рассчетного',
    42 => 'Больше рассчетного под остаток',
];

?>
<table class="fix table-edit supply-table">
    <thead class="main-head">
    <tr>
        <th colspan="3">Товар</th>
        <th colspan="2">Предложение</th>
        <th rowspan="2" width="50">Улн</th><?

	    foreach ($stores['ITEMS'] as $st)
	    {
		    if ($st['ID'] == $store['ID'])
			    continue;

		    ?><th width="70" rowspan="2" class="st st<?= $st['ID'] ?>"><?= $st['TITLE'] ?></th><?
	    }

	    ?>
        <th colspan="3"><?= $store['TITLE'] ?><?= $add ?></th>
        <th rowspan="2" width="60">Прод.<br />мес</th>
        <th rowspan="2" width="60">Зак.<br />2 нед</th>
        <th colspan="3">Результат</th>
    </tr>
    <tr>
        <th width="200">Название</th>
        <th width="90">Артикул</th>
        <th width="60">Цена</th>
        <th width="200">Название</th>
        <th width="24"><input class="m" type="checkbox" checked /></th>
        <th width="50">Скл.</th>
        <th width="60">Деф.</th>
        <th width="60">База</th>
        <th width="30">R</th>
        <th width="300">Примечание</th>
    </tr>
    </thead>
	<thead class="fix-head">
		<tr>
			<th width="200">&nbsp;</th>
			<th width="90">&nbsp;</th>
			<th width="60">&nbsp;</th>
			<th width="200">&nbsp;</th>
			<th width="24">&nbsp;</th>
			<th width="50">&nbsp;</th><?

			foreach ($stores['ITEMS'] as $st)
			{
				?><th class="st st<?= $st['ID'] ?>" width="70">&nbsp;</th><?
			}

			?>
			<th width="50">&nbsp;</th>
			<th width="60">&nbsp;</th>
			<th width="60">&nbsp;</th>
			<th width="60">&nbsp;</th>
			<th width="60">&nbsp;</th>
			<th width="30">&nbsp;</th>
			<th width="300">&nbsp;</th>
		</tr>
	</thead>

    <tbody><?

        //
        // Summary
        //
        ?>
        <tr class="summary">
            <td colspan="2" class="tar">Средняя цена:</td>
            <td><?= intval(round($summary['PRICE'] / $summary['CNT'])) ?></td>
            <td colspan="2">Всего позиций: <?= $summary['CNT'] ?></td>
            <td><?= $summary['ULN'] ?></td><?

            foreach ($stores['ITEMS'] as $st)
            {
                if ($st['ID'] == $store['ID'])
                    continue;

                ?><td class="st st<?= $st['ID'] ?>"></td><?
            }

            ?>
            <td><?= $summary['STOCKS'] ?></td>
            <td><?= $summary['DEFICIT'] ?></td>
            <td><?= $summary['TARGET'] ?></td>
            <td></td>
			<td></td>
			<td><?= $summary['R'] ?></td>
			<td></td>
        </tr><?

		//
		// Сумма по выбранным позициям
		//
		?>
		<tr class="summary-checked">
			<td colspan="2" class="tar">Средняя цена выбранных позиций:</td>
			<td class="js-price"><?= intval(round($summaryChecked['PRICE'] / $summaryChecked['CNT'])) ?></td>
			<td colspan="2">Выбрано позиций: <span class="js-cnt"><?= $summaryChecked['CNT'] ?></span></td>
			<td class="js-uln"><?= $summaryChecked['ULN'] ?></td><?

			foreach ($stores['ITEMS'] as $st)
			{
				if ($st['ID'] == $store['ID'])
					continue;

				?><td class="st st<?= $st['ID'] ?>"></td><?
			}

			?>
			<td class="js-stocks"><?= $summaryChecked['STOCKS'] ?></td>
			<td class="js-deficit"><?= $summaryChecked['DEFICIT'] ?></td>
			<td class="js-target"><?= $summaryChecked['TARGET'] ?></td>
			<td></td>
			<td></td>
			<td class="js-R"><?= $summaryChecked['R'] ?></td>
			<td></td>
		</tr><?

        foreach ($result as $item)
        {
            $product = \Local\Main\Products::getById($item['PRODUCT']);

            $text = $textByCode[$item['TEXT']];
            $rClass = '';
            if ($item['TEXT'] > 40)
	            $rClass = 'hlp';
            elseif ($item['TEXT'] == 1)
	            $rClass = 'hl1';
            elseif ($item['TEXT'] == 12)
	            $rClass = 'hl2';
            elseif ($item['TEXT'] == 13)
	            $rClass = 'hl3';

            $trClass = '';
            $trClass .= $item['R'] ? '' : 'zR ';
            $trClass .= $item['ULN'] ? '' : 'zU ';
            $trClass .= $item['DEFICIT'] ? '' : 'zD ';
            $trClass .= $item['TARGET'] ? 'zT ' : '';
            $trClass .= !$item['DEFICIT'] && $item['ULN'] ? '' : ' sDU';
            $trClass .= $item['TEXT'] > 40 ? '' : ' sA';

            $productA = \Local\Main\Products::getA($product, true);
            $offerA = \Local\Main\Offers::getA($item, true, $product);

            ?>
            <tr data-id="<?= $item['ID'] ?>" class="<?= $trClass ?>"
                data-section="<?= $product['SECTION'] ?>" data-collection="<?= $product['COLLECTION'] ?>">
				<td class="tal"><div class="name"><?= $productA ?></div></td>
                <td><?= $product['CODE'] ?></td>
                <td><?= $product['PRICE'] ?></td>
                <td class="tal"><div class="name"><?= $offerA ?></div>
                </td>
                <td><?
                    ?><input class="m" type="checkbox" name="incl[<?= $item['ID'] ?>]" data-id="<?= $item['ID'] ?>" checked /><?
					?><input type="hidden" name="r[<?= $item['ID'] ?>]" value="<?= $item['R'] ?>" />
				</td>
                <td class="uln"><?= $item['ULN'] ?></td><?

                    foreach ($stores['ITEMS'] as $st)
                    {
                        if ($st['ID'] == $store['ID'])
                            continue;

                        $stocks = \Local\Main\Stocks::getAmount($item['ID'], $st['ID']);
                        ?><td class="st st<?= $st['ID'] ?>"><?= $stocks ?></td><?
                    }

                ?>
                <td class="stc"><?= $item['STOCKS'] ?></td>
                <td class="dfc"><?= $item['DEFICIT'] ?><input type="hidden" name="d[<?= $item['ID'] ?>]" value="<?= $item['DEFICIT'] ?>" /></td>
                <td class="e" data-id="<?= $store['ID'] ?>"><?= $item['TARGET'] ?></td><?

                ?>
                <td><?= $salesOfferData[$item['ID']]['M'] ?></td>
                <td><?= $salesOfferData[$item['ID']]['W'] ?></td>
                <td class="e <?= $rClass ?>" data-r="1"><?= $item['R'] ?></td>
                <td class="<?= $rClass ?>"><?= $text ?></td>
            </tr><?

			$jsRes[$item['ID']] = [
				'PRICE' => $product['PRICE'],
				'STOCKS' => $item['STOCKS'],
				'DEFICIT' => $item['DEFICIT'],
				'ULN' => $item['ULN'],
				'TARGET' => $item['TARGET'],
				'R' => $item['R'],
			];
        }

        ?>
    </tbody>
</table>
<script>var DATA = <?= json_encode($jsRes) ?>;
var kind = <?= $kind ?>;
var monoCorrect = <?= $monoCorrect ?>;
var monoMin = <?= $monoMin ?>;
var textByCode = <?= json_encode($textByCode, JSON_UNESCAPED_UNICODE) ?>;</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>