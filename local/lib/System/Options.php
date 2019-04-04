<?
namespace Local\System;

/**
 * Class Options Параметры
 * @package Local\System
 */
class Options
{
	const IBLOCK_ID = 1;
	const CACHE_PATH = 'Local/System/Options/';

	/**
	 * Возвращает все элементы
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAll($refreshCache = false)
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
					'ACTIVE' => 'Y',
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'CODE',
					'PROPERTY_VALUE',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$code = trim($item['CODE']);
				$value = $item['PROPERTY_VALUE_VALUE'];
				$return[$code] = $value;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает элемент по коду
	 * @param $code
	 * @return string
	 */
	public static function get($code)
	{
		$all = self::getAll();

		return $all[$code];
	}

}
