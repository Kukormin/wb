<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Бренды
 * Class Brands
 * @package Local\Main
 */
class Brands
{
	const IBLOCK_ID = 3;
	const CACHE_PATH = 'Local/Main/Brands/';

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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PREVIEW_PICTURE',
					'PROPERTY_ACCOUNT'
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$name = trim($item['NAME']);
				$accountId = intval($item['PROPERTY_ACCOUNT_VALUE']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $name,
					'CODE' => $item['CODE'],
					'PIC' => \CFile::GetPath($item['PREVIEW_PICTURE']),
					'ACCOUNT' => $accountId,
				);
				$return['BY_NAME'][$accountId][$name] = $id;
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
	 * Возвращает элемент по названию
	 * @param $name
	 * @param $accountId
	 * @return mixed
	 */
	public static function getByName($name, $accountId)
	{
		$all = self::getAll();

		$id = $all['BY_NAME'][$accountId][$name];

		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет элемент
	 * @param $name
	 * @param $accountId
	 * @return mixed
	 */
	public static function add($name, $accountId)
	{
		$el = new \CIBlockElement();
		$el->Add([
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'PROPERTY_VALUES' => [
				'ACCOUNT' => $accountId,
			],
		]);

		self::getAll(true);

		return self::getByName($name, $accountId);
	}
}