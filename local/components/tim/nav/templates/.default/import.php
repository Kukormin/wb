<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$from = time() - 86400 * 7;
$log = \Local\Import\Log::getByDate($from);

$titles = [
	'nomenclature' => [
		'PRODUCTS' => 'Товаров в БД',
		'OFFERS' => 'Предложений в БД',
		'ROWS' => 'Всего строк в файле импорта',
		'SKIP' => 'Пропущено строк',
		'NEW_PRODUCTS' => 'Новых товаров',
		'NEW_OFFERS' => 'Новых предложений',
		'ERROR_PRODUCTS' => 'Ошибок товаров',
		'ERROR_OFFERS' => 'Ошибок предложений',
	],
	'deficit' => [
		'ROWS' => 'Всего строк',
		'ERROR_PRODUCTS' => 'Не найдено товаров',
		'ERROR_OFFERS' => 'Не найдено предложений',
		'ADDED' => 'Добавлено записей по общему дефициту',
		'UPD' => 'Изменений по складам',
		'Z' => 'Обнулений дефицита по складам',
	],
	'storeStocksAndPrices' => [
		'ROWS' => 'Всего строк',
		'ERROR_PRODUCTS' => 'Не найдено товаров',
		'ERROR_OFFERS' => 'Не найдено предложений',
		'PRICE_UPDATED' => 'Товаров, у которых обновились цены или скидки',
		'OFFERS_UPDATED' => 'Предложений, у которых обновились остатки',
		'STOCKS_UPDATED' => 'Изменений остатков по складам',
		'STOCKS_Z' => 'Остатков обнулено',
		'H_ADDED' => 'История. Новых элементов',
		'H_EXISTS' => 'История. Без изменения',
		'H_CHANGED' => 'История. Изменено',
		'H_ERROR' => 'История. Ошибок',
		'H_SKIP' => 'История. Пропущено',
	],
	'priceHistory' => [
		'ROWS' => 'Всего строк',
		'ERROR_PRODUCTS' => 'Не найдено товаров',
		'PRICE_UPDATED' => 'Товаров, у которых обновились цены или скидки',
	],
	'sales' => [
		'OFFERS' => 'Предложений в БД',
		'DAYS' => 'Дней с продажами загружено',
		'ROWS' => 'Всего строк',
		'ERROR_OFFERS' => 'Не найдено предложений',
		'SKIP' => 'Пропущено строк',
		'ADDED' => 'Добавлено элементов',
		'EXISTS' => 'Без изменений',
		'CHANGED' => 'Элементов изменено',
		'ERROR' => 'Ошибок добавления',
	],
	'shipping' => [
		'ROWS' => 'Всего строк',
		'ERROR_BAR' => 'Не задан ШК',
		'ERROR_PRODUCTS' => 'Не найдено товаров',
		'ERROR_OFFERS' => 'Не найдено предложений',
		'UPDATED' => 'Обновлено предложений',
	],
	'prices' => [
		'ALL' => 'Всего товаров',
		'SKIP' => 'Пропущено',
		'NULL' => 'Нет данных',
		'OK' => 'Добавлено',
		'LOAD' => 'Ошибок загрузки',
		'PARSE' => 'Ошибок распознавания',
		'WRONG' => 'Ошибок WB',
	],
];

?>
	<h3 class="page-title">Запуск скриптов импорта вручную</h3>
	<ul><?

		$imports = \Local\Import\Imports::getAll();
		foreach ($imports['ITEMS'] as $item)
		{
			?>
			<li><a target="_blank" href="<?= $item['CODE'] ?>"><?= $item['NAME'] ?></a></li><?
		}

		?>
	</ul>
	<h2 class="page-title">Журнал</h2>
	<table class="fix log-table">
		<colgroup width="300">
		<colgroup width="100">
		<colgroup width="160">
		<colgroup width="200">
		<colgroup width="100">
		<colgroup width="300">
		<colgroup width="50">
		<thead>
		<tr>
			<th>Импорт</th>
			<th>Вручную</th>
			<th>Начало</th>
			<th>Продложительность, с</th>
			<th>Успешно</th>
			<th>Подробности</th>
			<th>...</th>
		</tr>
		</thead>

		<tbody><?

		foreach ($log as $item)
		{
			$import = \Local\Import\Imports::getById($item['IMPORT']);

			$manual = '';
			if ($item['MANUAL'])
			{
				$manual = 'да';
				if ($item['USER'])
				{
					$user = \Local\System\User::getById($item['USER']);
					if ($user['NAME'])
						$manual = $user['NAME'];
					else
						$manual = $user['LOGIN'];
				}
			}

			$begin = date('d.m.Y H:i:s', $item['BEGIN']);
			$t = $item['END'] - $item['BEGIN'];
			$success = ($item['SUCCESS']) ? 'да' : '';


			$showInfo = $item['DATA']['COUNTS'];
			$class = $showInfo ? 'wa ' : '';
			if (!$item['SUCCESS'])
				$class .= 'error';
			elseif ($item['DATA']['WARNINGS'])
				$class .= 'warnings';

			$add = '';
			if ($item['DATA']['ERRORS'])
				$add = implode('<br>', $item['DATA']['ERRORS']);
			elseif ($item['DATA']['TEXT'])
				$add = $item['DATA']['TEXT'];

			$more = $showInfo ? '...' : '';

			?>
			<tr class="<?= $class ?>">
				<td class="tal"><?= $import['NAME'] ?></td>
				<td><?= $manual ?></td>
				<td><?= $begin ?></td>
				<td><?= number_format($t, 2, ',', '') ?></td>
				<td><?= $success ?></td>
				<td><?= $add ?></td>
				<td><?= $more ?></td>
			</tr><?

			if ($showInfo)
			{
				?>
				<tr class="add-log">
					<td colspan="7" class="tal"><?

						if ($item['DATA']['COUNTS'])
						{
							?>
							<dl class="log"><?

								foreach ($item['DATA']['COUNTS'] as $k => $v)
								{
									$dt = $k;
									if (isset($titles[$import['XML_ID']][$k]))
										$dt = $titles[$import['XML_ID']][$k];
									?>
									<dt><?= $dt ?>:</dt>
									<dd><?= $v ?></dd><?
								}

								?>
							</dl><?
						}

						?>
					</td>
				</tr><?
			}
		}

		?>
		</tbody>
	</table>
