<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\ExtCache;

/**
 * История цен и скидок
 * Class PriceHistory
 * @package Local\Main
 */
class PriceHistory
{
	const IBLOCK_ID = 6;
	const ENTITY_ID = 2;
	const CACHE_PATH = 'Local/Main/PriceHistory/';

	/**
	 * Возвращает все элементы
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAllItems($refreshCache = false)
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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'ACTIVE',
					'PROPERTY_DATE',
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
					'DATE' => $item['PROPERTY_DATE_VALUE'],
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
		$all = self::getAllItems();

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает элемент по названию
	 * @param $xmlId
	 * @return mixed
	 */
	public static function getByXmlId($xmlId)
	{
		$all = self::getAllItems();

		$id = $all['BY_XML_ID'][$xmlId];

		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет элемент
	 * @param $name
	 * @param $xmlId
	 * @param $date
	 * @return mixed
	 */
	public static function addItem($name, $xmlId, $date)
	{
		$el = new \CIBlockElement();
		$el->Add([
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'XML_ID' => $xmlId,
			'CODE' => $date,
			'ACTIVE' => 'N',
			'PROPERTY_VALUES' => [
				'DATE' => str_replace('  ', ' ', $date),
			],
		]);

		self::getAllItems(true);

		return self::getByXmlId($xmlId);
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

		self::getAllItems(true);
	}

	/**
	 * Добавляет элемент в историю изменения цен и скидок
	 * @param $product
	 * @param $item
	 * @param $date
	 * @param $price
	 * @param $discount
	 * @param $promo
	 * @param $priceCh
	 * @param $discountCh
	 * @param $promoCh
	 * @return array|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function add($product, $item, $date, $price, $discount, $promo, $priceCh, $discountCh, $promoCh)
	{
		$data = array(
			'UF_PRODUCT' => $product,
			'UF_ITEM' => $item,
			'UF_DATE' => $date,
			'UF_PRICE' => $price,
			'UF_DISCOUNT' => $discount,
			'UF_PROMO' => $promo,
			'UF_PRICE_CHANGE' => $priceCh,
			'UF_DISCOUNT_CHANGE' => $discountCh,
			'UF_PROMO_CHANGE' => $promoCh,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		return $id;
	}

	/**
	 * Возвращает историю изменения цен для товара
	 * @param $product
	 * @return array
	 */
	public static function getByProduct($product)
	{
		$return = [];

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::getList([
			'order' => [
				'UF_DATE' => 'asc',
			],
			'filter' => [
				'LOGIC' => 'OR',
				['UF_PRODUCT' => $product],
				['=UF_PRODUCT' => 0],
			],
		]);
		while ($item = $result->Fetch())
		{
			$return[] = $item;
		}

		return $return;
	}

	/**
	 * Возвращает всю историю изменения цен
	 * @return array
	 */
	public static function getAll()
	{
		$return = [];

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::getList([
			'order' => [
				'UF_DATE' => 'asc',
			],
		]);
		$all = [];
		while ($item = $result->Fetch())
		{
			if ($item['UF_PRODUCT'])
			{
				if (!isset($return[$item['UF_PRODUCT']]))
				{
					foreach ($all as $it)
						$return[$item['UF_PRODUCT']][] = $it;
				}

				$return[$item['UF_PRODUCT']][] = $item;
			}
			else
			{
				$all[] = $item;
				foreach ($return as $productId => $ar)
					$return[$productId][] = $item;
			}
		}



		return $return;
	}

}