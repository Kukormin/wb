<?
namespace Local\Import;

/**
 * Точка входа для импортов
 * Class Service
 * @package Local\Import
 */
class Service
{
	const SELF_HOST = 'http://victoria-kids.tk';

	private static function checkLock($code, $expired = 7200)
	{
		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/' . $code . '.lock';
		if (!file_exists($fileName))
		{
			$fp = fopen($fileName, 'w+');
			fclose($fp);

			return true;
		}
		elseif (filemtime($fileName) + $expired < time())
		{
			return true;
		}

		return false;
	}

	private static function clearLock($code)
	{
		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/' . $code . '.lock';
		if (file_exists($fileName))
		{
			unlink($fileName);
		}
	}

	/**
	 * Номенклатура
	 * @param bool $agent
	 */
	public static function nomenclature($agent = false)
	{
		Common::setLogFilename('/_import/nomenclature.txt');
		if (!self::checkLock('nomenclature'))
		{
			Common::log('Импорт номенклатуры уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('nomenclature');
		$data = Loader::nomenclature();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('nomenclature');
	}

	/**
	 * Остатки по складам и текущие цены
	 * @param bool $agent
	 */
	public static function storeStocksAndPrices($agent = false)
	{
		Common::setLogFilename('/_import/storeStocksAndPrices.txt');
		if (!self::checkLock('storeStocksAndPrices'))
		{
			Common::log('Импорт данных по остаткам уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('storeStocksAndPrices');
		$data = Loader::storeStocksAndPrices();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('storeStocksAndPrices');
	}

	/**
	 * История изменения цен
	 * @param bool $agent
	 */
	public static function priceHistory($agent = false)
	{
		Common::setLogFilename('/_import/priceHistory.txt');
		if (!self::checkLock('priceHistory'))
		{
			Common::log('Импорт истории загрузок цен уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('priceHistory');
		$data = Loader::priceHistory();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('priceHistory');
	}

	/**
	 * Продажи
	 * @param bool $agent
	 */
	public static function sales($agent = false)
	{
		Common::setLogFilename('/_import/sales.txt');
		if (!self::checkLock('sales'))
		{
			Common::log('Импорт продаж уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('sales');
		$data = Loader::sales();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('sales');
	}

	/**
	 * Продажи по реализации - запуск по HTTP
	 */
	public static function realizationAgent()
	{
		$http = new CurlHTTP();

		$url = self::SELF_HOST . '/xls/import_realization.php?agent=Y';
		$http->get($url);
	}

	/**
	 * Продажи по реализации
	 * @param bool $agent
	 */
	public static function realization($agent = false)
	{
		Common::setLogFilename('/_import/realization.txt');
		if (!self::checkLock('realization'))
		{
			Common::log('Импорт продаж по реализации уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('realization');
		$data = Loader::realization();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('realization');
	}

	/**
	 * Обновление одного отчета по реализации
	 * @param $item
	 */
	public static function realizationItem($item)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('realization');
		Common::setLogFilename('/_import/realization.txt');
		$data = [
			'ERRORS' => [],
		];
		$log = [];
		Loader::realizationItem($item, $data, $log);
		$success = count($data['ERRORS']) <= 0;
		if ($success)
			$data['TEXT'] = '"' . $item['XML_ID'] . '": данные успешно обновлены';

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, $success, false, $data);
	}

	/**
	 * Товары в пути
	 * @param bool $agent
	 */
	public static function shipping($agent = false)
	{
		Common::setLogFilename('/_import/shipping.txt');
		if (!self::checkLock('shipping'))
		{
			Common::log('Импорт товаров пути уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('shipping');
		$data = Loader::shipping();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('shipping');
	}

	/**
	 * Дефицит - запуск по HTTP
	 */
	public static function deficitAgent()
	{
		$http = new CurlHTTP();

		$url = self::SELF_HOST . '/xls/import_deficit.php?agent=Y';
		$http->get($url);
	}

	/**
	 * Дефицит
	 * @param bool $agent
	 */
    public static function deficit($agent = false)
    {
		Common::setLogFilename('/_import/deficit.txt');
		if (!self::checkLock('deficit'))
		{
			Common::log('Импорт дефицита уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('deficit');
		$data = Loader::deficit();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('deficit');
    }

	/**
	 * Локальные остатки - запуск по HTTP
	 */
	public static function localAgent()
	{
		$http = new CurlHTTP();

		$url = self::SELF_HOST . '/xls/import_local.php?agent=Y';
		$http->get($url);
	}

	/**
	 * Локальные остатки
	 * @param bool $agent
	 */
	public static function local($agent = false)
	{
		Common::setLogFilename('/_import/uln.txt');
		if (!self::checkLock('local'))
		{
			Common::log('Импорт локальных данных уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('local');
		$data = Local::load();

		self::clearLock('local');

		if (isset($data['SKIP']) && $data['SKIP'] === true)
			return;

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}

	/**
	 * Цены и скидки с сайта wildberries.ru
	 * @param bool $agent
	 */
	public static function prices($agent = false)
	{
		Common::setLogFilename('/_import/prices.txt');
		if (!self::checkLock('prices'))
		{
			Common::log('Импорт цен с сайта уже запущен.');
			return;
		}

		$begin = microtime(true);

		$import = Imports::getByXmlId('prices');
		$data = Loader::prices();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);

		self::clearLock('prices');
	}
}