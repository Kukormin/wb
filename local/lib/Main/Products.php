<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Товары
 * Class Products
 * @package Local\Main
 */
class Products
{
	const IBLOCK_ID = 1;
	const CACHE_PATH = 'Local/Main/Products/';

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
			86400
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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'IBLOCK_SECTION_ID', 'ACTIVE',
					'PROPERTY_BRAND',
					'PROPERTY_COLOR',
					'PROPERTY_ARTICLE_IMT',
					'PROPERTY_ARTICLE_COLOR',
					'PROPERTY_PRICE',
					'PROPERTY_START_PRICE',
					'PROPERTY_DISCOUNT',
					'PROPERTY_COLLECTION',
					'PROPERTY_DISABLE',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$xmlId = trim($item['XML_ID']);
				$code = trim($item['CODE']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'ACTIVE' => $item['ACTIVE'] == 'Y',
					'CODE' => $code,
					'XML_ID' => $xmlId,
					'SECTION' => intval($item['IBLOCK_SECTION_ID']),
					'BRAND' => intval($item['PROPERTY_BRAND_VALUE']),
					'COLOR' => intval($item['PROPERTY_COLOR_VALUE']),
					'ARTICLE_IMT' => $item['PROPERTY_ARTICLE_IMT_VALUE'],
					'ARTICLE_COLOR' => $item['PROPERTY_ARTICLE_COLOR_VALUE'],
					'PRICE' => floatval($item['PROPERTY_PRICE_VALUE']),
					'START_PRICE' => floatval($item['PROPERTY_START_PRICE_VALUE']),
					'DISCOUNT' => floatval($item['PROPERTY_DISCOUNT_VALUE']),
					'COLLECTION' => intval($item['PROPERTY_COLLECTION_VALUE']),
					'DISABLE' => $item['PROPERTY_DISABLE_VALUE'] == 1,
				);
				$return['BY_XML_ID'][$xmlId] = $id;
				$return['BY_CODE'][$code] = $id;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getByCollection($collectionId, $refreshCache = false)
	{
		$return = array();
		$collectionId = intval($collectionId);
		if (!$collectionId)
			return $return;

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$collectionId,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400
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
					'=PROPERTY_COLLECTION' => $collectionId,
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'IBLOCK_SECTION_ID', 'ACTIVE',
					'PROPERTY_BRAND',
					'PROPERTY_COLOR',
					'PROPERTY_ARTICLE_IMT',
					'PROPERTY_ARTICLE_COLOR',
					'PROPERTY_PRICE',
					'PROPERTY_START_PRICE',
					'PROPERTY_DISCOUNT',
					'PROPERTY_COLLECTION',
					'PROPERTY_DISABLE',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$xmlId = trim($item['XML_ID']);
				$code = trim($item['CODE']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'ACTIVE' => $item['ACTIVE'] == 'Y',
					'CODE' => $code,
					'XML_ID' => $xmlId,
					'SECTION' => intval($item['IBLOCK_SECTION_ID']),
					'BRAND' => intval($item['PROPERTY_BRAND_VALUE']),
					'COLOR' => intval($item['PROPERTY_COLOR_VALUE']),
					'ARTICLE_IMT' => $item['PROPERTY_ARTICLE_IMT_VALUE'],
					'ARTICLE_COLOR' => $item['PROPERTY_ARTICLE_COLOR_VALUE'],
					'PRICE' => floatval($item['PROPERTY_PRICE_VALUE']),
					'START_PRICE' => floatval($item['PROPERTY_START_PRICE_VALUE']),
					'DISCOUNT' => floatval($item['PROPERTY_DISCOUNT_VALUE']),
					'COLLECTION' => intval($item['PROPERTY_COLLECTION_VALUE']),
					'DISABLE' => $item['PROPERTY_DISABLE_VALUE'] == 1,
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

    /**
     * Возвращает название
     * @param $product
     * @return string
     */
    public static function getName($product)
    {
        return $product['NAME'] . ' ' . $product['CODE'];
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
	 * Возвращает элемент по коду
	 * @param $code
	 * @return mixed
	 */
	public static function getByCode($code)
	{
		$all = self::getAll();

		$id = $all['BY_CODE'][$code];

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает элемент по XML_ID
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
	 * Возвращает ссылку на товар
	 * @param $product
	 * @param bool $blank
	 * @param array $collection
	 * @return string
	 */
	public static function getA($product, $blank = false, $collection = [])
	{
		$href = self::getHref($product, $collection);
		$target = $blank ? ' target="_blank"' : '';

		return '<a' . $target . ' href="' . $href . '">' . $product['NAME'] . '</a>';
	}

	/**
	 * Возвращает ссылку на товар
	 * @param $product
	 * @param array $collection
	 * @return string
	 */
	public static function getHref($product, $collection = [])
	{
		if (!$collection)
			$collection = Collections::getById($product['COLLECTION']);

		if (!$collection)
			return self::getAdminHref($product);

		return '/brands/' . $collection['BRAND'] . '/' . $collection['ID'] . '/' . $product['ID'] . '/';
	}

	/**
	 * Возвращает ссылку на элемент в админке
	 * @param $product
	 * @return string
	 */
	public static function getAdminHref($product)
	{
		return '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . self::IBLOCK_ID .
			'&type=catalog&ID=' . $product['ID'];
	}

	/**
	 * Возвращает ссылку на товар wildberries
	 * @param $product
	 * @return string
	 */
	public static function getWBHref($product)
	{
		return 'https://www.wildberries.ru/catalog/' . $product['XML_ID'] . '/detail.aspx';
	}

	/**
	 * Добавляет элемент
	 * @param $fields
	 * @return mixed
	 */
	public static function add($fields)
	{
		$fields['IBLOCK_ID'] = self::IBLOCK_ID;
		$el = new \CIBlockElement();
		$el->Add($fields);

		self::getAll(true);

		return self::getByXmlId($fields['XML_ID']);
	}

	/**
	 * Обновляет цену и скидку
	 * @param $id
	 * @param $price
	 * @param $discount
	 */
	public static function updatePriceDiscount($id, $price, $discount)
	{
		$el = new \CIBlockElement();
		$el->SetPropertyValuesEx($id, self::IBLOCK_ID, [
			'PRICE' => $price,
			'DISCOUNT' => $discount,
		]);

		self::getAll(true);
	}
}