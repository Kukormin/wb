<?
namespace Local\Main;

use Bitrix\Main\Type\DateTime;
use Local\System\ExtCache;

/**
 * Скидки вручную
 * Class Discount
 * @package Local\Main
 */
class Discount
{
	const IBLOCK_ID = 11;
	const CACHE_PATH = 'Local/Main/Discount/';

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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'FROM' => $item['DATE_ACTIVE_FROM'],
					'TO' => $item['DATE_ACTIVE_TO'],
					'FROM~' => DateTime::createFromUserTime($item['DATE_ACTIVE_FROM']),
					'TO~' => DateTime::createFromUserTime($item['DATE_ACTIVE_TO']),
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

		return $all[$id];
	}

	/**
	 * Добавляет элемент
	 * @param $name
	 * @param $from
	 * @param $to
	 * @param $value
	 */
	public static function add($name, $from, $to, $value)
	{
		$el = new \CIBlockElement();
		$el->Add([
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => htmlspecialchars(trim($name)),
			'DATE_ACTIVE_FROM' => htmlspecialchars(trim($from)),
			'DATE_ACTIVE_TO' => htmlspecialchars(trim($to)),
			'CODE' => intval($value),
		]);

		self::getAll(true);
	}

	/**
	 * Обновляет элемент
	 * @param $id
	 * @param $name
	 * @param $from
	 * @param $to
	 * @param $value
	 */
	public static function update($id, $name, $from, $to, $value)
	{
		$el = new \CIBlockElement();
		$el->Update($id, [
			'NAME' => htmlspecialchars(trim($name)),
			'DATE_ACTIVE_FROM' => htmlspecialchars(trim($from)),
			'DATE_ACTIVE_TO' => htmlspecialchars(trim($to)),
			'CODE' => intval($value),
		]);

		self::getAll(true);
	}

}