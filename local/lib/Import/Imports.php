<?
namespace Local\Import;

use Local\System\ExtCache;

/**
 * Скрипты импортов
 * Class Imports
 * @package Local\Main
 */
class Imports
{
	const IBLOCK_ID = 10;
	const CACHE_PATH = 'Local/Import/Imports/';

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
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID'
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'CODE' => $item['CODE'],
					'XML_ID' => $item['XML_ID'],
					'NAME' => $item['NAME'],
				);
				$return['BY_XML_ID'][$item['XML_ID']] = $id;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает элемент по ID
	 * @param $id
	 * @return string
	 */
	public static function getById($id)
	{
		$all = self::getAll();

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает элемент по XML_ID
	 * @param $xmlId
	 * @return string
	 */
	public static function getByXmlId($xmlId)
	{
		$all = self::getAll();
		$id = $all['BY_XML_ID'][$xmlId];

		return $all['ITEMS'][$id];
	}
}