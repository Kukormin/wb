<?
class Import
{
	public static function nomenclature()
	{
		\Local\Import\Service::nomenclature(true);

		return 'Import::nomenclature();';
	}

	public static function storeStocksAndPrices()
	{
		\Local\Import\Service::storeStocksAndPrices(true);

		return 'Import::storeStocksAndPrices();';
	}

	public static function priceHistory()
	{
		\Local\Import\Service::priceHistory(true);

		return 'Import::priceHistory();';
	}

	public static function sales()
	{
		\Local\Import\Service::sales(true);

		return 'Import::sales();';
	}

	public static function realization()
	{
		\Local\Import\Service::realizationAgent();

		return 'Import::realization();';
	}

	public static function shipping()
	{
		\Local\Import\Service::shipping(true);

		return 'Import::shipping();';
	}

    public static function deficit()
    {
        \Local\Import\Service::deficitAgent();

        return 'Import::deficit();';
    }

	public static function uln()
	{
		\Local\Import\Service::ulnAgent();

		return 'Import::uln();';
	}

	public static function prices()
	{
		\Local\Import\Service::prices(true);

		return 'Import::prices();';
	}
}