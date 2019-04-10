<?
namespace Local\Import;

use Local\System\ExtCache;

/**
 * Настройки
 * Class Options
 * @package Local\Import
 */
class Options
{
	const IBLOCK_ID = 12;
	const CACHE_PATH = 'Local/Main/Import/';

	/**
	 * Возвращает все элементы
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function get($refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400 * 100
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$el = new \CIBlockElement();
			$rsItems = $el->GetList(
				array(),
				array(
					'IBLOCK_ID' => self::IBLOCK_ID,
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'NAME', 'CODE',
					'PROPERTY_LOGIN',
					'PROPERTY_PASS',
					'PROPERTY_PUB_LOGIN',
					'PROPERTY_PUB_PASS',
				)
			);
			if ($item = $rsItems->Fetch())
			{
				$return = array(
					'LOGIN' => $item['PROPERTY_LOGIN_VALUE'],
					'PASS' => $item['PROPERTY_PASS_VALUE'],
					'PUB_LOGIN' => $item['PROPERTY_PUB_LOGIN_VALUE'],
					'PUB_PASS' => $item['PROPERTY_PUB_PASS_VALUE'],
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

}