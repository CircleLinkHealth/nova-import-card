<?php namespace App\Importer\Predictors;

use App\Contracts\Importer\Predictor;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:36 PM
 */
class HistoricLocationPredictor implements Predictor
{
    /**
     * @var string
     */
    protected $custodian;

    /**
     * @var Collection
     */
    protected $providerLogs;

    /**
     * @var integer
     */
    protected $locationIdPrediction;

    public function __construct(
        $custodian,
        $providerLogs
    ) {
        $this->custodian = $custodian;

        if (is_array($providerLogs)) {
            $providerLogs = new Collection($providerLogs);
        }

        $this->providerLogs = $providerLogs;
    }

    /**
     * Predicts the Location, and Practice for a medical record.
     * Returns an id.
     *
     * @return integer
     */
    public function predict()
    {
        $custodianLookup = $this->custodianLookup();
        $providersLookup = $this->providersLookup();
        $addressesLookup = $this->addressesLookup();

        $merged = $custodianLookup->merge($providersLookup)
            ->merge($addressesLookup)
            ->groupBy('location_id')
            ->
            map(function (
                $item,
                $key
            ) {
                $collection = new Collection($item);

                return [
                    'location_id'    => $key,
                    'location_count' => $collection->sum('location_count'),
                ];
            })
            ->values()
            ->sortByDesc('location_count');


        return $merged->first()['location_id'] ?? null;

    }

    protected function custodianLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->custodian) {
            return new Collection();
        }

        return DocumentLog::select(DB::raw("count('location_id') as location_count, location_id"))
            ->where('custodian', '=', $this->custodian)
            ->whereNotNull('location_id')
            ->orderBy('location_count', 'desc')
            ->groupBy('location_id')
            ->get(['location_id'])
            ->map(function ($item) {
                return [
                    'location_id'    => $item->location_id,
                    'location_count' => $item->location_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['location_id'];
            });
    }

    protected function providersLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        return ProviderLog::select(DB::raw("count('location_id') as location_count, location_id"))
            ->whereIn('first_name', $this->providerLogs->pluck('first_name'))
            ->whereIn('last_name', $this->providerLogs->pluck('last_name'))
            ->whereNotNull('location_id')
            ->orderBy('location_count', 'desc')
            ->groupBy('location_id')
            ->get(['location_id'])
            ->map(function ($item) {
                return [
                    'location_id'    => $item->location_id,
                    'location_count' => $item->location_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['location_id'];
            });
    }

    protected function addressesLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        return ProviderLog::select(DB::raw("count('location_id') as location_count, location_id"))
            ->whereNotNull('location_id')
            ->whereIn('street', $this->providerLogs->pluck('street'))
            ->whereIn('city', $this->providerLogs->pluck('city'))
            ->whereIn('state', $this->providerLogs->pluck('state'))
            ->whereIn('zip', $this->providerLogs->pluck('zip'))
            ->orWhereIn('cell_phone', $this->providerLogs->pluck('cell_phone'))
            ->orWhereIn('home_phone', $this->providerLogs->pluck('home_phone'))
            ->orWhereIn('work_phone', $this->providerLogs->pluck('work_phone'))
            ->orderBy('location_count', 'desc')
            ->groupBy('location_id')
            ->get(['location_id'])
            ->map(function ($item) {
                return [
                    'location_id'    => $item->location_id,
                    'location_count' => $item->location_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['location_id'];
            });
    }
}