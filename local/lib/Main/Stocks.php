<?
namespace Local\Main;

use Bitrix\Main\Entity\ExpressionField;
use Local\System\ExtCache;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Остатки на складах
 * Class Stocks
 * @package Local\Main
 */
class Stocks
{
	const ENTITY_ID = 1;
	const CACHE_PATH = 'Local/Main/Stocks/';

	/**
	 * Возвращает все занчения текущих остатков
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAll($refreshCache = false)
	{
		return self::getByType(0, $refreshCache);
	}

	/**
	 * Возвращает все требуемые значения ("База")
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAllTarget($refreshCache = false)
	{
		return self::getByType(1, $refreshCache);
	}

	/**
	 * Возвращает все значения дефицита
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAllDeficit($refreshCache = false)
	{
		return self::getByType(2, $refreshCache);
	}

	/**
	 * Возвращает все значения указанного типа
	 * @param $type
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByType($type, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$type,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400 * 100
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();

			$rsItems = $dataClass::getList([
				'filter' => [
					'UF_TYPE' => $type,
				],
			]);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$offer = intval($item['UF_OFFER']);
				$store = intval($item['UF_STORE']);
				$return[$offer][$store] = array(
					'ID' => $id,
					'AMOUNT' => intval($item['UF_AMOUNT']),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает остатки заданных предложений
	 * @param $offerIds
	 * @return array
	 */
	public static function getByOffers($offerIds)
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();

		$rsItems = $dataClass::getList([
			'filter' => [
				'UF_TYPE' => 0,
				'UF_OFFER' => $offerIds,
			],
		]);
		while ($item = $rsItems->Fetch())
		{
			$id = intval($item['ID']);
			$offer = intval($item['UF_OFFER']);
			$store = intval($item['UF_STORE']);
			$return[$offer][$store] = array(
				'ID' => $id,
				'AMOUNT' => intval($item['UF_AMOUNT']),
			);
		}

		return $return;
	}

	/**
	 * Возвращает элемент с остатками
	 * @param $offer
	 * @param $store
	 * @return mixed
	 */
	public static function getItem($offer, $store)
	{
		$all = self::getAll();

		return $all[$offer][$store];
	}

	/**
	 * Возвращает остатки
	 * @param $offer
	 * @param $store
	 * @return int
	 */
	public static function getAmount($offer, $store)
	{
		$item = self::getItem($offer, $store);

		return intval($item['AMOUNT']);
	}

	/**
	 * Возвращает элемент с остатками
	 * @param $offer
	 * @param $store
	 * @return mixed
	 */
	public static function getTargetItem($offer, $store)
	{
		$all = self::getAllTarget();

		return $all[$offer][$store];
	}

	/**
	 * Возвращает остатки
	 * @param $offer
	 * @param $store
	 * @return int
	 */
	public static function getTargetAmount($offer, $store)
	{
		$item = self::getTargetItem($offer, $store);

		return intval($item['AMOUNT']);
	}

	/**
	 * Возвращает элемент с остатками
	 * @param $offer
	 * @param $store
	 * @return mixed
	 */
	public static function getDeficitItem($offer, $store)
	{
		$all = self::getAllDeficit();

		return $all[$offer][$store];
	}

	/**
	 * Возвращает остатки
	 * @param $offer
	 * @param $store
	 * @return int
	 */
	public static function getDeficitAmount($offer, $store)
	{
		$item = self::getDeficitItem($offer, $store);

		return intval($item['AMOUNT']);
	}

	/**
	 * Добавляет элемент
	 * @param $offer
	 * @param $store
	 * @param $amount
	 * @param $type
	 * @return array|int
	 */
	public static function add($offer, $store, $amount, $type = 0)
	{
		$data = array(
			'UF_OFFER' => $offer,
			'UF_STORE' => $store,
			'UF_AMOUNT' => $amount,
			'UF_TYPE' => $type,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		return $id;
	}

	/**
	 * Обновляет элемент
	 * @param $id
	 * @param $amount
	 */
	public static function update($id, $amount)
	{
		$data = array(
			'UF_AMOUNT' => $amount,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($id, $data);
	}

}