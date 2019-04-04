<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Склады
 * Class Stores
 * @package Local\Main
 */
class Stores
{
	const IBLOCK_ID = 5;
	const PODOLSK_ID = 4;
	const COMMON_ID = 0;
	const CACHE_PATH = 'Local/Main/Stores/';

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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID',
					'PROPERTY_TITLE',
					'PROPERTY_EN',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'CODE' => $item['CODE'],
					'NAME' => $item['NAME'],
					'XML_ID' => $item['XML_ID'],
					'TITLE' => $item['PROPERTY_TITLE_VALUE'],
					'EN' => $item['PROPERTY_EN_VALUE'],
				);
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
}