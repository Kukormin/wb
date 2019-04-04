<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\DateTime;

/**
 * Остатки на складах
 * Class StocksHistory
 * @package Local\Main
 */
class StocksHistory
{
	const ENTITY_ID = 7;
	const CACHE_PATH = 'Local/Main/StocksHistory/';

	/**
	 * @param array $filter
	 * @return array
	 */
	public static function getGroupByDate($filter = [])
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'select' => [
				'UF_DATE',
				'SUM',
			],
			'filter' => $filter,
			'group' => 'UF_DATE',
			'runtime' => [
				new ExpressionField('SUM', 'SUM(UF_AMOUNT)'),
			],
			'order' => [
				'UF_DATE' => 'asc',
			],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * Возвращает остатки предложения на заданный день для конкретного склада
	 * @param $offer
	 * @param $date
	 * @param $store
	 * @return array|false
	 */
	public static function getByOfferDateStore($offer, $date, $store)
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'filter' => [
				'UF_OFFER' => $offer,
				'UF_DATE' => $date,
				'UF_STORE' => $store,
			],
		]);
		if ($item = $rsItems->Fetch())
			$return = $item;

		return $return;
	}

	/**
	 * Возвращает остатки по фильтру
	 * @param array $filter
	 * @return array
	 */
	public static function getByFilter($filter = [])
	{
		global $DB;
		$return = array();

		$join = '';
		$where = '1=1';
		if ($filter['>=UF_DATE'])
			$where .= " AND S.UF_DATE >= '{$filter['>=UF_DATE']->format('Y-m-d')}'";
		if ($filter['<=UF_DATE'])
			$where .= " AND S.UF_DATE <= '{$filter['<=UF_DATE']->format('Y-m-d')}'";
		if ($filter['UF_STORE'])
			$where .= " AND S.UF_STORE = {$filter['UF_STORE']}";
		if ($filter['UF_OFFER'])
			$where .= " AND S.UF_OFFER = {$filter['UF_OFFER']}";
		if ($filter['UF_PRODUCT'])
			if (is_array($filter['UF_PRODUCT']))
				$where .= " AND S.UF_PRODUCT IN (" . implode(',', $filter['UF_PRODUCT']) . ")";
			else
				$where .= " AND S.UF_PRODUCT = {$filter['UF_PRODUCT']}";
		if ($filter['UF_COLLECTION'])
		{
			$join .= ' LEFT JOIN `b_iblock_element_prop_s1` P ON P.IBLOCK_ELEMENT_ID = S.UF_PRODUCT';
			if (is_array($filter['UF_COLLECTION']))
				$where .= " AND P.PROPERTY_20 IN (" . implode(',', $filter['UF_COLLECTION']) . ")";
			else
				$where .= " AND P.PROPERTY_20 = {$filter['UF_COLLECTION']}";
		}

		$q = "
SELECT
	S.*
FROM `stocks_history` S
	{$join}
WHERE
	{$where}
ORDER BY S.UF_DATE desc
";
		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
		{
			$phpDateTime = new \DateTime($item['UF_DATE']);
			$item['UF_DATE'] = DateTime::createFromPhp($phpDateTime);
			$return[] = $item;
		}

		return $return;
	}

	/**
	 * Добавляет элемент
	 * @param $product
	 * @param $offer
	 * @param $store
	 * @param $amount
	 * @param $date
	 * @return array|int
	 */
	public static function addUpdate($product, $offer, $store, $amount, $date)
	{
		$ex = self::getByOfferDateStore($offer, $date, $store);
		if ($ex)
		{
			if ($ex['UF_AMOUNT'] != $amount)
			{
				$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
				$entity = HighloadBlockTable::compileEntity($entityInfo);
				$dataClass = $entity->getDataClass();
				$dataClass::update($ex['ID'], [
					'UF_AMOUNT' => $amount,
				]);
				return -2;
			}

			return -1;
		}

		$data = array(
			'UF_PRODUCT' => $product,
			'UF_OFFER' => $offer,
			'UF_STORE' => $store,
			'UF_AMOUNT' => $amount,
			'UF_DATE' => $date,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		return $id;
	}

}