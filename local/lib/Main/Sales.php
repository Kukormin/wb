<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

/**
 * Продажи по дням
 * Class Sales
 * @package Local\Main
 */
class Sales
{
	const ENTITY_ID = 3;

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
				'CNT',
				'SUM',
				'RETURN_CNT',
				'RETURN_SUM',
				'ORDER_CNT',
				'ORDER_SUM',
			],
			'filter' => $filter,
			'group' => 'UF_DATE',
			'runtime' => [
				new ExpressionField('CNT', 'SUM(UF_SALES)'),
				new ExpressionField('SUM', 'SUM(UF_SALES_PRICE)'),
				new ExpressionField('RETURN_CNT', 'SUM(UF_RETURN)'),
				new ExpressionField('RETURN_SUM', 'SUM(UF_RETURN_PRICE)'),
				new ExpressionField('ORDER_CNT', 'SUM(UF_ORDER)'),
				new ExpressionField('ORDER_SUM', 'SUM(UF_ORDER_PRICE)'),
			],
			'order' => [
				'UF_DATE' => 'asc',
			],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	public static function getTopProducts($key, $order, $collection, $interval)
	{
		global $DB;
		$return = array();

		$date = new DateTime();
		$date->add($interval);
		$dateF = $date->format('Y-m-d');

		$groupBy = 'S.UF_PRODUCT';
		$select = 'S.UF_PRODUCT AS UF_PRODUCT,';
		$join = 'LEFT JOIN `b_iblock_element_prop_s2` O ON O.IBLOCK_ELEMENT_ID = S.UF_OFFER';
		if ($collection)
		{
			$select = 'P.PROPERTY_20 AS COLLECTION,';
			$join .= ' LEFT JOIN `b_iblock_element_prop_s1` P ON P.IBLOCK_ELEMENT_ID = S.UF_PRODUCT';
			$groupBy = 'P.PROPERTY_20';
		}
		if ($key == 'SALES')
		{
			$select .= 'SUM(S.UF_SALES_PRICE - O.PROPERTY_8 * S.UF_SALES) AS `PRE_MARGIN`,';
			$select .= 'SUM(S.UF_SALES_PRICE - O.PROPERTY_23 * S.UF_SALES) AS `MARGIN`,';
		}

		$q = "
SELECT
	{$select}
	SUM(S.UF_{$key}) AS `CNT`,
	SUM(S.UF_{$key}_PRICE) AS `SUM`
FROM `sales` S
	{$join}
WHERE
	S.UF_DATE >= '{$dateF}'
GROUP BY
	{$groupBy}
ORDER BY
	`{$order}` DESC
LIMIT
	0, 10";

		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * @param DateTime $from
	 * @param DateTime $to
	 * @param int $store
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function getSummarySalesByCollection($from = null, $to = null, $store = 0)
	{
		global $DB;
		$return = array();

		$where = '1=1';
		if ($from)
			$where .= " AND S.UF_DATE >= '{$from->format('Y-m-d')}'";
		if ($to)
			$where .= " AND S.UF_DATE <= '{$to->format('Y-m-d')}'";
		if ($store)
			$where .= " AND S.UF_STORE = {$store}";

		$q = "
SELECT
	P.PROPERTY_20 AS COLLECTION,
	SUM(S.UF_ADMISSION) AS `UF_ADMISSION`,
	SUM(S.UF_ADMISSION_PRICE) AS `UF_ADMISSION_PRICE`,
	SUM(S.UF_ORDER) AS `UF_ORDER`,
	SUM(S.UF_ORDER_PRICE) AS `UF_ORDER_PRICE`,
	SUM(S.UF_RETURN) AS `UF_RETURN`,
	SUM(S.UF_RETURN_PRICE) AS `UF_RETURN_PRICE`,
	SUM(S.UF_SALES) AS `UF_SALES`,
	SUM(S.UF_SALES_PRICE) AS `UF_SALES_PRICE`,
	SUM(S.UF_REMISSION) AS `UF_REMISSION`,
	SUM(S.UF_REMISSION_PRICE) AS `UF_REMISSION_PRICE`
FROM `sales` S
	LEFT JOIN `b_iblock_element_prop_s1` P ON P.IBLOCK_ELEMENT_ID = S.UF_PRODUCT
WHERE
	{$where}
GROUP BY
	P.PROPERTY_20
";

		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
			$return[$item['COLLECTION']] = $item;

		return $return;
	}

	/**
	 * @param $productIds
	 * @param DateTime $from
	 * @param DateTime $to
	 * @param int $store
	 * @return array
	 */
	public static function getSummarySalesByProducts($productIds, $from = null, $to = null, $store = 0)
	{
		global $DB;
		$return = array();

		$where = 'S.UF_PRODUCT IN (' . implode(',',  $productIds) . ')';
		if ($from)
			$where .= " AND S.UF_DATE >= '{$from->format('Y-m-d')}'";
		if ($to)
			$where .= " AND S.UF_DATE <= '{$to->format('Y-m-d')}'";
		if ($store)
			$where .= " AND S.UF_STORE = {$store}";

		$q = "
SELECT
	S.UF_PRODUCT AS PRODUCT,
	SUM(S.UF_ADMISSION) AS `UF_ADMISSION`,
	SUM(S.UF_ADMISSION_PRICE) AS `UF_ADMISSION_PRICE`,
	SUM(S.UF_ORDER) AS `UF_ORDER`,
	SUM(S.UF_ORDER_PRICE) AS `UF_ORDER_PRICE`,
	SUM(S.UF_RETURN) AS `UF_RETURN`,
	SUM(S.UF_RETURN_PRICE) AS `UF_RETURN_PRICE`,
	SUM(S.UF_SALES) AS `UF_SALES`,
	SUM(S.UF_SALES_PRICE) AS `UF_SALES_PRICE`,
	SUM(S.UF_REMISSION) AS `UF_REMISSION`,
	SUM(S.UF_REMISSION_PRICE) AS `UF_REMISSION_PRICE`
FROM `sales` S
WHERE
	{$where}
GROUP BY
	S.UF_PRODUCT
";

		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
			$return[$item['PRODUCT']] = $item;

		return $return;
	}

	/**
	 * @param $offerIds
	 * @param DateTime $from
	 * @param DateTime $to
	 * @param int $store
	 * @return array
	 */
	public static function getSummarySalesByOffers($offerIds, $from = null, $to = null, $store = 0)
	{
		global $DB;
		$return = array();

		$where = 'S.UF_OFFER IN (' . implode(',',  $offerIds) . ')';
		if ($from)
			$where .= " AND S.UF_DATE >= '{$from->format('Y-m-d')}'";
		if ($to)
			$where .= " AND S.UF_DATE <= '{$to->format('Y-m-d')}'";
		if ($store)
			$where .= " AND S.UF_STORE = {$store}";

		$q = "
SELECT
	S.UF_OFFER AS OFFER,
	SUM(S.UF_ADMISSION) AS `UF_ADMISSION`,
	SUM(S.UF_ADMISSION_PRICE) AS `UF_ADMISSION_PRICE`,
	SUM(S.UF_ORDER) AS `UF_ORDER`,
	SUM(S.UF_ORDER_PRICE) AS `UF_ORDER_PRICE`,
	SUM(S.UF_RETURN) AS `UF_RETURN`,
	SUM(S.UF_RETURN_PRICE) AS `UF_RETURN_PRICE`,
	SUM(S.UF_SALES) AS `UF_SALES`,
	SUM(S.UF_SALES_PRICE) AS `UF_SALES_PRICE`,
	SUM(S.UF_REMISSION) AS `UF_REMISSION`,
	SUM(S.UF_REMISSION_PRICE) AS `UF_REMISSION_PRICE`
FROM `sales` S
WHERE
	{$where}
GROUP BY
	S.UF_OFFER
";

		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
			$return[$item['OFFER']] = $item;

		return $return;
	}

	/**
	 * Возвращает продажи предложения на заданный день для конкретного склада
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
	 * Возвращает продажи по фильтру
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
FROM `sales` S
	{$join}
WHERE
	{$where}
ORDER BY S.UF_DATE desc
";
		$rsItems = $DB->Query($q);
		while ($item = $rsItems->Fetch())
		{
			$phpDateTime = new \DateTime($item['UF_DATE']);
			$item['UF_DATE'] = Date::createFromPhp($phpDateTime);
			$return[] = $item;
		}

		return $return;
	}

	/**
	 * Возвращает продажи всех товаров для диапазона дат
	 * @param $from
	 * @param $to
	 * @return array
	 */
	public static function getByDates($from, $to)
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'order' => [
				'UF_DATE' => 'desc',
			],
			'filter' => [
				'>=UF_DATE' => $from,
				'<=UF_DATE' => $to,
			],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * Добавляет элемент
	 * @param $fields
	 * @return array|int
	 */
	public static function addUpdate($fields)
	{
		$ex = self::getByOfferDateStore($fields['UF_OFFER'], $fields['UF_DATE'], $fields['UF_STORE']);
		if ($ex)
		{
			if ($ex['UF_ADMISSION'] != $fields['UF_ADMISSION'] ||
				$ex['UF_ADMISSION_PRICE'] != $fields['UF_ADMISSION_PRICE'] ||
				$ex['UF_ORDER'] != $fields['UF_ORDER'] ||
				$ex['UF_ORDER_PRICE'] != $fields['UF_ORDER_PRICE'] ||
				$ex['UF_RETURN'] != $fields['UF_RETURN'] ||
				$ex['UF_RETURN_PRICE'] != $fields['UF_RETURN_PRICE'] ||
				$ex['UF_SALES'] != $fields['UF_SALES'] ||
				$ex['UF_SALES_PRICE'] != $fields['UF_SALES_PRICE'] ||
				$ex['UF_REMISSION'] != $fields['UF_REMISSION'] ||
				$ex['UF_REMISSION_PRICE'] != $fields['UF_REMISSION_PRICE'])
			{
				$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
				$entity = HighloadBlockTable::compileEntity($entityInfo);
				$dataClass = $entity->getDataClass();
				$dataClass::update($ex['ID'], $fields);
				return -2;
			}

			return -1;
		}

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($fields);
		$id = $result->getId();

		return $id;
	}

	/**
	 * Удаляет элемент
	 * @param $id
	 */
	public static function delete($id)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($id);
	}
}