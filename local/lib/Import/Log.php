<?
namespace Local\Import;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\User;

/**
 * Лог импорта в БД
 * Class Log
 * @package Local\Import
 */
class Log
{
	const ENTITY_ID = 6;

	/**
	 * Возвращает лог за указанный промежуток времени
	 * @param $from
	 * @param $to
	 * @return array|false
	 */
	public static function getByDate($from, $to = false)
	{
		$return = array();

		$filter = [
			'>=UF_END' => $from,
		];
		if ($to !== false)
		{
			$filter['<=UF_BEGIN'] = $to;
		}

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'filter' => $filter,
			'order' => ['ID' => 'desc'],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = [
				'ID' => $item['ID'],
				'IMPORT' => $item['UF_IMPORT'],
				'BEGIN' => $item['UF_BEGIN'],
				'END' => $item['UF_END'],
				'SUCCESS' => !!$item['UF_SUCCESS'],
				'MANUAL' => !!$item['UF_MANUAL'],
				'DATA' => json_decode($item['UF_DATA'], true),
				'USER' => intval($item['UF_USER']),
			];

		return $return;
	}

	/**
	 * Добавляет элемент
	 * @param $import
	 * @param $begin
	 * @param $end
	 * @param $success
	 * @param $agent
	 * @param $data
	 * @return array|int
	 */
	public static function add($import, $begin, $end, $success, $agent, $data)
	{
		$fields = [
			'UF_IMPORT' => $import,
			'UF_BEGIN' => $begin,
			'UF_END' => $end,
			'UF_SUCCESS' => $success ? 1 : 0,
			'UF_MANUAL' => $agent ? 0 : 1,
			'UF_DATA' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'UF_USER' => User::getCurrentUserId(),
		];

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