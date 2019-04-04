<?
namespace Local\Main;
use Local\System\ExtCache;

/**
 * Предложения
 * Class Offers
 * @package Local\Main
 */
class Offers
{
	const IBLOCK_ID = 2;
	const CACHE_PATH = 'Local/Main/Offers/';

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
					'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID',
					'PROPERTY_PRODUCT',
					'PROPERTY_SIZE',
					'PROPERTY_STOCKS',
					'PROPERTY_PRICE',
					'PROPERTY_COST',
					'PROPERTY_ARTICLE',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$bar = trim($item['XML_ID']);
				$bars = explode(',', $bar);
				$size = trim($item['PROPERTY_SIZE_VALUE']);
				$productId = intval($item['PROPERTY_PRODUCT_VALUE']);
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'BAR' => $bar,
					'PRODUCT' => $productId,
					'SIZE' => $size,
					'STOCKS' => intval($item['PROPERTY_STOCKS_VALUE']),
					'PRICE' => floatval($item['PROPERTY_PRICE_VALUE']),
					'COST' => floatval($item['PROPERTY_COST_VALUE']),
					'ARTICLE' => trim($item['PROPERTY_ARTICLE_VALUE']),
				);
				foreach ($bars as $bar)
					$return['BY_BAR'][$bar] = $id;
				$return['BY_PRODUCT'][$productId][$size] = $id;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает все элементы
	 * @param $productId
	 * @return array
	 */
	public static function getByProduct($productId)
	{
		$return = array();

		$el = new \CIBlockElement();
		$rsItems = $el->GetList(
			array(
				'PROPERTY_SIZE' => 'asc',
			),
			array(
				'IBLOCK_ID' => self::IBLOCK_ID,
				'PROPERTY_PRODUCT' => $productId,
			),
			false,
			false,
			array(
				'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID',
				'PROPERTY_PRODUCT',
				'PROPERTY_SIZE',
                'PROPERTY_STOCKS',
				'PROPERTY_PRICE',
				'PROPERTY_COST',
				'PROPERTY_ARTICLE',
			)
		);
		while ($item = $rsItems->Fetch())
		{
			$id = intval($item['ID']);
			$bar = trim($item['XML_ID']);
			$productId = intval($item['PROPERTY_PRODUCT_VALUE']);
			$return['ITEMS'][$id] = array(
				'ID' => $id,
				'NAME' => $item['NAME'],
				'CODE' => $item['CODE'],
				'BAR' => $bar,
				'PRODUCT' => $productId,
				'SIZE' => $item['PROPERTY_SIZE_VALUE'],
                'STOCKS' => intval($item['PROPERTY_STOCKS_VALUE']),
				'PRICE' => floatval($item['PROPERTY_PRICE_VALUE']),
				'COST' => floatval($item['PROPERTY_COST_VALUE']),
				'ARTICLE' => trim($item['PROPERTY_ARTICLE_VALUE']),
			);
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
	 * Возвращает элемент по штрихкоду
	 * @param $bar
	 * @return mixed
	 */
	public static function getByBar($bar)
	{
		$all = self::getAll();

		$id = $all['BY_BAR'][$bar];

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает элемент по товару и размеру
	 * @param $productId
	 * @param $size
	 * @return mixed
	 */
	public static function getByProductSize($productId, $size)
	{
		$all = self::getAll();

		$id = $all['BY_PRODUCT'][$productId][$size];

		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает ссылку на предложение
	 * @param $offer
	 * @param bool $blank
	 * @param array $product
	 * @param array $collection
	 * @return string
	 */
	public static function getA($offer, $blank = false, $product = [], $collection = [])
	{
		$href = self::getHref($offer, $product, $collection);
		$target = $blank ? ' target="_blank"' : '';

		return '<a' . $target . ' href="' . $href . '">' . $offer['NAME'] . '</a>';
	}

	/**
	 * Возвращает ссылку на предложение
	 * @param $offer
	 * @param array $product
	 * @param array $collection
	 * @return string
	 */
	public static function getHref($offer, $product = [], $collection = [])
	{
		if (!$product)
			$product = Products::getById($offer['PRODUCT']);

		return Products::getHref($product, $collection) . $offer['ID'] . '/';
	}

	/**
	 * Возвращает ссылку на элемент в админке
	 * @param $offer
	 * @return string
	 */
	public static function getAdminHref($offer)
	{
		return '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . self::IBLOCK_ID .
			'&type=catalog&ID=' . $offer['ID'];
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

		return self::getByBar($fields['XML_ID']);
	}

	/**
	 * Меняет штрихкод элемента
	 * @param $id
	 * @param $bar
	 * @return mixed
	 */
	public static function updateBar($id, $bar)
	{
		$el = new \CIBlockElement();
		$el->Update($id, [
			'XML_ID' => $bar,
		]);

		self::getAll(true);

		return self::getByBar($bar);
	}

	/**
	 * Обновляет значение остатков на местном складе
	 * @param $id
	 * @param $value
	 */
	public static function updateStocks($id, $value)
	{
		$el = new \CIBlockElement();
		$el->SetPropertyValuesEx($id, self::IBLOCK_ID, ['STOCKS' => $value]);
	}

	/**
	 * Обновляет цены
	 * @param $id
	 * @param $price
	 * @param $cost
	 */
	public static function updatePrices($id, $price, $cost)
	{
		$el = new \CIBlockElement();
		$el->SetPropertyValuesEx($id, self::IBLOCK_ID, ['PRICE' => $price, 'COST' => $cost]);
	}

	/**
	 * Обновляет артикул
	 * @param $id
	 * @param $article
	 */
	public static function updateArticle($id, $article)
	{
		$el = new \CIBlockElement();
		$el->SetPropertyValuesEx($id, self::IBLOCK_ID, ['ARTICLE' => $article]);
	}

	/**
	 * Удаляет элемент
	 * @param $id
	 */
	public static function delete($id)
	{
		\CIBlockElement::Delete($id);
	}
}