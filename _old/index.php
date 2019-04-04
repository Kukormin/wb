<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>WB</title>
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

$path = realpath(dirname(__FILE__));
if ($_POST['send'])
{
	$dir = date('Y-m-d-H-i');
	$path = $path . '/' . $dir;
	mkdir($path, 0755, true);
	copy($_FILES['torg']['tmp_name'],$path . '/torg.xls');
	foreach ($_FILES['wb']['tmp_name'] as $i => $tmp)
	{
		$name = $_FILES['wb']['name'][$i];
		copy($tmp, $path . '/' . $name);
	}

	header('Location: ?a=' . $dir);
}
elseif ($_GET['a'])
{
	$dir = $_GET['a'];
	$dir = $path . '/' . $dir;
	$files = scandir($dir);
	$dir .= '/';
	$torg = $dir . 'torg.xls';
	$wb = [];
	foreach ($files as $file)
	{
		if ($file == '.' || $file == '..')
			continue;

		if ($file == 'torg.xls')
			continue;

		$wb[] = $file;
	}

	$res = [];
	$wbDouble = [];
	$resWb = [];
	$fileByJ = [];
	$wbCodes = [];

	foreach ($wb as $j => $filename)
	{
		$fileByJ[$j] = $filename;
		$s = file_get_contents($dir . $filename);

		$ar1 = explode('ShkId="', $s);
		foreach ($ar1 as $i => $s1)
		{
			if (!$i)
				continue;

			$ar2 = explode('" Barcode="', $s1);
			$ar3 = explode('" BindDate', $ar2[1]);
			$ShkId = trim($ar2[0]);
			$Barcode = trim($ar3[0]);

			if (isset($res[$Barcode][$ShkId]))
				$wbDouble[$ShkId][] = $j;
			else
				$res[$Barcode][$ShkId] = $j;

			$resWb[$ShkId] = $ShkId;
			$wbCodes[$ShkId][] = $Barcode;
		}
	}

	require_once ($path . '/PHPExcel.php');
	$excel = \PHPExcel_IOFactory::load($torg);

	$sheet = $excel->getSheet(0);
	$ar = $sheet->toArray();

	$sum = 0;
	$torgData = [];
	foreach ($ar as $row)
	{
		if (is_numeric($row[1]) && $row[2] && strlen($row[6]) > 7)
		{
			$cnt = intval($row[17]);
			$sum += $cnt;
			$code = trim($row[6]);
			$torgData[$row[6]] = [
				'ID' => intval($row[1]),
				'NAME' => trim($row[2]),
				'CNT' => $cnt,
			];
		}
	}

	?><p>Позиций в ТОРГ: <?= count($torgData) ?></p><?
	?><p>Количество в ТОРГ: <?= $sum ?></p><?
	?><p>Позиций в WB файлах: <?= count($res) ?></p><?
	?><p>Количество уникальных WB кодов: <?= count($resWb) ?></p><?
	?><p>Интервалы кодов:</p><ul><?

	sort($resWb);

	$cur = [];
	$start = 0;
	$end = 0;
	$pred = 0;
	$cnt = 0;
	foreach ($resWb as $wb)
	{
		if (!$start)
		{
			$cur = [];
			$start = $wb;
			$pred = $wb;
			$cnt = 0;
			continue;
		}

		if ($wb - $pred > 4)
		{
		    $serr = '';
			$err = [];
			foreach ($cur as $i => $s)
			{
				if (!$i)
					continue;

				$r = $s - $cur[$i - 1];
				if ($r != 2 && $r != 4)
					$err[] = '[' . $s . '-' . $cur[$i - 1] . ']';
			}
			if ($err)
			{
				$serr = ' - Ошибки: ' . implode(',', $err);
			}

			$end = $pred;
			if ($start == $end)
			{
				?><li><?= $start ?></li><?
			}
			else
			{
				?><li><?= $start ?> - <?= $end ?> (<?= $cnt ?>)<?= $serr ?></li><?
			}


			$cur = [];
			$start = $wb;
			$cnt = 0;
		}

		$pred = $wb;
		$cur[] = $wb;
		$cnt++;
	}
	$serr = '';
	$err = [];
	foreach ($cur as $i => $s)
	{
		if (!$i)
			continue;

		$r = $s - $cur[$i - 1];
		if ($r != 2 && $r != 4)
			$err[] = '[' . $s . '-' . $cur[$i - 1] . ']';
	}
	if ($err)
	{
		$serr = ' - Ошибки: ' . implode(',', $err);
	}

	$end = $pred;
	if ($start == $end)
	{
		?><li><?= $start ?></li><?
	}
	else
	{
		?><li><?= $start ?> - <?= $end ?> (<?= $cnt ?>)<?= $serr ?></li><?
	}

	?></ul><?

	// Проверка на соответствие количества
	foreach ($torgData as $code => $data)
	{
		if (count($res[$code]) != $data['CNT'])
		{
			?><hr /><?
			?><p><?= $code ?> - Количество не совпадает</p><?
			?><p>[<?= $data['ID'] ?>] - <?= $data['NAME'] ?>. Количество: <?= $data['CNT'] ?></p><?
			?><p>Коды WB (<?= count($res[$code]) ?>):</p><ul><?
			foreach ($res[$code] as $wb => $j)
			{
				?><li><?= $wb ?> - <?= $fileByJ[$j] ?></li><?
			}
			?></ul><?
		}
	}

	// Проверка на лишние
	foreach ($res as $code => $data)
	{
		if (!$torgData[$code])
		{
			?><hr /><?
			?><p><?= $code ?> - Отсутствует в ТОРГ файле</p><?
			?><p>Коды WB (<?= count($data) ?>):</p><ul><?
			foreach ($data as $wb => $j)
			{
				?><li><?= $wb ?> - <?= $fileByJ[$j] ?></li><?
			}
			?></ul><?
		}
	}

	foreach ($wbCodes as $wb => $ar)
	{
		if (count($ar) > 1)
		{
			?><hr /><?
			?><p>"<?= $wb ?>" - Дублирование WB кода (<?= count($ar) ?>)</p><?
		}
		if (strlen($wb) !== 9)
		{
			?><hr /><?
			?><p>"<?= $wb ?>" - Неверный формат</p><?
		}
	}

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
    Файл ТОРГ-12:<br/>
    <input name="torg" type="file"/><br/>
    <br/>
    Файлы WB:<br/>
    <div id="wb_files">
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/><br/>
        <input name="wb[]" type="file"/>
    </div>
    <input type="button" id="more" value="Еще"/><br/>
    <br/>
    <input type="submit" name="send" value="Отправить"/>
</form>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#more').click(function () {
			$('#wb_files').append('<br /><input name="wb[]" type="file" />');
		});
	});
</script><?
}