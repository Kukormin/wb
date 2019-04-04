<?
if (!$_SERVER["DOCUMENT_ROOT"]) {
	error_reporting(0);
	setlocale(LC_ALL, 'ru.UTF-8');
	$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__));
	$bConsole = true;
}
else {
	$bConsole = false;
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

@set_time_limit(0);
ini_set('memory_limit', '2048M');

if (!$bConsole)
	echo '<pre>';


/*$to = 'tim.kukom@gmail.com';
$s = 'test';
$head = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/_dev/head.txt');
$mess = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/_dev/mess.txt');
$res = mail($to, $s, $mess, $head);
debugmessage($res);*/

/*$data = [];
$offers = \Local\Main\Offers::getAll(true);
foreach ($offers['ITEMS'] as $offer)
{
	$productId = $offer['PRODUCT'];
	$size = $offer['SIZE'];
	$data[$productId][$size][$offer['BAR']] = $offer['ID'];
}

$el = new \CIBlockElement();
foreach ($data as $productId => $ar)
	foreach ($ar as $size => $ar1)
	{
		if (count($ar1) > 1)
		{
			debugmessage($ar1);
			$firstId = false;
			$newBar = '';
			foreach ($ar1 as $bar => $id)
			{
				if ($firstId === false)
					$firstId = $id;
				else
				{
					$sales = \Local\Main\Sales::getByOffer($id);
					foreach ($sales as $item)
						\Local\Main\Sales::delete($item['ID']);
					\Local\Main\Offers::Delete($id);
				}
				if ($newBar)
					$newBar .= ',';
				$newBar .= $bar;
			}

			$el->Update($firstId, ['XML_ID' => $newBar]);
		}
	}*/

$file = $_SERVER['DOCUMENT_ROOT'] . '/_import/realization/list.html';
$s = file_get_contents($file);
$log = '';
\Local\Import\Parser::realization($s, $log);

//Import::realization();
//Local\Import\Loader::shipping();
//Local\Import\Loader::nomenclature();
//Local\Import\Loader::storeStocksAndPrices();
//Local\Import\Loader::priceHistory();
//Local\Import\Loader::sales();
//\Local\Import\Loader::salesDate('25.06.2018');

/*$ts = MakeTimeStamp('01.04.2018');
$now = MakeTimeStamp('06.08.2018');
$i = 0;
while ($ts <= $now)
{
	$dateF = date('d.m.Y', $ts);

	Local\Import\Loader::salesDate($dateF);

	$ts += 86400;
}*/


/*$file = $_SERVER['DOCUMENT_ROOT'] . '/_import/sales/2017_07/12.07.2017.csv';
$s = file_get_contents($file);
\Local\Import\Parser::sales($s, '12.07.2017');


//\Local\Main\Import::priceHistory();
/*$file = $_SERVER['DOCUMENT_ROOT'] . '/data/3.html';
$s = file_get_contents($file);
\Local\Main\Import::parsePriceHistory($s);*/

/*$hist = \Local\Main\PriceHistory::getByXmlId(107787);
$file = $_SERVER['DOCUMENT_ROOT'] . '/data/priceHistory/107787.html';
$s = file_get_contents($file);
\Local\Main\Import::parsePriceHistoryItem($hist, $s);*/

if (!$bConsole)
	echo '</pre>';