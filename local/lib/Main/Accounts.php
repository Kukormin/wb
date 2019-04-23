<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Учетные записи WB
 * Class Accounts
 * @package Local\Main
 */
class Accounts
{
	const IBLOCK_ID = 13;
	const CACHE_PATH = 'Local/Main/Accounts/';

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

			$el = new \CIBlockElement();
			$rsItems = $el->GetList(
				array(),
				array(
					'IBLOCK_ID' => self::IBLOCK_ID,
				),
				false,
				false,
				array(
					'ID', 'IBLOCK_ID', 'NAME', 'CODE',
				)
			);
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$ar = explode(':', $item['CODE']);
				$login = '';
				$pass = '';
				if (strlen($ar[0]) && strlen($ar[1]))
				{
					$login = $ar[0];
					$pass = $ar[1];
				}
				if ($login)
				{
					$return[$id] = [
						'ID' => $id,
						'NAME' => trim($item['NAME']),
						'LOGIN' => $login,
						'PASS' => $pass,
					];
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}
}