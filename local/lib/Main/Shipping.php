<?
namespace Local\Main;

use Local\System\ExtCache;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Товар в пути
 * Class Shipping
 * @package Local\Main
 */
class Shipping
{
	const ENTITY_ID = 4;
	const CACHE_PATH = 'Local/Main/Shipping/';

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

			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();

			$rsItems = $dataClass::getList();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$offer = intval($item['UF_OFFER']);
				$return[$offer] = array(
					'ID' => $id,
					'TO_CLIENT' => intval($item['UF_TO_CLIENT']),
					'FROM_CLIENT' => intval($item['UF_FROM_CLIENT']),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает элемент с остатками
	 * @param $offer
	 * @return mixed
	 */
	public static function getItem($offer)
	{
		$all = self::getAll();

		return $all[$offer];
	}

	/**
	 * Добавляет элемент
	 * @param $offer
	 * @param $toClient
	 * @param $fromClient
	 * @return array|int
	 */
	public static function add($offer, $toClient, $fromClient)
	{
		$data = array(
			'UF_OFFER' => $offer,
			'UF_TO_CLIENT' => $toClient,
			'UF_FROM_CLIENT' => $fromClient,
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
	 * @param $toClient
	 * @param $fromClient
	 */
	public static function update($id, $toClient, $fromClient)
	{
		$data = array(
			'UF_TO_CLIENT' => $toClient,
			'UF_FROM_CLIENT' => $fromClient,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($id, $data);
	}
}