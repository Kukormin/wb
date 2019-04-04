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

$file = $_SERVER['DOCUMENT_ROOT'] . '/_import/sales/2018_11/03.11.2018_podolsk.csv';
$s = file_get_contents($file);
$log = '';
$dateF = '03.11.2018';
$store = \Local\Main\Stores::getById(4);
\Local\Import\Parser::sales($s, $dateF, $store, $log);

if (!$bConsole)
	echo '</pre>';