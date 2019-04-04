<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Разделы каталога
 * Class Sections
 * @package Local\Main
 */
class Sections
{
	const IBLOCK_ID = 1;
	const CACHE_PATH = 'Local/Main/Sections/';

	/**
	 * Возвращает все разделы
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

			$section = new \CIBlockSection();
			$rsItems = $section->GetList(
				array(),
				array(
					'IBLOCK_ID' => self::IBLOCK_ID,
				),
				false
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$name = trim($item['NAME']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $name,
					'CODE' => $item['CODE'],
					'PARENT' => intval($item['IBLOCK_SECTION_ID']),
				);
				$return['BY_NAME'][$name] = $id;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает раздел по ID
	 * @param $id
	 * @return string
	 */
	public static function getById($id)
	{
		$all = self::getAll();

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает раздел по названию
	 * @param $name
	 * @return mixed
	 */
	public static function getByName($name)
	{
		$all = self::getAll();

		$id = $all['BY_NAME'][$name];

		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет раздел
	 * @param $name
	 * @return mixed
	 */
	public static function add($name)
	{
		$section = new \CIBlockSection();
		$section->Add([
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
		]);

		self::getAll(true);

		return self::getByName($name);
	}
}