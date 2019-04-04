<?

namespace Local\Import;

use Local\Main\Products;
use Local\Main\Stores;

/**
 * Загрузка отчетов из WB
 * Class Loader
 * @package Local\Import
 */
class Loader
{
	const USER = 'UserName=supp11250&Password=XO780k1s';
	const PUB_USER = 'Item.Login=Alexei-adamov%40rambler.ru&Item.Password=Jeam_Beam';
	const HOST = 'https://suppliers.wildberries.ru';
	const COOKIES = '/_import/cookies.txt';

	/**
	 * @var CurlHTTP
	 */
	private static $http;

	/**
	 * Инициализация CurlHTTP
	 */
	public static function initHttp()
	{
		self::$http = new CurlHTTP();

		$cookiesFile = $_SERVER['DOCUMENT_ROOT'] . self::COOKIES;
		self::$http->cookies($cookiesFile);
	}

	/**
	 * Авторизация
	 * @return bool
	 */
	public static function login()
	{
		$url = self::HOST . '/Account/Login';
		$post = self::USER;
		$res = self::$http->post($url, $post);

		if ($res['http_code'] != 302)
		{
			Common::log('Ошибка авторизации');

			return false;
		}

		return true;
	}

	/**
	 * Авторизация в публичной части сайта
	 * @return bool
	 */
	public static function pubLogin()
	{
		$url = 'https://www.wildberries.ru/basket/info';
		$res = self::$http->post($url, '', '', ['X-Requested-With: XMLHttpRequest']);
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/prices/info.json';
		file_put_contents($reportFileName, $res['CONTENT']);
		if ($res['http_code'] == 200)
		{
			$data = json_decode($res['CONTENT'], true);
			if ($data['IsAuthenticated'])
				return true;
		}

		$url = 'https://security.wildberries.ru/loginpopup?returnUrl=https://www.wildberries.ru/&xdm_e=https://www.wildberries.ru&xdm_c=default4197&xdm_p=1';
		$res = self::$http->get($url);
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/prices/login.html';
		file_put_contents($reportFileName, $res['CONTENT']);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка авторизации 1');

			return false;
		}

		$ar = Common::strParts($res['CONTENT'], [
			'name="__RequestVerificationToken"',
			'value="',
			'"',
			'name="BDC_VCID_signIn"',
			'value="',
			'"',
			'name="BDC_BackWorkaround_signIn"',
			'value="',
			'"',
			'name="BDC_Hs_signIn"',
			'value="',
			'"',
			'name="BDC_SP_signIn"',
			'value="',
			'"',
		]);

		if (count($ar) < 15) {
			Common::log('Ошибка авторизации 2');

			return false;
		}

		$url = 'https://security.wildberries.ru/loginajax?returnUrl=https://www.wildberries.ru/';
		$post =
			'__RequestVerificationToken=' . $ar[2] . '&' . self::PUB_USER . '&Item.FullPhoneMobile=&BDC_VCID_signIn=' .
			$ar[5] . '&BDC_BackWorkaround_signIn=' . $ar[8] . '&BDC_Hs_signIn=' . $ar[11] . '&BDC_SP_signIn=' .
			$ar[14] . '&CaptchaCode=&Item.IsPersistentCookie=true';
		$res = self::$http->post($url, $post, '', [
			'TE: Trailers',
			'X-Requested-With: XMLHttpRequest',
			'Referer: https://security.wildberries.ru/loginpopup?returnUrl=https%3A%2F%2Fwww.wildberries.ru%2F&xdm_e=https%3A%2F%2Fwww.wildberries.ru&xdm_c=default7697&xdm_p=1',
		]);
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/prices/login_res.html';
		file_put_contents($reportFileName, $res['CONTENT']);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка авторизации 3');

			return false;
		}

		$data = json_decode($res['CONTENT'], true);
		if (!$data['ResultState'])
			return false;

		return true;
	}

	/**
	 * Отчет c перечнем всех номенклатур, артикулов и баркодов
	 * @return array
	 */
	public static function nomenclature()
	{
		$log = [
			'ERRORS' => [],
		];
		Common::log(date('d.m.Y') . ' - Импорт номенклатуры');

		self::initHttp();

		$url = self::HOST . '/aspx/report.aspx?rpid=16';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		$ar = Common::strParts($res['CONTENT'], [
			'"ExportUrlBase":"',
			'"',
		]);
		if (count($ar) < 2)
		{
			Common::log('Не найден "ExportUrlBase"');
			$log['ERRORS'][] = 'Не найден "ExportUrlBase"';

			return $log;
		}

		$format = 'CSV';
		$ExportUrlBase = str_replace('\u0026', '&', $ar[1]);
		$url = self::HOST . $ExportUrlBase . $format;

		Common::log("URL запроса:\n" . $url);

		$res = self::$http->get($url);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки данных');
			$log['ERRORS'][] = 'Ошибка загрузки данных';

			return $log;
		}

		$fn = date('Y_m_d_His') . '.csv';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/nomenclature/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		Parser::nomenclature($res['CONTENT'], $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Остаток товара на складах с указанием текущих розничных цен
	 * @return array
	 */
	public static function storeStocksAndPrices()
	{
		$log = [
			'ERRORS' => [],
		];
		Common::log(date('d.m.Y') . ' - Импорт данных по остаткам');

		self::initHttp();

		$url = self::HOST . '/aspx/report.aspx?rpid=15';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		$ar = Common::strParts($res['CONTENT'], [
			'"ExportUrlBase":"',
			'"',
		]);
		if (count($ar) < 2)
		{
			Common::log('Не найден "ExportUrlBase"');
			$log['ERRORS'][] = 'Не найден код файла для скачивания';

			return $log;
		}

		$format = 'CSV';
		$ExportUrlBase = str_replace('\u0026', '&', $ar[1]);
		$url = self::HOST . $ExportUrlBase . $format;

		Common::log("URL запроса:\n" . $url);

		$res = self::$http->get($url);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки данных');
			$log['ERRORS'][] = 'Ошибка загрузки данных';

			return $log;
		}

		$fn = date('Y_m_d_His') . '.csv';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/sap/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		Parser::storeStocksAndPrices($res['CONTENT'], $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Цены и скидки: история загрузок
	 * @return array
	 */
	public static function priceHistory()
	{
		$log = [
			'ERRORS' => [],
		];
		Common::log(date('d.m.Y') . ' - Импорт истории загрузок цен');

		self::initHttp();

		$url = self::HOST . '/discount/history';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		Parser::priceHistory($res['CONTENT'], $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Цены и скидки: история загрузок - заданный файл
	 * @param $hist
	 * @param $log
	 * @return bool
	 */
	public static function priceHistoryItem($hist, &$log)
	{
		Common::log('Новый файл в истории: ' . $hist['XML_ID']);

		self::initHttp();

		$url = self::HOST . '/DiscountAgreement/DiscountDetails?UploadId=' . $hist['XML_ID'];
		$res = self::$http->get($url);

		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки файла.');
			$log['ERRORS'][] = 'Ошибка загрузки файла.';

			return false;
		}

		$fn = $hist['XML_ID'] . '.html';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/price/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		return $res['CONTENT'];
	}

	/**
	 * Продажи по реализации
	 * @return array
	 */
	public static function realization()
	{
		$log = [
			'ERRORS' => [],
		];
		Common::log(date('d.m.Y') . ' - Импорт продаж по реализации');

		self::initHttp();

		$url = self::HOST . '/realization';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/realization/list.html';
		file_put_contents($reportFileName, $res['CONTENT']);

		Parser::realization($res['CONTENT'], $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Продажи по реализации - заданный файл месячного отчета
	 * @param $hist
	 * @param $log
	 * @return bool
	 */
	public static function realizationItem($hist, &$log)
	{
		Common::log('Новый отчет продаж по реализации: ' . $hist['XML_ID']);

		self::initHttp();

		$url = self::HOST . '/realization/getreportdetails/';
		$post =
			'draw=2&columns%5B0%5D%5Bdata%5D=GoodsIncomeId&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=SubjectName&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=Article&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=BrandName&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=SupplierArticle&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=Size&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=Barcode&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=DocumentType&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=Quantity&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=Nds&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=CostAmount&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=true&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B11%5D%5Bdata%5D=RetailPrice&columns%5B11%5D%5Bname%5D=&columns%5B11%5D%5Bsearchable%5D=true&columns%5B11%5D%5Borderable%5D=true&columns%5B11%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B11%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B12%5D%5Bdata%5D=RetailPriceRu&columns%5B12%5D%5Bname%5D=&columns%5B12%5D%5Bsearchable%5D=true&columns%5B12%5D%5Borderable%5D=true&columns%5B12%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B12%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B13%5D%5Bdata%5D=RetailAmount&columns%5B13%5D%5Bname%5D=&columns%5B13%5D%5Bsearchable%5D=true&columns%5B13%5D%5Borderable%5D=true&columns%5B13%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B13%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B14%5D%5Bdata%5D=RetailCommission&columns%5B14%5D%5Bname%5D=&columns%5B14%5D%5Bsearchable%5D=true&columns%5B14%5D%5Borderable%5D=true&columns%5B14%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B14%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B15%5D%5Bdata%5D=SalePercent&columns%5B15%5D%5Bname%5D=&columns%5B15%5D%5Bsearchable%5D=true&columns%5B15%5D%5Borderable%5D=true&columns%5B15%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B15%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B16%5D%5Bdata%5D=CommissionPercent&columns%5B16%5D%5Bname%5D=&columns%5B16%5D%5Bsearchable%5D=true&columns%5B16%5D%5Borderable%5D=true&columns%5B16%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B16%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B17%5D%5Bdata%5D=CustomerReward&columns%5B17%5D%5Bname%5D=&columns%5B17%5D%5Bsearchable%5D=true&columns%5B17%5D%5Borderable%5D=true&columns%5B17%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B17%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B18%5D%5Bdata%5D=SupplierReward&columns%5B18%5D%5Bname%5D=&columns%5B18%5D%5Bsearchable%5D=true&columns%5B18%5D%5Borderable%5D=true&columns%5B18%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B18%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B19%5D%5Bdata%5D=WarehouseName&columns%5B19%5D%5Bname%5D=&columns%5B19%5D%5Bsearchable%5D=true&columns%5B19%5D%5Borderable%5D=true&columns%5B19%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B19%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B20%5D%5Bdata%5D=ReasonForPayment&columns%5B20%5D%5Bname%5D=&columns%5B20%5D%5Bsearchable%5D=true&columns%5B20%5D%5Borderable%5D=true&columns%5B20%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B20%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B21%5D%5Bdata%5D=OrderPkDate&columns%5B21%5D%5Bname%5D=&columns%5B21%5D%5Bsearchable%5D=true&columns%5B21%5D%5Borderable%5D=true&columns%5B21%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B21%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=desc&start=0&length=-1&search%5Bvalue%5D=&search%5Bregex%5D=false&reportId=' .
			$hist['XML_ID'];
		$res = self::$http->post($url, $post);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return false;
			}

			$res = self::$http->post($url, $post);
		}

		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки файла.');
			$log['ERRORS'][] = 'Ошибка загрузки файла.';

			return false;
		}

		$fn = $hist['XML_ID'] . '.json';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/realization/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		return $res['CONTENT'];
	}

	/**
	 * Отчет по продажам
	 * @return array
	 */
	public static function sales()
	{
		$log = [
			'ERRORS' => [],
		];

		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/sales/last.txt';
		$lastSalesDay = file_get_contents($fileName);

		$last = MakeTimeStamp($lastSalesDay) + 86400;
		$daysBefore = 10;
		// Первого числа закачиваем продажи за 3 месяца
		if (date('d') == 1)
			$daysBefore = 90;

		$now = MakeTimeStamp(date('d.m.Y'));
		$ts = $now - $daysBefore * 86400;
		if ($last < $ts)
			$ts = $last;

		while ($ts < $now)
		{
			$dateF = date('d.m.Y', $ts);

			$res = self::salesDate($dateF, $log);
			if ($res)
				file_put_contents($fileName, $dateF);
			else
				break;

			$ts += 86400;
		}

		$log['TEXT'] =
			$log['COUNTS']['DAYS'] . ', ' . $log['COUNTS']['ROWS'] . ': +' . $log['COUNTS']['ADDED'] . ', &' .
			$log['COUNTS']['CHANGED'];
		if (!$log['COUNTS']['ADDED'] && !$log['COUNTS']['CHANGED'] && !$log['COUNTS']['EXISTS'])
			$log['WARNINGS'] = true;

		return $log;
	}

	/**
	 * Отчет по продажам за один день
	 * @param $dateF
	 * @param $log
	 * @return bool
	 */
	public static function salesDate($dateF, &$log)
	{
		Common::log(date('d.m.Y') . ' - Импорт данных по продажам за ' . $dateF);

		self::initHttp();

		$stores = Stores::getAll();
		foreach ($stores['ITEMS'] as $store)
		{
			Common::log('Склад: ' . $store['NAME']);

			$url = self::HOST . '/aspx/report.aspx?rpid=1';
			$res = self::$http->get($url);

			if ($res['http_code'] == 302)
			{
				Common::log('Необходима авторизация');
				if (!self::login())
				{
					$log['ERRORS']['A'] = 'Ошибка авторизации';

					return false;
				}

				$res = self::$http->get($url);
			}

			$params = Common::strParts($res['CONTENT'], [
				'name="__EVENTTARGET"',
				'value="',
				'"',
				'name="__EVENTARGUMENT"',
				'value="',
				'"',
				'name="__LASTFOCUS"',
				'value="',
				'"',
				'name="__VIEWSTATE"',
				'value="',
				'"',
				'name="__VIEWSTATEGENERATOR"',
				'value="',
				'"',
				'name="__EVENTVALIDATION"',
				'value="',
				'"',
			]);

			$post = 'ScriptManager1=ScriptManager1%7CReportViewer%24ctl04%24ctl00' .
				'&ReportTitle=%D0%9F%D1%80%D0%BE%D0%B4%D0%B0%D0%B6%D0%B8' . '&ReportViewer%24ctl03%24ctl00=' .
				'&ReportViewer%24ctl03%24ctl01=' . '&ReportViewer%24ctl10=ltr' . '&ReportViewer%24ctl11=standards' .
				'&ReportViewer%24AsyncWait%24HiddenCancelField=False' . '&ReportViewer%24ctl04%24ctl03%24txtValue=' .
				$dateF . '&ReportViewer%24ctl04%24ctl05%24txtValue=' . $dateF .
				'&ReportViewer%24ctl04%24ctl07%24ddValue=1' . '&ReportViewer%24ctl04%24ctl09%24ddValue=1' .
				'&ReportViewer%24ctl04%24ctl11%24ddValue=' . $store['XML_ID'] . '&ReportViewer%24ToggleParam%24store=' .
				'&ReportViewer%24ToggleParam%24collapse=false' . '&ReportViewer%24ctl08%24ClientClickedId=' .
				'&ReportViewer%24ctl07%24store=' . '&ReportViewer%24ctl07%24collapse=false' .
				'&ReportViewer%24ctl09%24VisibilityState%24ctl00=None' . '&ReportViewer%24ctl09%24ScrollPosition=' .
				'&ReportViewer%24ctl09%24ReportControl%24ctl02=' . '&ReportViewer%24ctl09%24ReportControl%24ctl03=' .
				'&ReportViewer%24ctl09%24ReportControl%24ctl04=100' . '&__EVENTTARGET=' . urlencode($params[2]) .
				'&__EVENTARGUMENT=' . urlencode($params[5]) . '&__LASTFOCUS=' . urlencode($params[8]) .
				'&__VIEWSTATE=' . urlencode($params[11]) . '&__VIEWSTATEGENERATOR=' . urlencode($params[14]) .
				'&__EVENTVALIDATION=' . urlencode($params[17]) . '&__ASYNCPOST=true' .
				'&ReportViewer%24ctl04%24ctl00=View%20Report';

			$res = self::$http->post($url, $post, '', [
				'X-MicrosoftAjax: Delta=true',
				'X-Requested-With: XMLHttpRequest',
			]);

			$ar = Common::strParts($res['CONTENT'], [
				'"ExportUrlBase":"',
				'"',
			]);
			if (count($ar) < 3)
			{
				Common::log('Не найден "ExportUrlBase"');
				$log['ERRORS']['E'] = 'Не найден код для загрузки данных';

				return false;
			}

			$format = 'CSV';
			$ExportUrlBase = str_replace('\u0026', '&', $ar[1]);
			$url = self::HOST . $ExportUrlBase . $format;

			Common::log("URL запроса:\n" . $url);

			$res = self::$http->get($url);
			if ($res['http_code'] != 200)
			{
				Common::log('Ошибка загрузки данных');
				$log['ERRORS']['L'] = 'Ошибка загрузки данных';

				return false;
			}

			$dir = substr($dateF, 6, 4) . '_' . substr($dateF, 3, 2);
			$fn = $dateF . '_' . $store['EN'] . '.csv';
			$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/sales/' . $dir . '/';
			CheckDirPath($reportFileName);
			$reportFileName .= $fn;
			Common::log('Файл с данными: ' . $dir . '/' . $fn);
			file_put_contents($reportFileName, $res['CONTENT']);

			Parser::sales($res['CONTENT'], $dateF, $store, $log);
		}

		Common::log("Импорт завершен.\n");

		return true;
	}

	/**
	 * Отчет по товарам в пути
	 * @return array
	 */
	public static function shipping()
	{
		$log = [
			'ERRORS' => [],
		];

		Common::log(date('d.m.Y') . ' - Импорт товаров пути');

		self::initHttp();

		$url = self::HOST . '/aspx/report.aspx?rpid=21';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		$ar = Common::strParts($res['CONTENT'], [
			'"ExportUrlBase":"',
			'"',
		]);
		if (count($ar) < 2)
		{
			Common::log('Не найден "ExportUrlBase"');
			$log['ERRORS'][] = 'Не найден код файла для скачивания';

			return $log;
		}

		$format = 'CSV';
		$ExportUrlBase = str_replace('\u0026', '&', $ar[1]);
		$url = self::HOST . $ExportUrlBase . $format;

		Common::log("URL запроса:\n" . $url);

		$res = self::$http->get($url);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки данных');
			$log['ERRORS'][] = 'Ошибка загрузки данных';

			return $log;
		}

		$fn = date('Y_m_d_His') . '.csv';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/shipping/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		Parser::shipping($res['CONTENT'], $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Отчет дефицита
	 * @return array
	 */
	public static function deficit()
	{
		$log = [
			'ERRORS' => [],
		];

		Common::log(date('d.m.Y') . ' - Импорт дефицита');

		self::initHttp();

		// 1. Задание на генерацию файла отчета
		$url = self::HOST . '/shortage/downloadexcelother-request';
		$res = self::$http->get($url);

		if ($res['http_code'] == 302)
		{
			Common::log('Необходима авторизация');
			if (!self::login())
			{
				$log['ERRORS'][] = 'Ошибка авторизации';

				return $log;
			}

			$res = self::$http->get($url);
		}

		$deficitWbId = json_decode($res['CONTENT'], true);
		if (!$deficitWbId)
		{
			Common::log('Не найден код файла');
			$log['ERRORS'][] = 'Не найден код файла для скачивания';

			return $log;
		}

		// 2. Проверка готовности отчета
		$url = self::HOST . '/shortage/isready/' . $deficitWbId;
		$ready = false;
		for ($i = 1; $i < 10; $i++)
		{
			$res = self::$http->get($url);
			$ans = json_decode($res['CONTENT'], true);
			if ($ans['isFaulted'])
			{
				Common::log('Ошибка формирования отчета');
				$log['ERRORS'][] = 'Ошибка формирования отчета';

				return $log;
			}

			if ($ans['isReady'] || $ans['isFaulted'])
			{
				$ready = true;
				break;
			}

			sleep(1);
		}

		if (!$ready)
		{
			Common::log('Отчет не сформирован за 10 секунд');
			$log['ERRORS'][] = 'Отчет не сформирован за 10 секунд';

			return $log;
		}

		// 3. Загрузка отчета
		$url = self::HOST . '/shortage/downloadexcel/' . $deficitWbId;
		self::$http->follow();
		$res = self::$http->get($url);
		if ($res['http_code'] != 200)
		{
			Common::log('Ошибка загрузки подготовленного отчета');
			$log['ERRORS'][] = 'Ошибка загрузки подготовленного отчета';

			return $log;
		}

		$fn = date('Y_m_d') . '.xls';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/deficit/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		file_put_contents($reportFileName, $res['CONTENT']);

		Parser::deficit($reportFileName, $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Данные по остаткам на ульяновском складе
	 * @return array
	 */
	public static function uln()
	{
		$log = [
			'ERRORS' => [],
		];

		$filePath = '/srv/ftp/avail/report.xls';

		Common::log(date('d.m.Y') . ' - Импорт остатков из 1С');

		if (!file_exists($filePath))
		{
			Common::log('Не найден файл');
			$log['ERRORS'][] = 'Не найден файл импорта';

			return $log;
		}

		$fn = date('Y_m_d_H_i') . '.xls';
		$reportFileName = $_SERVER['DOCUMENT_ROOT'] . '/_import/uln/' . $fn;
		Common::log('Файл с данными: ' . $fn);
		rename($filePath, $reportFileName);

		Parser::uln($reportFileName, $log);

		Common::log("Импорт завершен.\n");

		return $log;
	}

	/**
	 * Цены и скидки с сайта wildberries.ru
	 * @return array
	 */
	public static function prices()
	{
		$products = Products::getAll();
		$log = [
			'ERRORS' => [],
			'COUNTS' => [
				'ALL' => count($products['ITEMS']),
				'SKIP' => 0,
				'NULL' => 0,
				'OK' => 0,
				'LOAD' => 0,
				'PARSE' => 0,
				'WRONG' => 0,
			],
		];

		$path = $_SERVER['DOCUMENT_ROOT'] . '/_import/prices/';
		exec("rm -rf " . $path . '*');

		self::initHttp();

		$isAuthorized = self::pubLogin();
		if (!$isAuthorized)
		{
			$log['ERRORS'][] = 'Ошибка авторизации';

			return $log;
		}

		foreach ($products['ITEMS'] as $product)
		{
			if ($product['DISABLE'])
			{
				$log['COUNTS']['SKIP']++;
				continue;
			}

			$fn = $path . $product['XML_ID'] . '.json';
			$cardUrl = Products::getWBHref($product);
			$res = self::$http->post('https://www.wildberries.ru/content/cardpromo',
				'cod1s=' . $product['XML_ID'] . '&characteristicId=',
				$fn, ['Referer: ' . $cardUrl, 'X-Requested-With: XMLHttpRequest']);

			if ($res['http_code'] != 200)
			{
				$log['ERRORS']['L'] = 'Ошибка загрузки';
				$log['COUNTS']['LOAD']++;
				continue;
			}

			Parser::prices($fn, $product, $log);
		}

		$log['TEXT'] = $log['COUNTS']['ALL'] . ': +' . $log['COUNTS']['OK'];

		return $log;
	}

}