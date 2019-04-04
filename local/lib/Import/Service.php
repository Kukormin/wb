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

	/**
	 * Номенклатура
	 * @param bool $agent
	 */
	public static function nomenclature($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('nomenclature');
		Common::setLogFilename('/_import/nomenclature.txt');
		$data = Loader::nomenclature();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}

	/**
	 * Остатки по складам и текущие цены
	 * @param bool $agent
	 */
	public static function storeStocksAndPrices($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('storeStocksAndPrices');
		Common::setLogFilename('/_import/storeStocksAndPrices.txt');
		$data = Loader::storeStocksAndPrices();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}

	/**
	 * История изменения цен
	 * @param bool $agent
	 */
	public static function priceHistory($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('priceHistory');
		Common::setLogFilename('/_import/priceHistory.txt');
		$data = Loader::priceHistory();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}

	/**
	 * Продажи
	 * @param bool $agent
	 */
	public static function sales($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('sales');
		Common::setLogFilename('/_import/sales.txt');
		$data = Loader::sales();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
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
		$begin = microtime(true);

		$import = Imports::getByXmlId('realization');
		Common::setLogFilename('/_import/realization.txt');
		$data = Loader::realization();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
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
		Loader::realizationItem($item, $data);
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
		$begin = microtime(true);

		$import = Imports::getByXmlId('shipping');
		Common::setLogFilename('/_import/shipping.txt');
		$data = Loader::shipping();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
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
		$begin = microtime(true);

		$import = Imports::getByXmlId('deficit');
		Common::setLogFilename('/_import/deficit.txt');
		$data = Loader::deficit();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
    }

	/**
	 * Остатки в Улн - запуск по HTTP
	 */
	public static function ulnAgent()
	{
		// В сб и вс файл с данными не выгружается
		$w = date('w');
		if ($w == 0 || $w == 6)
			return;

		$http = new CurlHTTP();

		$url = self::SELF_HOST . '/xls/import_uln.php?agent=Y';
		$http->get($url);
	}

	/**
	 * Остатки в Улн
	 * @param bool $agent
	 */
	public static function uln($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('uln');
		Common::setLogFilename('/_import/uln.txt');
		$data = Loader::uln();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}

	/**
	 * Цены и скидки с сайта wildberries.ru
	 * @param bool $agent
	 */
	public static function prices($agent = false)
	{
		$begin = microtime(true);

		$import = Imports::getByXmlId('prices');
		Common::setLogFilename('/_import/prices.txt');
		$data = Loader::prices();

		$end = microtime(true);
		Log::add($import['ID'], $begin, $end, count($data['ERRORS']) <= 0, $agent, $data);
	}
}