<?
namespace Local\System;

use Local\Main\Discount;

/**
 * Class Utils Утилиты проекта
 * @package Local\System
 */
class Utils
{
	public static $MONTH_NAMES = [
		'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
		'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь',
	];

	/**
	 * Склонение числительных
	 * @param $number
	 * @param $values
	 * @return mixed
	 */
	public static function numeral($number, $values)
	{
		$number = abs($number);
		$keys = array(2, 0, 1, 1, 1, 2);
		$mod = $number % 100;
		$key = $mod > 4 && $mod < 20 ? 2 : $keys[min($mod%10, 5)];

		return $values[$key];
	}

	/**
	 * @param $day string
	 * @param $priceHistory array история изменения цен с сортировкой по возрастанию
	 * @param $startPrice float начальная цена
	 * @return array
	 */
	public static function getProductPriceByDay($day, $priceHistory, $startPrice)
	{
		// Время начала дня
		$dayBegin = \Bitrix\Main\Type\DateTime::createFromUserTime($day);
		// Время конца дня
		$dayEnd = \Bitrix\Main\Type\DateTime::createFromUserTime($day . ' 23:59:59');

		// Для начала берем текущие значения
		$return = [
			'PRICE_FROM' => $startPrice,
			'PRICE_TO' => $startPrice,
			'DISCOUNT_FROM' => 0,
			'DISCOUNT_TO' => 0,
		];

		foreach ($priceHistory as $item)
		{
			// Время изменения цены
			$date = $item['UF_DATE'];
			// Если изменения позже даты - то выходим
			if ($date > $dayEnd)
				break;

			if ($date < $dayBegin)
			{
				if ($item['UF_PRICE_CHANGE'])
				{
					$return['PRICE_FROM'] = $item['UF_PRICE'];
					$return['PRICE_TO'] = $item['UF_PRICE'];
				}
				if ($item['UF_DISCOUNT_CHANGE'])
				{
					$return['DISCOUNT_FROM'] = $item['UF_DISCOUNT'];
					$return['DISCOUNT_TO'] = $item['UF_DISCOUNT'];
				}
			}
			else
			{
				if ($item['UF_PRICE_CHANGE'])
				{
					if ($item['UF_PRICE'] < $return['PRICE_FROM'])
						$return['PRICE_FROM'] = $item['UF_PRICE'];
					if ($item['UF_PRICE'] > $return['PRICE_TO'])
						$return['PRICE_TO'] = $item['UF_PRICE'];
				}
				if ($item['UF_DISCOUNT_CHANGE'])
				{
					if ($item['UF_DISCOUNT'] < $return['DISCOUNT_FROM'])
						$return['DISCOUNT_FROM'] = $item['UF_DISCOUNT'];
					if ($item['UF_DISCOUNT'] > $return['DISCOUNT_TO'])
						$return['DISCOUNT_TO'] = $item['UF_DISCOUNT'];
				}
			}
		}

		// Скидки вручную
		$discounts = Discount::getAll();
		foreach ($discounts as $discount)
		{
			if ($discount['FROM~'] <= $dayBegin && $dayBegin <= $discount['TO~'])
			{
				if ($discount['CODE'] < $return['DISCOUNT_FROM'])
					$return['DISCOUNT_FROM'] = $discount['CODE'];
				if ($discount['CODE'] > $return['DISCOUNT_TO'])
					$return['DISCOUNT_TO'] = $discount['CODE'];
			}
		}

		$return['PRICE'] = $return['PRICE_FROM'];
		if ($return['PRICE_FROM'] != $return['PRICE_TO'])
			$return['PRICE'] .= ' - ' . $return['PRICE_TO'];
		$return['DISCOUNT'] = $return['DISCOUNT_FROM'];
		if ($return['DISCOUNT_FROM'] != $return['DISCOUNT_TO'])
			$return['DISCOUNT'] .= ' - ' . $return['DISCOUNT_TO'];

		$return['RES_FROM'] = $return['PRICE_FROM'] * (1 - $return['DISCOUNT_TO'] / 100);
		$return['RES_TO'] = $return['PRICE_TO'] * (1 - $return['DISCOUNT_FROM'] / 100);
		$return['RES'] = number_format($return['RES_FROM'], 2, ',', '');
		if ($return['RES_FROM'] != $return['RES_TO'])
			$return['RES'] .= ' - ' . number_format($return['RES_TO'], 2, ',', '');

		$return['M_FROM'] = self::getWbMargin($return['RES_FROM']);
		$return['M_TO'] = self::getWbMargin($return['RES_TO']);

		$return['WIN_FROM'] = $return['RES_FROM'] - $return['M_FROM'];
		$return['WIN_TO'] = $return['RES_TO'] - $return['M_TO'];
		$return['WIN'] = number_format($return['WIN_FROM'], 2, ',', '');
		if ($return['WIN_FROM'] != $return['WIN_TO'])
			$return['WIN'] .= ' - ' . number_format($return['WIN_TO'], 2, ',', '');

		// Погрешность
		$return['WIN_FROM'] -= 0.01;
		$return['WIN_TO'] += 0.01;

		return $return;
	}

	public static function getWbMargin($price)
	{
		$res = ceil($price * 38) / 100;
		if ($res < 100)
			$res = 100;

		return $res;
	}

	public static function hl($s, $q)
	{
		if (strpos($s, $q) !== false)
			return implode('<b>' . $q . '</b>', explode($q, $s));
		else
			return $s;
	}

}
