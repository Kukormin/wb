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
	 * Возвращает СПП на заданный день для бренда
	 * @param $dayBegin
	 * @param $dayEnd
	 * @param $sppHistory
	 * @param $brand
	 * @return mixed
	 */
	public static function getSppByDay($dayBegin, $dayEnd, $sppHistory, $brand)
	{
		$brandSpp = 0;
		$allSpp = 0;
		foreach ($sppHistory as $item)
		{
			// Время изменения цены
			$date = $item['UF_DATE'];
			// Если изменения позже даты - то выходим
			if ($date > $dayEnd)
				break;

			if ($date < $dayBegin)
			{
				if ($item['UF_BRAND'] == $brand)
					$brandSpp = $item['UF_VALUE'];
				elseif (!$item['UF_BRAND'])
					$allSpp = $item['UF_VALUE'];
			}
			else
			{
				if ($item['UF_BRAND'] == $brand)
				{
					if ($item['UF_VALUE'] > $brandSpp)
						$brandSpp = $item['UF_VALUE'];
				}
				elseif (!$item['UF_BRAND'])
				{
					if ($item['UF_VALUE'] > $allSpp)
						$allSpp = $item['UF_VALUE'];
				}
			}
		}

		return max($brandSpp, $allSpp);
	}

	/**
	 * Возвращает цены и скидки на заданный день
	 * @param $day string
	 * @param $priceHistory array история изменения цен с сортировкой по возрастанию
	 * @param $startPrice float начальная цена
	 * @param $sppHistory array история изменения СПП
	 * @param $brand int Бренд
	 * @return array
	 */
	public static function getProductPriceByDay($day, $priceHistory, $startPrice, $sppHistory, $brand)
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
			'PROMO_FROM' => 0,
			'PROMO_TO' => 0,
			'ALL_PROMO_FROM' => 0,
			'ALL_PROMO_TO' => 0,
			'SPP_TO' => self::getSppByDay($dayBegin, $dayEnd, $sppHistory, $brand),
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
				if ($item['UF_PROMO_CHANGE'])
				{
					if ($item['UF_PRODUCT'])
					{
						$return['PROMO_FROM'] = $item['UF_PROMO'];
						$return['PROMO_TO'] = $item['UF_PROMO'];
					}
					else
					{
						$return['ALL_PROMO_FROM'] = $item['UF_PROMO'];
						$return['ALL_PROMO_TO'] = $item['UF_PROMO'];
					}
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
				if ($item['UF_PROMO_CHANGE'])
				{
					if ($item['UF_PRODUCT'])
					{
						if ($item['UF_PROMO'] < $return['PROMO_FROM'])
							$return['PROMO_FROM'] = $item['UF_PROMO'];
						if ($item['UF_PROMO'] > $return['PROMO_TO'])
							$return['PROMO_TO'] = $item['UF_PROMO'];
					}
					else
					{
						if ($item['UF_PROMO'] < $return['ALL_PROMO_FROM'])
							$return['ALL_PROMO_FROM'] = $item['UF_PROMO'];
						if ($item['UF_PROMO'] > $return['ALL_PROMO_TO'])
							$return['ALL_PROMO_TO'] = $item['UF_PROMO'];
					}
				}
			}
		}

		// Скидки вручную
		$discounts = Discount::getAll();
		foreach ($discounts as $discount)
		{
			if ($discount['FROM~'] <= $dayBegin && $dayBegin <= $discount['TO~'])
			{
				if ($discount['CODE'] < $return['ALL_PROMO_FROM'])
					$return['ALL_PROMO_FROM'] = $discount['CODE'];
				if ($discount['CODE'] > $return['ALL_PROMO_TO'])
					$return['ALL_PROMO_TO'] = $discount['CODE'];
			}
		}

		$return['PRICE'] = $return['PRICE_FROM'];
		if ($return['PRICE_FROM'] != $return['PRICE_TO'])
			$return['PRICE'] .= ' - ' . $return['PRICE_TO'];
		$return['DISCOUNT'] = $return['DISCOUNT_FROM'];
		if ($return['DISCOUNT_FROM'] != $return['DISCOUNT_TO'])
			$return['DISCOUNT'] .= ' - ' . $return['DISCOUNT_TO'];

		$return['RES_PROMO_FROM'] = max($return['PROMO_FROM'], $return['ALL_PROMO_FROM']);
		$return['RES_PROMO_TO'] = max($return['PROMO_TO'], $return['ALL_PROMO_TO']);
		$return['PROMO'] = $return['RES_PROMO_FROM'];
		if ($return['RES_PROMO_FROM'] != $return['RES_PROMO_TO'])
			$return['PROMO'] .= ' - ' . $return['RES_PROMO_TO'];

		$return['SPP'] = '0';
		if ($return['SPP_TO'])
			$return['SPP'] .= ' - ' . $return['SPP_TO'];

		$return['RES_FROM'] = $return['PRICE_FROM'] * (1 - $return['DISCOUNT_TO'] / 100) * (1 - $return['RES_PROMO_TO'] / 100) * (1 - $return['SPP_TO'] / 100);
		$return['RES_TO'] = $return['PRICE_TO'] * (1 - $return['DISCOUNT_FROM'] / 100) * (1 - $return['RES_PROMO_FROM'] / 100);
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
