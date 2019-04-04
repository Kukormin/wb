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

$fileName = \Local\Main\Deficit::loadLast();
try
{
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($fileName);

    $sheet = $spreadsheet->getSheet(0);
    $loaded = true;
} catch (\Exception $e)
{
    $loaded = false;
}

if (!$loaded)
{
	echo 'Ошибка загрузки шаблона xls';

	return;
}

$newSheet = clone $sheet;
$newSheet->setTitle('Поставка ' . $store['NAME']);
$spreadsheet->addSheet($newSheet);
$current = 1;

$jIMT = false;
$jColor = false;
$jSize = false;
$jRes = false;
$rowIterator = $sheet->getRowIterator();
foreach ($rowIterator as $rowIndex => $row)
{
	$cellIterator = $row->getCellIterator();
	$IMT = '';
	$color = '';
	$size = '';
	foreach ($cellIterator as $j => $cell)
	{
		if ($rowIndex == 1)
		{
			$td = $cell->getValue();
			if ($td instanceof PhpOffice\PhpSpreadsheet\RichText\RichText)
				$text = $td->getPlainText();
			else
				$text = $td;

			if ($text == 'Артикул ИМТ')
				$jIMT = $j;
			elseif ($text == 'Артикул Цвета')
				$jColor = $j;
			elseif ($text == 'Размер')
				$jSize = $j;
			elseif ($text == 'Количество')
				$jRes = $j;
		}
		else
		{
			if ($j === $jIMT)
			{
				/** @var $td PhpOffice\PhpSpreadsheet\RichText\RichText */
				$td = $cell->getValue();
				if ($td instanceof PhpOffice\PhpSpreadsheet\RichText\RichText)
					$IMT = $td->getPlainText();
				else
					$IMT = $td;
			}
			elseif ($j === $jColor)
			{
				/** @var $td PhpOffice\PhpSpreadsheet\RichText\RichText */
				$td = $cell->getValue();
				if ($td instanceof PhpOffice\PhpSpreadsheet\RichText\RichText)
					$color = $td->getPlainText();
				else
					$color = $td;
			}
			elseif ($j === $jSize)
			{
				/** @var $td PhpOffice\PhpSpreadsheet\RichText\RichText */
				$td = $cell->getValue();
				if ($td instanceof PhpOffice\PhpSpreadsheet\RichText\RichText)
					$size = $td->getPlainText();
				else
					$size = $td;
			}
		}
	}

	if ($rowIndex > 1)
	{
		if ($jIMT === false || $jColor === false || $jSize === false)
		{
			echo 'Не найдены ключевые заголовки';

			return;
		}

		if (!$color)
			continue;

		$code = $color;
		if ($IMT)
			$code = $IMT . $code;
		$product = \Local\Main\Products::getByCode($code);
		if (!$product)
			continue;

		$offer = \Local\Main\Offers::getByProductSize($product['ID'], $size);
		if (!$offer)
			continue;

		if (isset($incl[$offer['ID']]) && !$incl[$offer['ID']])
			continue;

		$R = $_REQUEST['r'][$offer['ID']];
		if ($R <= 0)
			continue;

		$current++;
		$jIndex = 0;
		foreach ($cellIterator as $j => $cell)
		{
			if ($j == $jRes)
				$val = $R;
			else
				$val = $cell->getValue();
			$jIndex++;
			$newSheet->setCellValueByColumnAndRow($jIndex, $current, $val);
		}
	}
}

$newSheet->removeRow($current + 1, $newSheet->getHighestRow() - $current - 1);
$cnt = $spreadsheet->getSheetCount();
for ($i = $cnt - 2; $i >= 0; $i--)
	$spreadsheet->removeSheetByIndex($i);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
//$res = $writer->save($_SERVER["DOCUMENT_ROOT"] . '/1.xlsx');

$resultfileName = $prefix . 'supply' . $_REQUEST['k'] . '_' . $store['EN'] . date('_Y_m_d') . '.xlsx';

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $resultfileName . '"');
header('Cache-Control: max-age=0');

$writer->save("php://output");

