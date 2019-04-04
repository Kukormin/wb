<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Цены и скидки на сайте
 * Class Prices
 * @package Local\Main
 */
class Prices
{
	const ENTITY_ID = 8;

	/**
	 * Добавляет элемент
	 * @param $data
	 * @return array|int
	 */
	public static function add($data)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		return $id;
	}

}