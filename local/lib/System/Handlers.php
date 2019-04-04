<?

namespace Local\System;

/**
 * Class Handlers Обработчики событий
 * @package Local\System
 */
class Handlers
{
	/**
	 * Добавление обработчиков
	 */
	public static function addEventHandlers()
	{
		static $added = false;
		if (!$added)
		{
			$added = true;
			AddEventHandler('iblock', 'OnIBlockPropertyBuildList', array(
				__NAMESPACE__ . '\Handlers',
				'iBlockPropertyBuildList'
			));
		}
	}

	/**
	 * Добавление пользовательских свойств
	 * @return array
	 */
	public static function iBlockPropertyBuildList()
	{
		return UserTypeNYesNo::GetUserTypeDescription();
	}

}