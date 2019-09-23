<?

namespace Local\Import;

/**
 * HTTP запросы через библиотеку curl
 * Class CurlHTTP
 * @package Local\Import
 */
class CurlHTTP
{
	const LOAD_LOG = true;

	private $USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0';
	private $ENCODING = 'gzip';
	private $auth;
	private $sslNoVerify = false;
	private $cookiesFile;
	private $cookie;
	private $verbose = false;
	public $sizeDownload = 0;
	public $reqCount = 0;
	public $ansCount = 0;
	public $follow = 0;
	public $proxy = '';
	public $proxyUserPass = '';

	public function __construct()
	{

	}

	/**
	 * Для запросов будет использоваться http авторизация
	 * @param $auth
	 */
	public function httpAuth($auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Для запросов с cookies
	 * @param $cookiesFile
	 * @param string $cookie
	 */
	public function cookies($cookiesFile, $cookie = '')
	{
		$this->cookiesFile = $cookiesFile;
		$this->cookie = $cookie;
	}

	/**
	 * Отключает проверку сертификатов
	 */
	public function sslNoVerify()
	{
		$this->sslNoVerify = true;
	}

	/**
	 * Разрешает переходы по редиректам
	 */
	public function follow()
	{
		$this->follow = 1;
	}

	/**
	 * Вывод лога
	 */
	public function verbose()
	{
		$this->verbose = true;
	}

	/**
	 * Для запросов через прокси
	 * @param $hostPort
	 * @param $userPass
	 */
	public function proxy($hostPort, $userPass = '')
	{
		$this->proxy = $hostPort;
		$this->proxyUserPass = $userPass;
	}

	/**
	 * простой GET запрос
	 * @param $url
	 * @param string $fileName
	 * @param array $headers
	 * @return mixed
	 */
	public function get($url, $fileName = '', $headers = array())
	{
		return $this->exec($url, $fileName, '-', $headers);
	}

	/**
	 * Простой POST запрос
	 * @param $url
	 * @param $post
	 * @param string $fileName
	 * @param array $headers
	 * @return mixed
	 */
	public function post($url, $post, $fileName = '', $headers = array())
	{
		return $this->exec($url, $fileName, $post, $headers);
	}

	/**
	 * Отправка запроса
	 * @param $url
	 * @param string $fileName
	 * @param string $post
	 * @param array $headers
	 * @return mixed
	 */
	private function exec($url, $fileName = '', $post = '-', $headers = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->USER_AGENT);
		curl_setopt($ch, CURLOPT_ENCODING, $this->ENCODING);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if ($post !== '-')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if ($headers)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if (substr($url, 0, 5) == 'https')
		{
			curl_setopt($ch, CURLOPT_SSLVERSION, 'all');
		}
		if ($this->cookie)
		{
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		}
		if ($this->cookiesFile)
		{
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiesFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiesFile);
		}
		if ($this->auth)
		{
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
		}
		if ($this->sslNoVerify)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		if ($this->proxy)
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
			if ($this->proxyUserPass)
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUserPass);
		}
		if ($this->follow)
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		if ($this->verbose)
		{
			$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/_import/curl.txt', 'w');
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_STDERR, $fp);
		}

		$this->reqCount++;

		// Выполняем запрос
		$content = curl_exec($ch);
		// Получаем информацию о результатах выполнения запроса
		$result = curl_getinfo($ch);
		$result['ERROR'] = curl_error($ch);
		$result['ERRNO'] = curl_errno($ch);

		curl_close($ch);

		// Сохраняем результат в файл
		if ($fileName)
			file_put_contents($fileName, $content);

		$this->ansCount++;
		$this->sizeDownload += $result['size_download'];

		// Добавляем в массив полученный контент
		$result['CONTENT'] = $content;

		return $result;
	}

	/**
	 * параллельные HTTP-запросы нескольких адресов
	 * @param $parts
	 * @return array
	 */
	public function multi($parts)
	{

		// Результат
		$result = array();

		// Массив для получения индекса массива для созданного соединения
		$indexByCh = array();

		// Создаем соединения
		$mh = curl_multi_init();
		foreach ($parts as $i => $part)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $part['url']);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->USER_AGENT);
			curl_setopt($ch, CURLOPT_ENCODING, $this->ENCODING);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if ($part['headers'])
			{
				curl_setopt($ch, CURLOPT_HTTPHEADER, $part['headers']);
			}
			if ($part['post'])
			{
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $part['post']);
			}
			if ($this->proxy)
				curl_setopt($ch, CURLOPT_PROXY, $this->proxy);

			$this->reqCount++;

			$indexByCh[intval($ch)] = $i;
			curl_multi_add_handle($mh, $ch);
		}

		// Запускаем
		$prev_running = $running = null;
		do
		{
			curl_multi_exec($mh, $running);

			if ($running != $prev_running)
			{
				// При загрузке очередного результата
				do
				{
					$multiInfo = curl_multi_info_read($mh);
					if (is_array($multiInfo) && ($ch = $multiInfo['handle']))
					{

						// Получаем ответ
						$content = curl_multi_getcontent($ch);
						// Получаем информацию о результатах выполнения запроса
						$info = curl_getinfo($ch);

						// Получаем индекс
						$i = $indexByCh[intval($ch)];

						// Сохраняем результат в файл
						if ($parts[$i]['log'])
							file_put_contents($parts[$i]['log'], $content);

						$this->ansCount++;
						$this->sizeDownload += $info['size_download'];

						// Добавляем в массив полученный контент
						$info['CONTENT'] = $content;
						$result[$i] = $info;

						curl_close($ch);
						curl_multi_remove_handle($mh, $ch);
					}
				} while ($multiInfo !== false);

				$prev_running = $running;
			}

		} while ($running > 0);

		curl_multi_close($mh);

		return $result;
	}

}