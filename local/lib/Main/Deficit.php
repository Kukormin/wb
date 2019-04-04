<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Дефицит по дням
 * Class Sales
 * @package Local\Main
 */
class Deficit
{
	const ENTITY_ID = 5;
	const LAST_FILE_NAME = '/_import/deficit/last.txt';

	/**
	 * Сохраняет название последнего загруженного файла
	 * @param $fileName
	 */
	public static function saveLast($fileName)
	{
		file_put_contents($_SERVER["DOCUMENT_ROOT"]. self::LAST_FILE_NAME, $fileName);
	}

	/**
	 * Возвращает название последнего загруженного файла
	 * @return bool|mixed|string
	 */
	public static function loadLast()
	{
		return file_get_contents($_SERVER["DOCUMENT_ROOT"]. self::LAST_FILE_NAME);
	}


	/**
	 * Возвращает дефицит за все дни
	 * @return array|false
	 */
	public static function getAll()
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList();
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * Возвращает дефицит предложения на заданный день
	 * @param $offer
	 * @param $date
	 * @return array|false
	 */
	public static function getByOfferDate($offer, $date)
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'filter' => [
				'UF_OFFER' => $offer,
				'UF_DATE' => $date,
			],
		]);
		if ($item = $rsItems->Fetch())
			$return = $item;

		return $return;
	}

	/**
	 * Возвращает дефицит товара
	 * @param $offer
	 * @return array
	 */
	public static function getByOffer($offer)
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
				'UF_OFFER' => $offer,
			],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * Возвращает текущий дефицит всех товаров
	 * @return array
	 */
	public static function getAllCurrent()
	{
		$return = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$rsItems = $dataClass::getList([
			'order' => [
				'UF_DATE' => 'desc',
			],
		]);
		while ($item = $rsItems->Fetch())
		{
			$offer = intval($item['UF_OFFER']);
			if (!isset($return[$offer]))
				$return[$offer] = intval($item['UF_VALUE']);
		}

		return $return;
	}

	/**
	 * Возвращает дефицит для диапазона дат
	 * @param $offer
	 * @param $from
	 * @param $to
	 * @return array
	 */
	public static function getByOfferAndDates($offer, $from, $to)
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
				'UF_OFFER' => $offer,
				'>=UF_DATE' => $from,
				'<=UF_DATE' => $to,
			],
		]);
		while ($item = $rsItems->Fetch())
			$return[] = $item;

		return $return;
	}

	/**
	 * Возвращает дефицит всех товаров для диапазона дат
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
     * @param $offer
     * @param $date
     * @param $value
     * @return array|int
     */
	public static function add($offer, $date, $value)
	{
	    $fields = [
	        'UF_DATE' => $date,
	        'UF_OFFER' => $offer,
	        'UF_VALUE' => $value,
        ];

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($fields);
		$id = $result->getId();

		return $id;
	}

}