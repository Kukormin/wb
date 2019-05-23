<?
namespace Local\Import;

use Bitrix\Main\Type\DateTime;
use Local\Main\Brands;
use Local\Main\Collections;
use Local\Main\Contracts;
use Local\Main\Deficit;
use Local\Main\Offers;
use Local\Main\PriceHistory;
use Local\Main\Prices;
use Local\Main\Products;
use Local\Main\Realization;
use Local\Main\Sales;
use Local\Main\Sections;
use Local\Main\Shipping;
use Local\Main\Stocks;
use Local\Main\StocksHistory;
use Local\Main\Stores;
use Local\System\Utils;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

/**
 * Работа с отчетами от WB
 * Class Parser
 * @package Local\Import
 */
class Parser
{
	public static function nomenclature($accountId, $content, &$log)
	{
		if (ord($content[0]) === 239)
			$content = substr($content, 1);

		Brands::getAll(true);
		Sections::getAll(true);
		$products = Products::getAll(true);
		$offers = Offers::getAll(true);

		$map = [];
		$counts = [
			'PRODUCTS' => count($products['ITEMS']),
			'OFFERS' => count($offers['ITEMS']),
			'ROWS' => 0,
			'SKIP' => 0,
			'NEW_PRODUCTS' => 0,
			'NEW_OFFERS' => 0,
			'ERROR_PRODUCTS' => 0,
			'ERROR_OFFERS' => 0,
		];

		$rows = explode("\r\n", $content);
		foreach ($rows as $i => $row)
		{
			$ar = Common::strParts($row, ['"', '"']);
			if (count($ar) == 3)
				$row = $ar[0] . str_replace(',', '.', $ar[1]) . $ar[2];
			$parts = explode(',', $row);

			// Первая строка (с заголовками)
			// brand_name,subject_name,nm_id,chrt_id,sa,IMTsa,nsa,ts_name,barcode,price_ru,contents_names_list,MinWeight
			if (!$i)
			{
				if (count($parts) < 11)
				{
					Common::log('Количество столбцов отличается от ожидаемого: ' . count($parts));
					Common::addAdminNotify('Импорт номенклатуры: ошибка "Количество столбцов отличается от ожидаемого"');
					$log['ERRORS'][] = 'Количество столбцов отличается от ожидаемого';

					return;
				}

				$map = $parts;
			}
			else
			{
				if (!$row)
					continue;

				if (!$map)
				{
					Common::log('Отсутствует строка заголовков');
					Common::addAdminNotify('Импорт номенклатуры: ошибка "Отсутствует строка заголовков"');
					$log['ERRORS'][] = 'Отсутствует строка заголовков';

					break;
				}

				$counts['ROWS']++;

				$data = [];
				foreach ($parts as $j => $v)
				{
					$key = $map[$j];
					$data[$key] = trim($v);
				}

				$brandName = $data['brand_name'];
				if (!strlen($brandName))
				{
					$counts['SKIP']++;
					continue;
				}

				$brand = Brands::getByName($brandName, $accountId);
				if (!$brand)
					$brand = Brands::add($brandName, $accountId);
				if (!$brand)
				{
					$counts['SKIP']++;
					continue;
				}

				$xmlId = $data['nm_id'];
				$product = Products::getByXmlId($xmlId);
				if (!$product)
				{
					$sectionName = $data['subject_name'];
					$section = Sections::getByName($sectionName);
					if (!$section)
						$section = Sections::add($sectionName);
					if (!$section)
						Common::log('Ошибка раздела');

					$name = $sectionName;
					if ($name == 'Футболки')
						$name = 'Футболка';
					elseif ($name == 'Комбинезоны нательные для малышей')
						$name = 'Комбинезон нательный для малыша';
					elseif ($name == 'Майки бельевые')
						$name = 'Майка бельевая';
					elseif ($name == 'Комплекты нательные для малышей')
						$name = 'Комплект нательный для малыша';
					elseif ($name == 'Пижамы')
						$name = 'Пижама';
					elseif ($name == 'Комбинезоны')
						$name = 'Комбинезон';
					elseif ($name == 'Кофточки')
						$name = 'Кофточка';
					elseif ($name == 'Костюмы')
						$name = 'Костюм';
					elseif ($name == 'Песочники')
						$name = 'Песочник';
					elseif ($name == 'Распашонки')
						$name = 'Распашонка';
					elseif ($name == 'Чепчики')
						$name = 'Чепчик';
					elseif ($name == 'Водолазки')
						$name = 'Водолазка';
					elseif ($name == 'Лонгсливы')
						$name = 'Лонгслив';
					elseif ($name == 'Туники')
						$name = 'Туника';
					elseif ($name == 'Майки спортивные')
						$name = 'Майка спортивная';
					elseif ($name == 'Полукомбинезоны')
						$name = 'Полукомбинезон';
					elseif ($name == 'Свитшоты')
						$name = 'Свитшот';
					elseif ($name == 'Толстовки')
						$name = 'Толстовка';
					elseif ($name == 'Бомберы')
						$name = 'Бомбер';

					$defaultCollection = Collections::getDefaultId($brand['ID']);
					if (!$defaultCollection)
						$defaultCollection = Collections::addDefault($brand['ID']);
					$fields = [
						'NAME' => $name,
						'XML_ID' => $xmlId,
						'CODE' => $data['sa'],
						'IBLOCK_SECTION_ID' => $section['ID'],
						'PROPERTY_VALUES' => [
							'BRAND' => $brand['ID'],
							'ARTICLE_IMT' => $data['IMTsa'],
							'ARTICLE_COLOR' => $data['nsa'],
							'PRICE' => floatval($data['price_ru']),
							'START_PRICE' => floatval($data['price_ru']),
							'COLLECTION' => $defaultCollection,
						],
					];
					$product = Products::add($fields);

					$counts['NEW_PRODUCTS']++;
				}
				if (!$product)
				{
					$counts['ERROR_PRODUCTS']++;
					Common::log('Ошибка товара: ' . $xmlId);
					continue;
				}

				$bar = $data['barcode'];
				if (!$bar)
				{
					$counts['SKIP']++;
					continue;
				}
				$offer = Offers::getByBar($bar);
				if (!$offer)
				{
					$size = $data['ts_name'];
					$offer = Offers::getByProductSize($product['ID'], $size);
					if (!$offer)
					{
						$name = $product['NAME'] . ' - ' . $size;

						$fields = [
							'NAME' => $name,
							'CODE' => $data['chrt_id'],
							'XML_ID' => $bar,
							'PROPERTY_VALUES' => [
								'PRODUCT' => $product['ID'],
								'SIZE' => $size,
							],
						];
						$offer = Offers::add($fields);
					}
					else
						$offer = Offers::updateBar($offer['ID'], $offer['BAR'] . ',' . $bar);

					$counts['NEW_OFFERS']++;
				}
				if (!$offer)
				{
					$counts['ERROR_OFFERS']++;
					Common::log('Ошибка предложения: ' . $bar);
					continue;
				}
			}
		}

		$report = "Отчет:";
		$report .= "\nТоваров: " . $counts['PRODUCTS'];
		$report .= "\nПредложений: " . $counts['OFFERS'];
		$report .= "\nВсего строк: " . $counts['ROWS'];
		$report .= "\nПропущено: " . $counts['SKIP'];
		$report .= "\nНовых товаров: " . $counts['NEW_PRODUCTS'];
		$report .= "\nНовых предложений: " . $counts['NEW_OFFERS'];
		$report .= "\nОшибок товаров: " . $counts['ERROR_PRODUCTS'];
		$report .= "\nОшибок предложений: " . $counts['ERROR_OFFERS'];
		Common::log($report);

		$log['COUNTS'] = $counts;
		$log['TEXT'] = $counts['ROWS'] . ': +' . $counts['NEW_PRODUCTS'] . ', +' . $counts['NEW_OFFERS'];
		if ($counts['ROWS'] < 1000)
			$log['WARNINGS'] = true;

		if ($counts['NEW_PRODUCTS'] || $counts['NEW_OFFERS'])
		{
			$message = 'Новые элементы в номенклатуре: (новых товаров: ' . $counts['NEW_PRODUCTS'] . ', новых предложений: ' . $counts['NEW_OFFERS'] . ')';
			Common::addAdminNotify($message);

			if ($counts['NEW_PRODUCTS'])
				Products::getAll(true);
			if ($counts['NEW_OFFERS'])
				Offers::getAll(true);
		}
	}

	/**
	 * Обарботка отчета по остаткам и ценам
	 * @param $accountId
	 * @param $content
	 * @param $log
	 */
	public static function storeStocksAndPrices($accountId, $content, &$log)
	{
		if (ord($content[0]) === 239)
			$content = substr($content, 1);

		Products::getAll(true);
		Offers::getAll(true);
		$allStocks = Stocks::getAll(true);
		$stores = Stores::getAll(true);

		$map = [];
		$counts = [
			'ROWS' => 0,
			'ERROR_PRODUCTS' => 0,
			'ERROR_OFFERS' => 0,
			'PRICE_UPDATED' => 0,
			'OFFERS_UPDATED' => 0,
			'STOCKS_UPDATED' => 0,
			'STOCKS_Z' => 0,
			'H_SKIP' => 0,
			'H_EXISTS' => 0,
			'H_ADDED' => 0,
			'H_CHANGED' => 0,
			'H_ERROR' => 0,
		];

		$rows = explode("\r\n", $content);
		foreach ($rows as $rowIndex => $row)
		{
			$parts = explode(',', $row);

			// Первая строка (с заголовками)
			if (!$rowIndex)
			{
				if (count($parts) < 15)
				{
					Common::log('Количество столбцов отличается от ожидаемого: ' . count($parts));
					$log['ERRORS'][] = 'Количество столбцов отличается от ожидаемого: ' . count($parts);

					return;
				}

				$map = $parts;
			}
			else
			{
				if (!$map)
				{
					Common::log('Отсутствует строка заголовков');
					$log['ERRORS'][] = 'Отсутствует строка заголовков';

					return;
				}

				if (!$row)
					continue;

				$counts['ROWS']++;

				$data = [];
				foreach ($parts as $j => $v)
				{
					$key = $map[$j];
					$data[$key] = $v;
				}

				$xmlId = $data['NM_ID'];
				$bar = $data['Barcode'];
				if (!$xmlId || !$bar)
					continue;

				$product = Products::getByXmlId($xmlId);
				if (!$product)
				{
					$counts['ERROR_PRODUCTS']++;
					Common::log('Не найден товар: ' . $xmlId);
					continue;
				}

				$price = floatval(str_replace(' ', '', $data['price_ru']));
				$discount = floatval($data['discount']);
				if ($price != $product['PRICE'] || $discount != $product['DISCOUNT'])
				{
					Products::updatePriceDiscount($product['ID'], $price, $discount);
					$counts['PRICE_UPDATED']++;
				}

				$offer = Offers::getByBar($bar);
				if (!$offer)
				{
					$counts['ERROR_OFFERS']++;
					Common::log('Не найдено предложение: ' . $bar);
					continue;
				}

				unset($allStocks[$offer['ID']]);

				$text = $data['Textbox1'];
				$arText = Common::strParts($text, [
					'Данные по остаткам на: ',
					'.    Данные по цене и скидке на: ',
				]);
				$date = $arText[1];

				$stocksUpdated = false;
				foreach ($stores['ITEMS'] as $store)
				{
					$key = $store['CODE'];
					$amount = intval($data[$key]);

					// Сохранение текущего значения
					$stocks = Stocks::getItem($offer['ID'], $store['ID']);
					if ($stocks['AMOUNT'] != $amount)
					{
						if ($stocks['ID'])
							Stocks::update($stocks['ID'], $amount);
						else
							Stocks::add($offer['ID'], $store['ID'], $amount);
						$counts['STOCKS_UPDATED']++;
						$stocksUpdated = true;
					}

					// Сохранение в историю
					if ($amount > 0)
					{
						$res = StocksHistory::addUpdate($product['ID'], $offer['ID'], $store['ID'], $amount, $date);

						if ($res == -2)
							$counts['H_CHANGED']++;
						elseif ($res == -1)
							$counts['H_EXISTS']++;
						elseif ($res == 0)
							$counts['H_ERROR']++;
						else
							$counts['H_ADDED']++;
					}
					else
						$counts['H_SKIP']++;
				}
				if ($stocksUpdated)
					$counts['OFFERS_UPDATED']++;
			}
		}

		// удаление остатков из БД, которых нет в файле
		if ($counts['OFFERS_UPDATED'])
		{
			foreach ($allStocks as $offerId => $stocks)
			{
				$offer = Offers::getById($offerId);
				$product = Products::getById($offer['PRODUCT']);
				$brand = Brands::getById($product['BRAND']);
				if ($brand['ACCOUNT'] != $accountId)
					continue;

				foreach ($stocks as $storeId => $ar)
				{
					if ($ar['AMOUNT'] > 0)
					{
						Stocks::update($ar['ID'], 0);
						$counts['STOCKS_Z']++;
					}
				}
			}

			Stocks::getAll(true);
		}

		$report = "Отчет:";
		$report .= "\n\tВсего строк: " . $counts['ROWS'];
		$report .= "\n\tОшибок товаров: " . $counts['ERROR_PRODUCTS'];
		$report .= "\n\tОшибок предложений: " . $counts['ERROR_OFFERS'];
		$report .= "\n\tТоваров, у которых обновились цены или скидки: " . $counts['PRICE_UPDATED'];
		$report .= "\n\tПредложений, у которых обновились остатки: " . $counts['OFFERS_UPDATED'];
		$report .= "\n\tОбновлений остатков: " . $counts['STOCKS_UPDATED'];
		$report .= "\n\tОбнулений остатков: " . $counts['STOCKS_Z'];
		$report .= "\nИстория остатков:";
		$report .= "\n\tДобавлено: " . $counts['H_ADDED'];
		$report .= "\n\tБез изменений: " . $counts['H_EXISTS'];
		$report .= "\n\tОбновлено: " . $counts['H_CHANGED'];
		$report .= "\n\tОшибок: " . $counts['H_ERROR'];
		$report .= "\n\tПропущено: " . $counts['H_SKIP'];
		Common::log($report);

		$log['COUNTS'] = $counts;
		$log['TEXT'] = $counts['ROWS'] . ': ' . $counts['STOCKS_UPDATED'];
		if ($counts['ROWS'] < 500)
			$log['WARNINGS'] = true;
	}

	/**
	 * Обарботка отчета по ценам и скидкам
	 * @param $accountId
	 * @param $content
	 * @param $log
	 */
	public static function priceHistory($accountId, $content, &$log)
	{
		PriceHistory::getAllItems(true);
		$data = json_decode($content, true);
		foreach ($data as $item)
		{
			$date = $item['uploadDate'];
			$xmlId = $item['id'];
			$name = $item['uploadType'];

			if (!$xmlId)
			{
				Common::log('Не найден xmlId');
				continue;
			}

			$hist = PriceHistory::getByXmlId($xmlId);
			if (!$hist)
				$hist = PriceHistory::addItem($name, $xmlId, $date);
			if (!$hist)
			{
				Common::log('Ошибка создания элемента-истории: ' . $xmlId);
				continue;
			}

			if (!$hist['ACTIVE'])
			{
				$content = Loader::priceHistoryItem($accountId, $hist, $log);
				if ($content !== false)
				{
					self::priceHistoryItem($hist, $content, $log);

					PriceHistory::activeItem($hist['ID']);
				}
			}
		}

		if (!$log['COUNTS'] && !$log['ERRORS'])
		{
			$log['TEXT'] = 'Новых данных не обнаружено.';
		}
	}

	/**
	 * Обарботка отчета по ценам и скидкам - заданный файл
	 * @param $hist
	 * @param $content
	 * @param $log
	 */
	public static function priceHistoryItem($hist, $content, &$log)
	{
		$data = json_decode($content, true);

		$counts = [
			'ROWS' => count($data),
			'ERROR_PRODUCTS' => 0,
			'PRICE_UPDATED' => 0,
		];

		foreach ($data as $item)
		{
			$xmlId = $item['nmId'];
			$price = $item['price'];
			$discount = $item['discount'];
			$priceCh = isset($item['price']);
			$discountCh = isset($item['discount']);

			$product = Products::getByXmlId($xmlId);
			if (!$product)
			{
				$counts['ERROR_PRODUCTS']++;
				Common::log('Не найден товар: ' . $xmlId);
				continue;
			}

			if ($priceCh || $discountCh)
			{
				$counts['PRICE_UPDATED']++;
				PriceHistory::add($product['ID'], $hist['ID'], $hist['DATE'], $price, $discount, $priceCh, $discountCh);
			}
		}

		$report = "Отчет:";
		$report .= "\nВсего строк: " . $counts['ROWS'];
		$report .= "\nОшибок товаров: " . $counts['ERROR_PRODUCTS'];
		$report .= "\nТоваров, у которых обновились цены или скидки: " . $counts['PRICE_UPDATED'];
		Common::log($report);

		$log['COUNTS'] = $counts;
		$log['TEXT'] = $counts['ROWS'] . ': ' . $counts['PRICE_UPDATED'];
		if ($counts['ROWS'] < 1)
			$log['WARNINGS'] = true;
	}

	/**
	 * Обработка отчета продаж по реализации
	 * @param $accountId
	 * @param $content
	 * @param $log
	 */
	public static function realization($accountId, $content, &$log)
	{
		Realization::getAll(true);

		$ar = Common::strParts($content, [
			'«Еженедельный отчет о продажах по реализации»', '<tbody>', '</tbody>',
			'«Ежемесячный отчет о продажах по реализации»', '<tbody>', '</tbody>',
		]);
		if (count($ar) < 7)
		{
			Common::log('Не найдена таблица с данными');
			$log['ERRORS'][] = 'Не найдена таблица с данными';

			return;
		}

		$data = [
			'week' => $ar[2],
			'month' => $ar[5],
		];

		foreach ($data as $sectionKey => $sectionData)
		{
			$isWeek = $sectionKey == 'week';

			$rows = explode('<tr', $sectionData);
			foreach ($rows as $i => $row)
			{
				if (!$i)
					continue;

				$xmlId = 0;
				$date = '';
				$from = '';
				$to = '';
				$sales = 0;
				$cost = 0;
				$fee = 0;
				$discount = 0;
				$report = false;

				$parts = explode('<td', $row);
				foreach ($parts as $j => $part)
				{
					$parts1 = Common::strParts($part, [
						'>',
						'</td>'
					]);
					$value = trim($parts1[1]);
					if ($j == 1)
						$xmlId = $value;
					elseif ($j == 2)
						$from = $value;
					elseif ($j == 3)
						$to = $value;
					elseif ($j == 4)
						$date = $value;
					elseif ($j == 5)
						$sales = str_replace(',', '.', str_replace('&#160;', '', $value));
					elseif ($j == 6)
						$cost = str_replace(',', '.', str_replace('&#160;', '', $value));
					elseif ($j == 7)
						$fee = str_replace(',', '.', str_replace('&#160;', '', $value));
					elseif ($j == 8)
						$discount = str_replace(',', '.', $value);
					elseif ($j >= 13)
						if (strpos($value, '/realization/details/') !== false)
							$report = true;
				}

				if (!$report && !$isWeek)
					continue;

				if (!$xmlId)
				{
					Common::log('Не найден xmlId');
					continue;
				}

				$hist = Realization::getByXmlId($xmlId);
				if (!$hist)
				{
					$name = $from . ' - ' . $to;
					if (!$isWeek)
					{
						if (strlen($from) == 10 && substr($from, 5, 1) == '.')
						{
							$mNum = substr($from, 3, 2);
							$year = substr($from, 6, 4);
							$name = Utils::$MONTH_NAMES[$mNum - 1] . ' ' . $year;
						}
					}

					$hist = Realization::addItem($name, $date, $from, $to, $xmlId, $sales, $cost, $fee, $discount, $isWeek);
					$log['TEXT'] = 'Новый отчет: ' . $name;
				}
				else
				{
					$hist = Realization::updateItem($hist, $sales, $cost, $fee, $discount);
				}

				if (!$hist)
				{
					Common::log('Ошибка создания элемента-истории: ' . $xmlId);
					continue;
				}

				if (!$hist['ACTIVE'])
				{
					$content = Loader::realizationItem($accountId, $hist, $log);
					if ($content !== false)
					{
						Realization::activeItem($hist['ID']);
						$log['TEXT'] = '"' . $hist['XML_ID'] . '": новый отчет за ' . ($isWeek ? 'неделю' : 'месяц');
					}
				}
			}
		}

		if (!$log['COUNTS'] && !$log['ERRORS'] && !$log['TEXT'])
		{
			$log['TEXT'] = 'Новых данных не обнаружено.';
		}
	}

	/**
	 * Обарботка отчета по продажам
	 * @param $content
	 * @param $dateF
	 * @param $store
	 * @param $log
	 */
	public static function sales($content, $dateF, $store, &$log)
	{
		if (ord($content[0]) === 239)
			$content = substr($content, 1);

		$search = "с $dateF по $dateF";
		if (strpos($content, $search) === false)
		{
			Common::log('Отчет за другой день');
			$log['ERRORS']['X'] = 'Отчет за другой день';

			return;
		}

		$offers = Offers::getAll(true);
		Contracts::getAll(true);

		$sep = '#@^';
		$map = [];
		$counts = [
			'OFFERS' => count($offers['ITEMS']),
			'ROWS' => 0,
			'ERROR_OFFERS' => 0,
			'ADDED' => 0,
			'CHANGED' => 0,
			'EXISTS' => 0,
			'ERROR' => 0,
			'SKIP' => 0,
		];

		$rows = explode("Отчёт по данным поставщика", $content);
		foreach ($rows as $rowIndex => $row)
		{
			if ($rowIndex)
				$row = '"' . $row;

			$tmp = explode('"', $row);
			$mod = [];
			foreach ($tmp as $j => $str)
			{
				if ($j % 2)
					$mod[] = str_replace(',', $sep, $str);
				else
					$mod[] = $str;
			}

			$parts = explode(',', implode('"', $mod));

			// Первая строка (с заголовками)
			if (!$rowIndex)
			{
				if (count($parts) < 25)
				{
					Common::log('Количество столбцов отличается от ожидаемого: ' . count($parts));
					$log['ERRORS']['C'] = 'Количество столбцов отличается от ожидаемого: ' . count($parts);

					return;
				}

				$map = $parts;
			}
			else
			{
				if (!$row)
					continue;

				if (!$map)
				{
					Common::log('Отсутствует строка заголовков');
					$log['ERRORS']['M'] = 'Отсутствует строка заголовков';

					return;
				}

				$counts['ROWS']++;

				$data = [];
				foreach ($parts as $j => $v)
				{
					$key = $map[$j];
					$data[$key] = trim(str_replace($sep, '.', $v), '"');
				}

				$bar = $data['barcode'];
				if (!$bar)
					continue;

				$offer = Offers::getByBar($bar);
				if (!$offer)
				{
					$counts['ERROR_OFFERS']++;
					Common::log('Не найдено предложение: ' . $bar);
					continue;
				}

				$contractName = $data['Контракт'];
				$contract = Contracts::getByName($contractName);
				if (!$contract)
					$contract = Contracts::add($contractName);
				if (!$contract)
				{
					Common::log('Ошибка контракта');
					continue;
				}

				$fields = [
					'UF_DATE' => $dateF,
					'UF_OFFER' => $offer['ID'],
					'UF_PRODUCT' => $offer['PRODUCT'],
					'UF_STORE' => $store['ID'],
					'UF_CONTRACT' => $contract['ID'],
					'UF_ADMISSION' => intval($data['Поступления__шт1']),
					'UF_ADMISSION_PRICE' => floatval(str_replace(' ', '', $data['Поступления__руб'])),
					'UF_ORDER' => intval($data['Заказано__шт1']),
					'UF_ORDER_PRICE' => floatval(str_replace(' ', '', $data['Себестоимость__руб1'])),
					'UF_RETURN' => intval($data['Возвраты_до_оплаты__шт']),
					'UF_RETURN_PRICE' => floatval(str_replace(' ', '', $data['Возвраты_до_оплаты_с_с__руб'])),
					'UF_SALES' => intval($data['Продажи_по_оплатам__шт1']),
					'UF_SALES_PRICE' => floatval(str_replace(' ', '', $data['Продажи_по_оплатам_с_с__руб'])),
					'UF_REMISSION' => intval($data['Возвраты_ШТ1']),
					'UF_REMISSION_PRICE' => floatval(str_replace(' ', '', $data['Возвраты_с_с__руб'])),
				];

				if ($fields['UF_ADMISSION'] > 0 || $fields['UF_ORDER'] > 0 || $fields['UF_RETURN'] > 0 ||
					$fields['UF_SALES'] > 0 || $fields['UF_REMISSION'] > 0)
				{
					$res = Sales::addUpdate($fields);
					if ($res == -2)
						$counts['CHANGED']++;
					elseif ($res == -1)
						$counts['EXISTS']++;
					elseif ($res == 0)
						$counts['ERROR']++;
					else
						$counts['ADDED']++;
				}
				else
					$counts['SKIP']++;
			}
		}

		$log['COUNTS']['OFFERS'] = $counts['OFFERS'];
		$log['COUNTS']['DAYS']++;
		$log['COUNTS']['ROWS'] += $counts['ROWS'];
		$log['COUNTS']['ERROR_OFFERS'] += $counts['ERROR_OFFERS'];
		$log['COUNTS']['SKIP'] += $counts['SKIP'];
		$log['COUNTS']['ADDED'] += $counts['ADDED'];
		$log['COUNTS']['EXISTS'] += $counts['EXISTS'];
		$log['COUNTS']['CHANGED'] += $counts['CHANGED'];
		$log['COUNTS']['ERROR'] += $counts['ERROR'];

		$report = "Отчет:";
		$report .= "\nВсего строк: " . $counts['ROWS'];
		$report .= "\nПредложений: " . $counts['OFFERS'];
		$report .= "\nОшибок предложений: " . $counts['ERROR_OFFERS'];
		$report .= "\nПропущено строк: " . $counts['SKIP'];
		$report .= "\nДобавлено элементов: " . $counts['ADDED'];
		$report .= "\nБез изменений: " . $counts['EXISTS'];
		$report .= "\nИзменений в элементах: " . $counts['CHANGED'];
		$report .= "\nОшибок добавления: " . $counts['ERROR'];
		Common::log($report);
	}

	/**
	 * Отчет по товарам в пути
	 * @param $content
	 * @param $log
	 */
	public static function shipping($content, &$log)
	{
		Products::getAll(true);
		Offers::getAll(true);
		Shipping::getAll(true);

		$map = [];
		$counts = [
			'ROWS' => 0,
			'ERROR_BAR' => 0,
			'ERROR_PRODUCTS' => 0,
			'ERROR_OFFERS' => 0,
			'UPDATED' => 0,
		];

		$byOffer = [];

		$rows = explode("\r\n", $content);
		foreach ($rows as $rowIndex => $row)
		{
			$ar = Common::strParts($row, ['"', '"', '"', '"']);
			if (count($ar) == 5)
				$row = $ar[0] . str_replace(',', '.', $ar[1]) . $ar[2] . str_replace(',', '.', $ar[3]) . $ar[4];
			elseif (count($ar) == 3)
				$row = $ar[0] . str_replace(',', '.', $ar[1]) . $ar[2];
			$parts = explode(',', $row);

			// Первая строка (с заголовками)
			// brand_name,direction,Textbox36,Textbox38,nm_id1,sa1,color1,Textbox45,Textbox46,ts_name,barcode,q,Textbox39
			if (!$rowIndex)
			{
				if (count($parts) < 11)
				{
					Common::log('Количество столбцов отличается от ожидаемого: ' . count($parts));
					$log['ERRORS'][] = 'Количество столбцов отличается от ожидаемого: ' . count($parts);

					return;
				}

				$map = $parts;
			}
			else
			{
				if (!$row)
					continue;

				if (!$map)
				{
					Common::log('Отсутствует строка заголовков');
					$log['ERRORS'][] = 'Отсутствует строка заголовков';

					return;
				}

				$counts['ROWS']++;

				$data = [];
				foreach ($parts as $j => $v)
				{
					$key = $map[$j];
					$data[$key] = $v;
				}

				$bar = $data['barcode'];
				if (!$bar)
				{
					$counts['ERROR_BAR']++;
					Common::log('Не задан ШК');
					continue;
				}

				$direction = $data['direction'];
				$xmlId = $data['nm_id1'];
				$q = intval($data['q']);

				$product = Products::getByXmlId($xmlId);
				if (!$product)
				{
					$counts['ERROR_PRODUCTS']++;
					Common::log('Не найден товар: ' . $xmlId);
					continue;
				}

				$offer = Offers::getByBar($bar);
				if (!$offer)
				{
					$counts['ERROR_OFFERS']++;
					Common::log('Не найдено предложение: ' . $bar);
					continue;
				}

				if ($direction === 'В пути к клиенту. шт.')
					$byOffer[$offer['ID']]['TO'] = $q;
				elseif ($direction === 'В пути от клиента. шт.')
					$byOffer[$offer['ID']]['FROM'] = $q;
			}
		}

		foreach ($byOffer as $offerId => $values)
		{
			$shipping = Shipping::getItem($offerId);

			if ($shipping['TO_CLIENT'] != $values['TO'] || $shipping['FROM_CLIENT'] != $values['FROM'])
			{
				if ($shipping['ID'])
					Shipping::update($shipping['ID'], $values['TO'], $values['FROM']);
				else
					Shipping::add($offerId, $values['TO'], $values['FROM']);

				$counts['UPDATED']++;
			}
		}

		$report = "Отчет:";
		$report .= "\nОшибок товаров: " . $counts['ERROR_PRODUCTS'];
		$report .= "\nОшибок предложений: " . $counts['ERROR_OFFERS'];
		$report .= "\nВсего строк: " . $counts['ROWS'];
		$report .= "\nОбновлено предложений: " . $counts['UPDATED'];
		Common::log($report);

		$log['COUNTS'] = $counts;
		$log['TEXT'] = $counts['ROWS'] . ': ' . $counts['UPDATED'];
		if ($counts['ROWS'] < 500)
			$log['WARNINGS'] = true;
	}

    /**
     * Отчет по дефициту
     * @param $accountId
     * @param $fileName
	 * @param $log
	 */
    public static function deficit($accountId, $fileName, &$log)
    {
        $ar = [];
        try {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($fileName);

            $sheet = $spreadsheet->getSheet(0);
            $ar = $sheet->toArray();
        }
        catch (\Exception $e)
        {
            Common::log($e->getMessage());
			$log['ERRORS'][] = 'Ошибка распознавания xlsx файла';
        }

        if (!$ar)
            return;

		Products::getAll(true);
		$offers = Offers::getAll(true);
        Stocks::getAllDeficit(true);
        $stores = Stores::getAll(true);
        $dateF = date('d.m.Y');

        $counts = [
            'ROWS' => 0,
            'ERROR_PRODUCTS' => 0,
            'ERROR_OFFERS' => 0,
            'ADDED' => 0,
            'UPD' => 0,
            'Z' => 0,
        ];
        $deficitClearCache = false;

        $jIMT = false;
        $jColor = false;
        $jSize = false;
        $jValue = false;
        $jStore = [];
        foreach ($ar as $rowIndex => $row)
        {
            if (!$rowIndex)
            {
                foreach ($row as $j => $td)
                {
                    if ($td == 'Артикул ИМТ')
						$jIMT = $j;
                    elseif ($td == 'Артикул Цвета')
						$jColor = $j;
                    elseif ($td == 'Размер')
						$jSize = $j;
                    elseif ($td == 'Общий дефицит')
                        $jValue = $j;
                    foreach ($stores['ITEMS'] as $store)
                    {
                    	if ('в т.ч. ' . $store['NAME'] == $td)
		                    $jStore[$store['ID']] = $j;
                    }
                }

                if ($jIMT === false || $jColor === false || $jSize === false)
                {
	                Common::log('Не найдены ключевые заголовки');
					$log['ERRORS'][] = 'Не найдены ключевые заголовки';

	                return;
                }
            }
            else
            {
                $IMT = $row[$jIMT];
                $color = $row[$jColor];
                $size = $row[$jSize];
                if ($color)
                {
                    $counts['ROWS']++;

                    $code = $color;
                    if ($IMT)
						$code = $IMT . $code;
                    $product = Products::getByCode($code);
                    if (!$product)
					{
						$counts['ERROR_PRODUCTS']++;
						Common::log('Не найден товар: ' . $code);

						continue;
					}

                    $offer = Offers::getByProductSize($product['ID'], $size);
                    if (!$offer)
                    {
                        $counts['ERROR_OFFERS']++;
                        Common::log('Не найдено предложение: ' . $size);

                        continue;
                    }

					$value = intval($row[$jValue]);
					$deficitUpdated = false;

					// Сохраняем в историю
					$ex = Deficit::getByOfferDate($offer['ID'], $dateF);
					if (!$ex) {
						$counts['ADDED']++;
						Deficit::add($offer['ID'], $dateF, $value);
					}

					// Значение общего дефицита
					$offers['ITEMS'][$offer['ID']]['EX_DEFICIT'][Stores::COMMON_ID] = true;
					$storeId = Stores::COMMON_ID;
					$deficit = Stocks::getDeficitItem($offer['ID'], $storeId);
					if ($deficit['AMOUNT'] != $value)
					{
						if ($deficit['ID'])
							Stocks::update($deficit['ID'], $value);
						else
							Stocks::add($offer['ID'], $storeId, $value, 2);

						$deficitUpdated = true;
						$counts['UPD']++;
					}

					// Значение дефицита в Подольске
					$offers['ITEMS'][$offer['ID']]['EX_DEFICIT'][Stores::PODOLSK_ID] = true;
					foreach ($row as $j => $td)
					{
						if ($j > $jValue)
							$value -= intval($td);
					}
					$storeId = Stores::PODOLSK_ID;
					$deficit = Stocks::getDeficitItem($offer['ID'], $storeId);
					if ($deficit['AMOUNT'] != $value)
					{
						if ($deficit['ID'])
							Stocks::update($deficit['ID'], $value);
						else
							Stocks::add($offer['ID'], $storeId, $value, 2);

						$deficitUpdated = true;
						$counts['UPD']++;
					}

					// Значения по остальным складам
					foreach ($jStore as $storeId => $j)
					{
						$offers['ITEMS'][$offer['ID']]['EX_DEFICIT'][$storeId] = true;
						$amount = intval($row[$j]);
						$deficit = Stocks::getDeficitItem($offer['ID'], $storeId);
						if ($deficit['AMOUNT'] != $amount)
						{
							if ($deficit['ID'])
								Stocks::update($deficit['ID'], $amount);
							else
								Stocks::add($offer['ID'], $storeId, $amount, 2);

							$deficitUpdated = true;
							$counts['UPD']++;
						}
					}
					if ($deficitUpdated)
						$deficitClearCache = true;
                }
            }
        }

        if ($counts['ROWS'])
        	Deficit::saveLast($fileName);

        if ($deficitClearCache)
        {
            foreach ($offers['ITEMS'] as $offer)
            {
            	$product = Products::getById($offer['PRODUCT']);
            	$brand = Brands::getById($product['BRAND']);
            	if ($accountId != $brand['ACCOUNT'])
            		continue;

            	$storeId = Stores::COMMON_ID;
            	// Для общего дефицита
	            if (!$offer['EX_DEFICIT'][$storeId])
	            {
		            $deficit = Stocks::getDeficitItem($offer['ID'], $storeId);
		            if ($deficit['ID'] && $deficit['AMOUNT'] > 0)
		            {
			            Stocks::update($deficit['ID'], 0);
			            $counts['Z']++;
		            }
	            }

            	// Для остальных складов
                foreach ($stores['ITEMS'] as $store)
                {
                    if (!$offer['EX_DEFICIT'][$store['ID']])
                    {
                        $deficit = Stocks::getDeficitItem($offer['ID'], $store['ID']);
                        if ($deficit['ID'] && $deficit['AMOUNT'] > 0)
                        {
                            Stocks::update($deficit['ID'], 0);
                            $counts['Z']++;
                        }
                    }
                }
            }
            Stocks::getAllDeficit(true);
        }

        $report = "Отчет:";
        $report .= "\nВсего строк: " . $counts['ROWS'];
        $report .= "\nНе найдено товаров: " . $counts['ERROR_PRODUCTS'];
        $report .= "\nНе найдено предложений: " . $counts['ERROR_OFFERS'];
        $report .= "\nДобавлено: " . $counts['ADDED'];
        $report .= "\nОбновлено по складам: " . $counts['UPD'];
        $report .= "\nОбнуление по складам: " . $counts['Z'];
        Common::log($report);

		$log['COUNTS'] = $counts;
		$log['TEXT'] = $counts['ROWS'] . ': &' . $counts['UPD'] . ', -' . $counts['Z'];
		if ($counts['ROWS'] < 500)
			$log['WARNINGS'] = true;
    }

	/**
	 * Цены и скидки с сайта wildberries.ru
	 * @param $fileName
	 * @param $product
	 * @param $log
	 */
	public static function prices($fileName, $product, &$log)
	{
		if (!file_exists($fileName)) {
			Common::log($product['XML_ID'] . ' - Ошибка загрузки');
			$log['ERRORS']['L'] = 'Ошибка загрузки';
			$log['COUNTS']['LOAD']++;

			return;
		}

		$content = file_get_contents($fileName);

		$ar = json_decode($content, true);
		if (empty($ar['Value']))
		{
			$log['COUNTS']['NULL']++;

			return;
		}

		if (empty($ar['Value']['promoInfo']['NmId']))
		{
			Common::log($product['XML_ID'] . ' - Не найден NmId');
			$log['ERRORS']['NF'] = 'Не найден NmId';
			$log['COUNTS']['PARSE']++;

			return;
		}

		if ($ar['Value']['promoInfo']['NmId'] != $product['XML_ID'])
		{
			Common::log($product['XML_ID'] . ' - Другой товар');
			$log['ERRORS']['A'] = 'Другой товар';
			$log['COUNTS']['WRONG']++;

			return;
		}

		$data = array(
			'UF_PRODUCT' => $product['ID'],
			'UF_PRICE' => $ar['Value']['promoInfo']['Price'],
			'UF_PRICE_WCAD' => $ar['Value']['promoInfo']['PriceWithCouponAndDiscount'],
			'UF_DATA' => $content,
			'UF_DATE' => new DateTime(),
		);
		Prices::add($data);

		$log['COUNTS']['OK']++;
	}
}