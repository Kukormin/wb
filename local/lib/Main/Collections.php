<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Контракты
 * Class Collections
 * @package Local\Main
 */
class Collections
{
	const IBLOCK_ID = 9;
	const CACHE_PATH = 'Local/Main/Collections/';

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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE',
					'PROPERTY_BRAND',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'BRAND' => intval($item['PROPERTY_BRAND_VALUE']),
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

	/**
	 * Возвращает коллекцию по-умолчанию для бренда
	 * @param $brandId
	 * @param bool $refreshCache
	 * @return int|mixed
	 */
	public static function getDefaultId($brandId, $refreshCache = false)
	{
		$return = 0;

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$brandId,
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
					'PROPERTY_BRAND' => $brandId,
					'CODE' => 'default'
				),
				false,
				false,
				array(
					'ID',
				)
			);
			if ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return = $id;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Добавляет коллекцию по-умолчанию для бренда
	 * @param $brandId
	 * @return int|mixed
	 */
	public static function addDefault($brandId)
	{
		$el = new \CIBlockElement();
		$el->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'CODE' => 'default',
			'NAME' => 'Разобрать (по-умолчанию)',
			'PROPERTY_VALUES' => array(
				'BRAND' => $brandId,
			),
		));

		self::getAll(true);

		return self::getDefaultId($brandId, true);
	}
}