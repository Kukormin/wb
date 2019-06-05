<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\DateTime;
use Local\System\ExtCache;

/**
 * История СПП (Скидка Постоянного Покупателя)
 * Class Spp
 * @package Local\Main
 */
class Spp
{
	const ENTITY_ID = 9;

	/**
	 * Добавляет элемент в историю СПП
	 * @param $brand
	 * @param $date
	 * @param $value
	 * @param $code
	 * @return array|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function add($brand, $date, $value, $code)
	{
		$data = array(
			'UF_BRAND' => $brand,
			'UF_DATE' => $date,
			'UF_VALUE' => $value,
			'UF_CODE' => $code,
		);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		return $id;
	}

	/**
	 * Возвращает историю СПП для бренда
	 * @param $brandId
	 * @return array
	 */
	public static function getByBrand($brandId)
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
				'UF_BRAND' => $brandId,
			],
		]);
		while ($item = $result->Fetch())
		{
			$return[] = $item;
		}

		return $return;
	}

	/**
	 * Возвращает всю историю СПП
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
		while ($item = $result->Fetch())
		{
			$return[$item['UF_CODE']] = $item;
		}

		return $return;
	}

}