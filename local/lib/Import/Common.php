<?
namespace Local\Import;

/**
 * Общие методы импорта
 * Class Common
 * @package Local\Import
 */
class Common
{
	/**
	 * Путь до файла с логом
	 */
	const LOG = '/_import/log.txt';

	/**
	 * @var string Файл с логом
	 */
	private static $logFileName = '';

	/**
	 * Устанавливает название файла с логом
	 * @param $filename
	 */
	public static function setLogFilename($filename)
	{
		self::$logFileName = $_SERVER['DOCUMENT_ROOT'] . $filename;
	}

	/**
	 * Сохраняет строку в лог
	 * @param $text
	 */
	public static function log($text)
	{
		echo $text . "\n";
		if (!self::$logFileName)
			self::$logFileName = $_SERVER['DOCUMENT_ROOT'] . self::LOG;

		$f = fopen(self::$logFileName, 'a');
		fwrite($f, date('H:i:s'));
		fwrite($f, "\t");
		fwrite($f, $text);
		fwrite($f, "\n");
		fclose($f);
	}

	/**
	 * Разбивает строку на части
	 * @param $s
	 * @param $seps
	 * @return array
	 */
	public static function strParts($s, $seps)
	{
		$return = array();
		foreach ($seps as $sep)
		{
			$ar = explode($sep, $s, 2);

			if (!isset($ar[1]))
				break;

			$return[] = $ar[0];
			$s = $ar[1];
		}

		$return[] = $s;

		return $return;
	}

	/**
	 * Добавляет полоску оповещения в админке
	 * @param $message
	 */
	public static function addAdminNotify($message)
	{
		\CAdminNotify::Add([
			'MESSAGE' => $message,
			'TAG' => 'import',
			'MODULE_ID' => 'main',
			'ENABLE_CLOSE' => 'Y',
		]);
	}

}