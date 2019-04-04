<?
namespace Local\Main;

use Local\System\ExtCache;
use Local\System\Utils;

/**
 * Отчеты
 * Class Reports
 * @package Local\Main
 */
class Reports
{
	const CACHE_PATH = 'Local/Main/Reports/';

	public static function getStocksByStores($refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$stocks = Stocks::getAll();
			foreach ($stocks as $offerId => $ar)
			{
				$offer = Offers::getById($offerId);
				$product = Products::getById($offer['PRODUCT']);
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				foreach ($ar as $storeId => $item)
				{
					$cnt = $item['AMOUNT'];
					$return[$storeId]['CNT'] += $cnt;
					$return[$storeId]['PRICE'] += $cnt * $price;
					$return[$storeId]['WB'] += $cnt * $margin;
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getStocksByCollections($refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$stocks = Stocks::getAll($refreshCache);
			foreach ($stocks as $offerId => $ar)
			{
				$offer = Offers::getById($offerId);
				$product = Products::getById($offer['PRODUCT']);
				$collectionId = $product['COLLECTION'];
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				foreach ($ar as $storeId => $item)
				{
					$cnt = $item['AMOUNT'];
					$return[$collectionId]['CNT'] += $cnt;
					$return[$collectionId]['PRICE'] += $cnt * $price;
					$return[$collectionId]['WB'] += $cnt * $margin;
					$return[$collectionId]['COST'] += $cnt * $offer['COST'];

					if ($cnt && !$offer['COST'])
						$return[$collectionId]['WARNINGS'] = true;
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getUlnByCollections($refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$offers = Offers::getAll($refreshCache);
			foreach ($offers['ITEMS'] as $offer)
			{
				$product = Products::getById($offer['PRODUCT']);
				$collectionId = $product['COLLECTION'];
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				$cnt = $offer['STOCKS'];
				$return[$collectionId]['CNT'] += $cnt;
				$return[$collectionId]['PRICE'] += $cnt * $price;
				$return[$collectionId]['WB'] += $cnt * $margin;
				$return[$collectionId]['COST'] += $cnt * $offer['COST'];

				if ($cnt && !$offer['COST'])
					$return[$collectionId]['WARNINGS'] = true;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getStocksByProducts($productIds, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$productIds,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$stocks = Stocks::getAll($refreshCache);
			foreach ($stocks as $offerId => $ar)
			{
				$offer = Offers::getById($offerId);

				if (!in_array($offer['PRODUCT'], $productIds))
					continue;

				$product = Products::getById($offer['PRODUCT']);
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				foreach ($ar as $storeId => $item)
				{
					$cnt = $item['AMOUNT'];
					$return[$offer['PRODUCT']]['CNT'] += $cnt;
					$return[$offer['PRODUCT']]['PRICE'] += $cnt * $price;
					$return[$offer['PRODUCT']]['WB'] += $cnt * $margin;
					$return[$offer['PRODUCT']]['COST'] += $cnt * $offer['COST'];

					if ($cnt && !$offer['COST'])
						$return[$offer['PRODUCT']]['WARNINGS'] = true;
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getUlnByProducts($productIds, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$productIds,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$offers = Offers::getAll($refreshCache);
			foreach ($offers['ITEMS'] as $offer)
			{
				if (!in_array($offer['PRODUCT'], $productIds))
					continue;

				$product = Products::getById($offer['PRODUCT']);
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				$cnt = $offer['STOCKS'];
				$return[$offer['PRODUCT']]['CNT'] += $cnt;
				$return[$offer['PRODUCT']]['PRICE'] += $cnt * $price;
				$return[$offer['PRODUCT']]['WB'] += $cnt * $margin;
				$return[$offer['PRODUCT']]['COST'] += $cnt * $offer['COST'];
				if ($cnt && !$offer['COST'])
					$return[$offer['PRODUCT']]['WARNINGS'] = true;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getStocksByOffers($offerIds, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$offerIds,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$stocks = Stocks::getByOffers($offerIds);
			foreach ($stocks as $offerId => $ar)
			{
				$offer = Offers::getById($offerId);
				$product = Products::getById($offer['PRODUCT']);
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				foreach ($ar as $storeId => $item)
				{
					$cnt = $item['AMOUNT'];
					$return[$offerId]['CNT'] += $cnt;
					$return[$offerId]['PRICE'] += $cnt * $price;
					$return[$offerId]['WB'] += $cnt * $margin;
					$return[$offerId]['COST'] += $cnt * $offer['COST'];

					if ($cnt && !$offer['COST'])
						$return[$offerId]['WARNINGS'] = true;
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getUlnByOffers($offerIds, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$offerIds,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			7200
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			foreach ($offerIds as $offerId)
			{
				$offer = Offers::getById($offerId);
				$product = Products::getById($offer['PRODUCT']);
				$price = $product['PRICE'];
				$discount = $product['DISCOUNT'];
				if ($discount)
					$price *= (1 - $discount / 100);
				$margin = Utils::getWbMargin($price);

				$cnt = $offer['STOCKS'];
				$return[$offerId]['CNT'] += $cnt;
				$return[$offerId]['PRICE'] += $cnt * $price;
				$return[$offerId]['WB'] += $cnt * $margin;
				$return[$offerId]['COST'] += $cnt * $offer['COST'];
				if (!$offer['COST'])
					$return[$offerId]['WARNINGS'] = true;
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

}