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
class HistoricPracticePredictor implements Predictor
{
    protected $custodian;

    /**
     * @var Collection
     */
    protected $providerLogs;

    /**
     * @var integer;
     */
    protected $billingProviderIdPrediction;

    /**
     * @var integer
     */
    protected $locationIdPrediction;

    /**
     * @var integer
     */
    protected $practiceIdPrediction;

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
            ->groupBy('practice_id')
            ->
            map(function (
                $item,
                $key
            ) {
                $collection = new Collection($item);

                return [
                    'practice_id'    => $key,
                    'practice_count' => $collection->sum('practice_count'),
                ];
            })
            ->values()
            ->sortByDesc('practice_count');


        return $merged->first()['practice_id'] ?? null;

    }

    protected function custodianLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->custodian) {
            return new Collection();
        }

        return DocumentLog::select(DB::raw("count('practice_id') as practice_count, practice_id"))
            ->where('custodian', '=', $this->custodian)
            ->whereNotNull('practice_id')
            ->orderBy('practice_count', 'desc')
            ->groupBy('practice_id')
            ->get(['practice_id'])
            ->map(function ($item) {
                return [
                    'practice_id'    => $item->practice_id,
                    'practice_count' => $item->practice_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['practice_id'];
            });
    }

    protected function providersLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        return ProviderLog::select(DB::raw("count('practice_id') as practice_count, practice_id"))
            ->whereIn('first_name', $this->providerLogs->pluck('first_name'))
            ->whereIn('last_name', $this->providerLogs->pluck('last_name'))
            ->whereNotNull('practice_id')
            ->orderBy('practice_count', 'desc')
            ->groupBy('practice_id')
            ->get(['practice_id'])
            ->map(function ($item) {
                return [
                    'practice_id'    => $item->practice_id,
                    'practice_count' => $item->practice_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['practice_id'];
            });
    }

    protected function addressesLookup()
    {
        //return an empty collection if the lookup value is empty
        if (!$this->providerLogs) {
            return new Collection();
        }

        return ProviderLog::select(DB::raw("count('practice_id') as practice_count, practice_id"))
            ->whereNotNull('practice_id')
            ->whereIn('street', $this->providerLogs->pluck('street'))
            ->whereIn('city', $this->providerLogs->pluck('city'))
            ->whereIn('state', $this->providerLogs->pluck('state'))
            ->whereIn('zip', $this->providerLogs->pluck('zip'))
            ->orWhereIn('cell_phone', $this->providerLogs->pluck('cell_phone'))
            ->orWhereIn('home_phone', $this->providerLogs->pluck('home_phone'))
            ->orWhereIn('work_phone', $this->providerLogs->pluck('work_phone'))
            ->orderBy('practice_count', 'desc')
            ->groupBy('practice_id')
            ->get(['practice_id'])
            ->map(function ($item) {
                return [
                    'practice_id'    => $item->practice_id,
                    'practice_count' => $item->practice_count,
                ];
            })
            ->reject(function ($item) {
                return !$item['practice_id'];
            });
    }
}