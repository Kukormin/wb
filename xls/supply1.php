<?
/** @var CMain $APPLICATION */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!\Local\System\User::isLogged()) die();

$storeId = $_REQUEST['store'];
if ($storeId == -1)
    $storeId = \Local\Main\Stores::PODOLSK_ID;

$store = \Local\Main\Stores::getById($storeId);
if (!$store)
    return;

$incl = $_REQUEST['incl'];
if (!$incl)
	return;

@set_time_limit(0);
ini_set('memory_limit', '2048M');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$styleArray = [
	'font' => [
		'bold' => true,
		'color' => [
			'argb' => 'FFFFFFFF',
		],
	],
	'fill' => [
		'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
		'color' => [
			'argb' => 'FF653f96',
		],
	],
];

$item = [
	'Бренд',
	'Артикул ИМТ',
	'Артикул Цвета',
	'Размер',
	'Предмет',
	'Пол',
	'Количество',
	'Розничная цена RU',
	'Розничная цена BY',
	'Розничная цена KZ',
	'Цена за штуку',
	'Валюта',
	'базценаопт',
	'Баркод (не заполнять, колонка для информации)',
	'Запросов в листе ожидания',
	'Общий дефицит',
	'в т.ч. Новосибирск',
	'в т.ч. Хабаровск',
	'в т.ч. Краснодар',
	'в т.ч. Екатеринбург',
	'в т.ч. Санкт-Петербург',
];
foreach ($item as $col => $value)
{
	$cell = $sheet->getCellByColumnAndRow($col + 1, 1);
	$cell->setValue($value);
}
$sheet->getStyle('A1:U1')->applyFromArray($styleArray);

$row = 1;
foreach ($_REQUEST['r'] as $id => $R)
{
	if ($R <= 0)
		continue;

	$incl = $_REQUEST['incl'][$id] == 'on';
	if (!$incl)
		continue;

	$offer = \Local\Main\Offers::getById($id);
	$product = \Local\Main\Products::getById($offer['PRODUCT']);
	$brand = \Local\Main\Brands::getById($product['BRAND']);
	$section = \Local\Main\Sections::getById($product['SECTION']);
	$price = $product['PRICE'];
	$discount = $product['DISCOUNT'];
	if ($discount)
		$price *= (1 - $discount / 100);
	$margin = \Local\System\Utils::getWbMargin($price);
	$bars = explode(',', $offer['BAR']);
	$bar = $bars[count($bars) - 1];
	$item = [
		1 => $brand['NAME'],
		2 => $product['ARTICLE_IMT'],
		3 => $product['ARTICLE_COLOR'],
		4 => $offer['SIZE'],
		5 => $section['NAME'],
		7 => $R,
		11 => $price - $margin,
		12 => 'руб',
		14 => $bar,
		16 => $_REQUEST['d'][$id],
	];

	$row++;
	foreach ($item as $col => $value)
	{
		$cell = $sheet->getCellByColumnAndRow($col, $row);
		$cell->setValue($value);
		if ($col === 14)
			$cell->setDataType(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
	}

}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
//$res = $writer->save($_SERVER["DOCUMENT_ROOT"] . '/1.xlsx');

$resultfileName = $prefix . 'supply' . $_REQUEST['k'] . '_' . $store['EN'] . date('_Y_m_d') . '.xlsx';

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $resultfileName . '"');
header('Cache-Control: max-age=0');

$writer->save("php://output");

