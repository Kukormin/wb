<?
namespace Local\System;

/**
 * Дополнительные методы для работы с пользователем битрикса
 * Class User
 * @package Local\System
 */
class User
{
	/**
	 * @var bool Пользователь
	 */
	private static $user = false;
	private static $u = false;
	private static $uId = false;
	private static $users = [];

	/**
	 * Залогинен ли пользователь
	 * @return bool
	 */
	public static function isLogged()
	{
		$uid = self::getCurrentUser();
		return $uid ? true : false;
	}

	/**
	 * Залогинен ли пользователь
	 * @return bool
	 */
	public static function isAdmin()
	{
		//$uid = self::getCurrentUserId();

		return self::$u->IsAdmin();
	}

	/**
	 * Возвращает Id текущего пользователя
	 * @return bool|null
	 */
	public static function getCurrentUserId()
	{
		if (!self::$uId)
		{
			if (!self::$u)
				self::$u = new \CUser();
			self::$uId = self::$u->GetID();
		}
		return self::$uId;
	}

	/**
	 * Возвращает текущего пользователя
	 * @return array|bool
	 */
	public static function getCurrentUser()
	{
		if (self::$user === false)
		{
			if (!self::$u)
				self::$u = new \CUser();
			$userId = self::$u->GetID();
			if ($userId)
			{
				$rs = self::$u->GetByID($userId);
				$user = $rs->Fetch();
				if (!$user['UF_DATA'])
					$user['UF_DATA'] = '{}';
				self::$user = array(
					'ID' => $userId,
					'NAME' => $user['NAME'],
					'UF_DATA' => $user['UF_DATA'],
					'DATA' => json_decode($user['UF_DATA'], true),
				);
			}
			else
				self::$user = array();
		}

		return self::$user;
	}

	/**
	 * Возвращает пользователя по ID
	 * @param $userId
	 * @return array
	 */
	public static function getById($userId)
	{
		if (!isset(self::$users[$userId]))
		{
			$u = new \CUser();
			$rs = $u->GetByID($userId);
			$user = $rs->Fetch();
			if (!$user['UF_DATA'])
				$user['UF_DATA'] = '{}';
			self::$users[$userId] = [
				'ID' => $userId,
				'NAME' => $user['NAME'],
				'UF_DATA' => $user['UF_DATA'],
				'DATA' => json_decode($user['UF_DATA'], true),
			];
		}

		return self::$users[$userId];
	}

	/**
	 * Сохраняет разные настройки пользователя
	 * @param $new
	 */
	public static function saveData($new)
	{
		self::getCurrentUser();

		$data = self::$user['DATA'];
		foreach ($new as $key => $value)
			$data[$key] = $value;

		$encoded = json_encode($data, JSON_UNESCAPED_UNICODE);

		if (self::$user['UF_DATA'] != $encoded)
		{
			$u = new \CUser();
			$u->Update(self::$user['ID'], array(
				'UF_DATA' => $encoded,
			));
			self::$user['DATA'] = $data;
		}
	}

}
