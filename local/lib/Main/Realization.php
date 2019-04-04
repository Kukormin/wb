<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\ExtCache;

/**
 * Продажи по реализации
 * Class Realization
 * @package Local\Main
 */
class Realization
{
	const IBLOCK_ID = 8;
	const ENTITY_ID = 2;
	const CACHE_PATH = 'Local/Main/Realization/';

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
				array(
					'XML_ID' => 'desc',
				),
				array(
					'IBLOCK_ID' => self::IBLOCK_ID,
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'ACTIVE',
					'PROPERTY_SALES',
					'PROPERTY_COST',
					'PROPERTY_FEE',
					'PROPERTY_DISCOUNT',
					'PROPERTY_FROM',
					'PROPERTY_TO',
					'PROPERTY_WEEK',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$xmlId = intval($item['XML_ID']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'XML_ID' => $xmlId,
					'ACTIVE' => $item['ACTIVE'] == 'Y',
					'SALES' => $item['PROPERTY_SALES_VALUE'],
					'COST' => $item['PROPERTY_COST_VALUE'],
					'FEE' => $item['PROPERTY_FEE_VALUE'],
					'DISCOUNT' => $item['PROPERTY_DISCOUNT_VALUE'],
					'FROM' => $item['PROPERTY_FROM_VALUE'],
					'TO' => $item['PROPERTY_TO_VALUE'],
					'WEEK' => $item['PROPERTY_WEEK_VALUE'] == 1,
				);
				$return['BY_XML_ID'][$xmlId] = $id;
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
	 * @param $xmlId
	 * @return mixed
	 */
	public static function getByXmlId($xmlId)
	{
		$all = self::getAll();

		$id = $all['BY_XML_ID'][$xmlId];

		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет элемент
	 * @param $name
	 * @param $code
	 * @param $from
	 * @param $to
	 * @param $xmlId
	 * @param $sales
	 * @param $cost
	 * @param $fee
	 * @param $discount
	 * @param bool $week
	 * @return mixed
	 */
	public static function addItem($name, $code, $from, $to, $xmlId, $sales, $cost, $fee, $discount, $week = false)
	{
		$el = new \CIBlockElement();
		$el->Add([
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'CODE' => $code,
			'XML_ID' => $xmlId,
			'ACTIVE' => 'N',
			'PROPERTY_VALUES' => [
				'SALES' => $sales,
				'COST' => $cost,
				'FEE' => $fee,
				'DISCOUNT' => $discount,
				'FROM' => $from,
				'TO' => $to,
				'WEEK' => $week ? 1 : 0,
			],
		]);

		self::getAll(true);

		return self::getByXmlId($xmlId);
	}

	/**
	 * Обновляет элемент
	 * @param $hist
	 * @param $sales
	 * @param $cost
	 * @param $fee
	 * @param $discount
	 * @return string
	 */
	public static function updateItem($hist, $sales, $cost, $fee, $discount)
	{
		if ($hist['SALES'] != $sales || $hist['COST'] != $cost || $hist['FEE'] != $fee || $hist['DISCOUNT'] != $discount)
		{
			$el = new \CIBlockElement();
			$el->SetPropertyValuesEx($hist['ID'], self::IBLOCK_ID, [
				'SALES' => $sales,
				'COST' => $cost,
				'FEE' => $fee,
				'DISCOUNT' => $discount,
			]);

			self::getAll(true);
		}

		return self::getById($hist['ID']);
	}

	/**
	 * Активирует элемент
	 * @param $id
	 */
	public static function activeItem($id)
	{
		$el = new \CIBlockElement();
		$el->Update($id, [
			'ACTIVE' => 'Y',
		]);

		self::getAll(true);
	}

}