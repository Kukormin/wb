<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ini_set('memory_limit', '2048M');

/** @var array $filter */
/** @var bool $hideStores */
/** @var int $height */
/** @var string $dateFromF */

if (!$height)
	$height = 700;

$stores = \Local\Main\Stores::getAll();

$selectedStore = $_GET['store'];
if ($selectedStore && !\Local\Main\Stores::getById($selectedStore))
	$selectedStore = 0;

if ($selectedStore)
	$filter['=UF_STORE'] = $selectedStore;

$sales = \Local\Main\Sales::getGroupByDate($filter);
$stocks = \Local\Main\StocksHistory::getGroupByDate($filter);

//
// Собираем продажи
//
$D = '';
$priceMax = 0;
$cntMax = 0;
$priceMin = 0;
$cntMin = 0;
foreach ($sales as $i => $item)
{
	/** @var \Bitrix\Main\Type\DateTime $date */
	$date = $item['UF_DATE'];

	if (!$i)
		$dateMinF = $date->format('c');

	$dateF = $date->format('c');

	if ($item['SUM'] > $priceMax)
		$priceMax = $item['SUM'];
	if ($item['CNT'] > $cntMax)
		$cntMax = $item['CNT'];
	if ($item['ORDER_SUM'] > $priceMax)
		$priceMax = $item['ORDER_SUM'];
	if ($item['ORDER_CNT'] > $cntMax)
		$cntMax = $item['ORDER_CNT'];

	if (-$item['RETURN_SUM'] < $priceMin)
		$priceMin = -$item['RETURN_SUM'];
	if (-$item['RETURN_CNT'] < $cntMin)
		$cntMin = -$item['RETURN_CNT'];

	if ($D)
		$D .= ',';

	$D .= '{d:new Date("' . $dateF . '"),cnt:' . $item['CNT'] . ',sum:' . round($item['SUM'], 2) . ',rcnt:' . $item['RETURN_CNT'] . ',rsum:' . round($item['RETURN_SUM'], 2) . ',ocnt:' . $item['ORDER_CNT'] . ',osum:' . round($item['ORDER_SUM'], 2) . '}';
}

$D = 'D=[' . $D . '];';
$dateMax = new \Bitrix\Main\Type\DateTime($date);
$dateMax->add('1 day');

//
// Собираем остатки
//
$S = '';
$stocksMax = 0;
foreach ($stocks as $i => $item)
{
	/** @var \Bitrix\Main\Type\DateTime $date */
	$date = $item['UF_DATE'];
	$dateF = $date->format('c');

	if ($item['SUM'] > $stocksMax)
		$stocksMax = $item['SUM'];

	if ($S)
		$S .= ',';

	$S .= '{d:new Date("' . $dateF . '"),sum:' . $item['SUM'] . '}';
}

$S = 'S=[' . $S . '];';
if ($date > $dateMax)
	$dateMax = $date;
$dateMaxF = $dateMax->format('c');

$view = $_GET['view'];
if ($view != 'cnt')
	$view = 'sum';

if (!$dateFromF)
	$dateFromF = $dateMinF;

?>
	<script src="/js/d3.v4.min.js"></script>
	<div class="graphs">
		<div id="svg" class="view-<?= $view ?>">
			<svg width="1650" height="<?= $height ?>"></svg>
		</div>
		<div class="options">
			<form method="get" action="">
				<input type="hidden" name="p" value="graphs"/>
				<label><input name="view" type="radio" value="sum"<?= $view == 'sum' ? ' checked' : '' ?>> Цены, ₽</label><br />
				<label><input name="view" type="radio" value="cnt"<?= $view == 'cnt' ? ' checked' : '' ?>> Количество, шт.</label><br /><?

				if (!$hideStores)
				{
					?>
					<h3>Склады</h3>
					<label><input name="store" type="radio" value="0"<?= !$selectedStore ? ' checked' : '' ?>> Сумма</label><br/><?

					foreach ($stores['ITEMS'] as $st)
					{
						$checked = ($selectedStore == $st['ID']) ? ' checked' : '';
						?><label><input name="store" type="radio"
										value="<?= $st['ID'] ?>"<?= $checked ?>> <?= $st['NAME'] ?></label><br/><?
					}
				}
				?>
			</form>
		</div>
	</div>
	<div id="rect-modal"></div>
	<script>
		var D = []; <?= $D ?>
		var S = []; <?= $S ?>
		var dateMin = new Date('<?= $dateMinF ?>');
		var dateMax = new Date('<?= $dateMaxF ?>');
		var priceMin = <?= $priceMin ?>;
		var priceMax = <?= $priceMax ?>;
		var cntMin = <?= $cntMin ?>;
		var cntMax = <?= $cntMax ?>;
		var stocksMax = <?= $stocksMax ?>;
		var height = <?= $height ?>;
		var dateFrom = new Date('<?= $dateFromF ?>');
	</script>
<?