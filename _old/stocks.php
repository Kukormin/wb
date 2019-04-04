<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Остатки</title>
</head>
<body><?

if (!function_exists('DebugMessage')) {
	function DebugMessage($message, $backtrace = false, $title = false, $color = '#008B8B') {
		if ($_SERVER["REMOTE_ADDR"] == '109.197.195.38' || $_SERVER["REMOTE_ADDR"] == '192.168.0.101')
		{
			?><table border="0" cellpadding="5" cellspacing="0" style="border:1px solid <?=$color?>;margin:2px;"><tr><td style="color:<?=$color?>;font-size:11px;font-family:Verdana;"><?
					if(strlen($title)) {
						?><p>[<?=$title?>]</p><?
					}
					if (is_array($message) || is_object($message)) {
						echo '<pre>'; print_r($message); echo '</pre>';
					}
					else {
						var_dump($message);
					}
					$bt = array();
					if($backtrace && function_exists('debug_backtrace'))
					{
						$arBacktrace = debug_backtrace();
						$iterationsCount = min(count($arBacktrace), 18);
						for ($i = 1; $i < $iterationsCount; $i++)
						{
							$s = $arBacktrace[$i]['function'];
							if (strlen($arBacktrace[$i]['class']))
								$s = $arBacktrace[$i]['class'] . '::' . $s;
							$bt[] = $s;
						}
						echo '<pre>';
						print_r($bt);
						echo '</pre>';
					}
					?></td></tr></table><?
		}
	}
}

$basePath = realpath(dirname(__FILE__));
$path = $basePath . '/stocks/';

if ($_POST['send'])
{
	$dir = date('Y-m-d-H-i');
	$aPath = $path . $dir;
	mkdir($aPath, 0755, true);
	copy($_FILES['wb_stocks']['tmp_name'], $aPath . '/wb_stocks.xls');
	copy($_FILES['wb_deficit']['tmp_name'], $aPath . '/wb_deficit.xls');
	copy($_FILES['avail']['tmp_name'], $aPath . '/avail.xls');
	copy($_FILES['target']['tmp_name'], $aPath . '/target.xls');

	header('Location: ?a=' . $dir);
}
elseif ($_GET['a'])
{
	set_time_limit(0);
	
	$dir = $_GET['a'];
	$aPath = $path . $dir;
	
	$data = [];

	require_once ($basePath . '/PHPExcel.php');
	
	$excel = \PHPExcel_IOFactory::load($aPath . '/wb_stocks.xls');
	$sheet = $excel->getSheet(0);
	$ar = $sheet->toArray();

	$jKey = false;
	$jValue = false;
	foreach ($ar as $row)
	{
		if ($jKey === false || $jValue === false)
		{
			foreach ($row as $j => $td)
			{
				if ($td == 'Баркод')
					$jKey = $j;
				if ($td == 'Подольск')
					$jValue = $j;
			}
		}
		else
		{
			$key = $row[$jKey];
			$value = intval(trim($row[$jValue], '$'));
			if ($key)
				$data[$key]['C'] = $value;
		}
	}
	
	$excel = \PHPExcel_IOFactory::load($aPath . '/wb_deficit.xls');
	$sheet = $excel->getSheet(0);
	$ar = $sheet->toArray();

	$jKey = false;
	$jValue = false;
	foreach ($ar as $row)
	{
		if ($jKey === false || $jValue === false)
		{
			foreach ($row as $j => $td)
			{
				if ($td == 'Баркод')
					$jKey = $j;
				if ($td == 'Общий дефицит')
					$jValue = $j;
			}
		}
		else
		{
			$key = $row[$jKey];
			$value = intval($row[$jValue]);
			if ($key)
				$data[$key]['D'] = $value;
		}
	}
	
	$excel = \PHPExcel_IOFactory::load($aPath . '/avail.xls');
	$sheet = $excel->getSheet(0);
	$ar = $sheet->toArray();

	$jKey = 0;
	$jValue = false;
	$jArticle = false;
	foreach ($ar as $row)
	{
		if ($jKey === false || $jValue === false)
		{
			foreach ($row as $j => $td)
			{
				if ($td == 'Конечный остаток')
					$jValue = $j;
				elseif ($td == 'Артикул')
					$jArticle = $j;
			}
		}
		else
		{
			$key = $row[$jKey];
			$value = intval(str_replace(',', '', $row[$jValue]));
			if ($key == 'Итого')
				break;
			
			if ($key && $key != 'Общий склад')
			{
				$data[$key]['S'] = $value;
				$data[$key]['Art'] = $row[$jArticle];
			}
		}
	}
	
	$excel = \PHPExcel_IOFactory::load($aPath . '/target.xls');
	$sheet = $excel->getSheet(0);
	$ar = $sheet->toArray();

	$jKey = false;
	$jValue = false;
	$jNn = false;
	foreach ($ar as $row)
	{
		if ($jKey === false || $jValue === false)
		{
			foreach ($row as $j => $td)
			{
				if ($td == 'Баркод')
					$jKey = $j;
				if ($td == 'Остаток')
					$jValue = $j;
				if ($td == 'Номенклатура')
					$jNn = $j;
			}
		}
		else
		{
			$key = $row[$jKey];
			$value = intval($row[$jValue]);
			if ($key)
			{
				$data[$key]['N'] = $value;
				$data[$key]['Nomen'] = $row[$jNn];
			}
		}
	}
	
	$counts = [
		'ALL' => 0,
		'NO_TARGET' => 0,
		'MORE' => 0,
		'NO_STOCKS' => 0,
		'S' => 0,
		'D' => 0,
		'OK' => 0,
	];
	
	$xls = new PHPExcel();
	$xls->setActiveSheetIndex(0);
	$sheet = $xls->getActiveSheet();
	$sheet->setTitle('Отчет');
	
	$headers = [
		[
			'CODE' => 'A',
			'NAME' => 'Баркод',
			'W' => 20,
		],
		[
			'CODE' => 'B',
			'NAME' => 'Номенклатура',
			'W' => 20,
		],
		[
			'CODE' => 'C',
			'NAME' => 'Артикул',
			'W' => 20,
		],
		[
			'CODE' => 'D',
			'NAME' => 'Требуемое количество',
			'W' => 23,
		],
		[
			'CODE' => 'E',
			'NAME' => 'Остаток на складе',
			'W' => 20,
		],
		[
			'CODE' => 'F',
			'NAME' => 'Остаток WB',
			'W' => 20,
		],
		[
			'CODE' => 'G',
			'NAME' => 'Дефицит WB',
			'W' => 20,
		],
		[
			'CODE' => 'H',
			'NAME' => 'X',
			'W' => 8,
		],
		[
			'CODE' => 'I',
			'NAME' => 'R',
			'W' => 8,
		],
		[
			'CODE' => 'J',
			'NAME' => 'Сообщение',
			'W' => 80,
		],
	];
	foreach ($headers as $i => $head)
	{
		$cell = $head['CODE'] . '1';
		$sheet->setCellValue($cell, $head['NAME']);
		$sheet->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('4F3076');
		$sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
		$sheet->getColumnDimension($head['CODE'])->setWidth($head['W']);
	}
	
	$i = 1;
	foreach ($data as $key => $item)
	{
		$counts['ALL']++;
		$text = '';
		$X = false;
		$R = false;
		if (!isset($item['N']))
		{
			$text = 'Не задано требуемое количество';
			$counts['NO_TARGET']++;
		}
		else
		{
			$X = $item['N'] - $item['C'];
			if ($X <= 0)
			{
				$R = 0;
				if ($X < 0)
				{
					$text = 'Остатков больше, чем требуется';
					$counts['MORE']++;
				}
			}
			elseif ($X > 0)
			{
				$R = $X;
				if (!isset($item['S']))
				{
					$R = 0;
					$text = 'Не найдена информация о количестве на складе';
					$counts['NO_STOCKS']++;
				}
				else
				{
					if ($item['S'] < $R)
					{
						$R = $item['S'];
						$text = 'Недостаточно на складе';
						$counts['S']++;
					}
					if ($item['D'] < $R)
					{
						$R = $item['D'];
						$text = 'Дефицит не позволяет отправить больше';
						$counts['D']++;
					}
				}
			}
		}
		if (!$text)
			$counts['OK']++;
		
		$data[$key]['RES'] = $R;
		
		$i++;
		$sheet->getStyle("A$i")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
		$sheet->getStyle("B$i")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
		$sheet->getStyle("C$i")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		$sheet->setCellValue("A$i", $key);
		$sheet->setCellValue("B$i", isset($item['Nomen']) ? $item['Nomen'] : '-');
		$sheet->setCellValue("C$i", isset($item['Art']) ? $item['Art'] : '-');
		$sheet->setCellValue("D$i", isset($item['N']) ? $item['N'] : '-');
		$sheet->setCellValue("E$i", isset($item['S']) ? $item['S'] : '-');
		$sheet->setCellValue("F$i", isset($item['C']) ? $item['C'] : '-');
		$sheet->setCellValue("G$i", isset($item['D']) ? $item['D'] : '-');
		$sheet->setCellValue("H$i", $X !== false ? $X : '-');
		$sheet->setCellValue("I$i", $R !== false ? $R : '-');
		$sheet->setCellValue("J$i", $text);
	}
	
	$objWriter = new PHPExcel_Writer_Excel5($xls);
	$objWriter->save($aPath . '/report.xls');

	?><p>Всего: <?= $counts['ALL'] ?></p><?
	?><p>Не задано требуемое количество: <?= $counts['NO_TARGET'] ?></p><?
	?><p>Остатков больше, чем требуется: <?= $counts['MORE'] ?></p><?
	?><p>Не найдена информация о количестве на складе: <?= $counts['NO_STOCKS'] ?></p><?
	?><p>Недостаточно на складе: <?= $counts['S'] ?></p><?
	?><p>Дефицит не позволяет отправить больше: <?= $counts['D'] ?></p><?
	?><p><a href="/wb/stocks/<?= $dir ?>/result.xls">Результат для отправки</a></p><?
	?><p><a href="/wb/stocks/<?= $dir ?>/report.xls">Подробный отчет</a></p><?


	$excel = \PHPExcel_IOFactory::load($aPath . '/wb_deficit.xls');
	$sheet = $excel->getSheet(0);

	$jKey = false;
	$jRes = false;
	$rowIterator = $sheet->getRowIterator();
	$deleteRows = [];
	foreach ($rowIterator as $i => $row)
	{
		$cellIterator = $row->getCellIterator();
		$key = 0;
		$resCell = null;
		foreach ($cellIterator as $j => $cell)
		{
			if ($jKey === false || $jRes === false)
			{
				$td = $cell->getValue();
				if ($td == 'Баркод')
					$jKey = $j;
				if ($td == 'Количество')
					$jRes = $j;
			}
			else
			{
				if ($j === $jKey)
					$key = $cell->getValue();
				elseif ($j === $jRes)
					$resCell = $cell;
			}
		}
		
		if ($key && $resCell)
		{
			if (isset($data[$key]['RES']))
				$resCell->setValue($data[$key]['RES']);
		}
	}
	
	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	$r = $writer->save($aPath . '/result.xls');
	
	
	/*$excel = \PHPExcel_IOFactory::load($aPath . '/result.xls');
	$sheet = $excel->getSheet(0);

	rsort($deleteRows);
	foreach ($deleteRows as $i)
		$sheet->removeRow($i, 1);
	
	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	$r = $writer->save($aPath . '/result.xls');*/
	
}
else
{
	$ar = [];

	$dirs = scandir($path);
	foreach ($dirs as $dir)
	{
		if ($dir == '.' || $dir == '..')
			continue;

		if (strlen($dir) < 14)
			continue;

		$ar[] = $dir;
	}

	if ($ar)
	{
		?>
		<h3>Ранее загруженные группы файлов</h3><?
		foreach ($ar as $dir)
		{
			?>
			<p><a href="?a=<?= $dir ?>"><?= $dir ?></a></p><?
		}
	}

	?>
	<h3>Новая группа файлов</h3>
	<form action="" method="post" enctype="multipart/form-data">
		Остатки WildBerries.ru:<br/>
		<input name="wb_stocks" type="file"/><br/>
		<br/>
		Дефицит WildBerries.ru:<br/>
		<input name="wb_deficit" type="file"/><br/>
		<br/>
		Наличие на складе:<br/>
		<input name="avail" type="file"/><br/>
		<br/>
		Установленные значения:<br/>
		<input name="target" type="file"/><br/>
		<br/>
		
		<input type="submit" name="send" value="Отправить"/>
	</form><?
}