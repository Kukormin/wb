<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var string $reportFileName */

$time = filectime($reportFileName);
?>
	<div class="container">
		<h2>Информация</h2>
		<dl class="props props-realization">
			<dt>Период:</dt>
			<dd><?= $realization['WEEK'] ? 'неделя' : 'месяц' ?></dd>
			<dt>Время загрузки отчета:</dt>
			<dd><?= date('d.m.Y H:i', $time) ?> <a target="_blank" href="/xls/realizationItem.php?id=<?= $realization['XML_ID'] ?>">Обновить (скачать с WB)</a></dd>
			<dt>Интервал дат:</dt>
			<dd><?= $realization['FROM'] ?> - <?= $realization['TO'] ?></dd>
			<dt>Продажи, руб:</dt>
			<dd><?= number_format($realization['SALES'], 2, ',', ' ') ?></dd>
			<dt>Продажи себестоимость, руб:</dt>
			<dd><?= number_format($realization['COST'], 2, ',', ' ') ?></dd>
			<dt>Вознаграждение, руб:</dt>
			<dd><?= number_format($realization['FEE'], 2, ',', ' ') ?></dd>
			<dt>Средний процент скидки:</dt>
			<dd><?= $realization['DISCOUNT'] ?></dd>
		</dl>
	</div><?

?><?
